<?php

namespace App\Imports;

use App\Models\Peserta;
use App\Models\Kehadiran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PesertaImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Data Peserta' => new PesertaSheetImport(),
            // Sheet Panduan diabaikan
        ];
    }
}

class PesertaSheetImport implements ToCollection, WithHeadingRow
{
    private $importedCount = 0;
    private $errors = [];

    public function collection(Collection $rows)
    {
        // Reset counter
        $this->importedCount = 0;
        $this->errors = [];

        Log::info("=== STARTING EXCEL IMPORT - DATA PESERTA SHEET ===");
        Log::info("Total rows in Data Peserta sheet: " . $rows->count());

        // Skip jika tidak ada data
        if ($rows->count() === 0) {
            Log::warning("No data found in Data Peserta sheet");
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                Log::info("Processing row {$index}: " . json_encode($row->toArray()));

                // Skip baris yang kosong
                if ($this->isRowEmpty($row)) {
                    Log::info("Skipping empty row {$index}");
                    continue;
                }

                // Normalisasi nama kolom
                $nama = $this->getCellValue($row, ['nama', 'Nama', 'NAMA']);
                $asalPimpinan = $this->getCellValue($row, ['asal_pimpinan', 'asal pimpinan', 'Asal Pimpinan', 'ASAL_PIMPINAN']);
                $jenisKelamin = $this->getCellValue($row, ['jenis_kelamin', 'jenis kelamin', 'Jenis Kelamin', 'JENIS_KELAMIN']);

                Log::info("Extracted - nama: '{$nama}', asal: '{$asalPimpinan}', jk: '{$jenisKelamin}'");

                // Validasi manual data required
                if (empty($nama) || empty($asalPimpinan) || empty($jenisKelamin)) {
                    $errorMsg = "Baris " . ($index + 2) . ": Data tidak lengkap";
                    $this->errors[] = $errorMsg;
                    Log::warning($errorMsg);
                    continue;
                }

                // Skip baris header jika terdeteksi
                if ($this->isHeaderRow($nama, $asalPimpinan, $jenisKelamin)) {
                    Log::info("Skipping header row {$index}");
                    continue;
                }

                // Normalisasi data
                $nama = trim($nama);
                $asalPimpinan = trim($asalPimpinan);
                $jenisKelamin = strtoupper(trim($jenisKelamin));

                // Konversi jenis kelamin
                if ($jenisKelamin === 'L' || $jenisKelamin === 'LAKI-LAKI' || $jenisKelamin === 'LAKI') {
                    $jenisKelamin = 'L';
                } elseif ($jenisKelamin === 'P' || $jenisKelamin === 'PEREMPUAN') {
                    $jenisKelamin = 'P';
                } else {
                    $this->errors[] = "Baris " . ($index + 2) . ": Jenis kelamin tidak valid '{$jenisKelamin}'";
                    continue;
                }

                $data = [
                    'nama' => $nama,
                    'asal_pimpinan' => $asalPimpinan,
                    'jenis_kelamin' => $jenisKelamin,
                    'status' => 'Aktif',
                    'foto' => null,
                ];

                // Cek duplikat berdasarkan nama
                $existing = Peserta::where('nama', $data['nama'])->first();
                if ($existing) {
                    $errorMsg = "Baris " . ($index + 2) . ": Peserta dengan nama '{$data['nama']}' sudah ada";
                    $this->errors[] = $errorMsg;
                    Log::warning($errorMsg);
                    continue;
                }

                // Buat Peserta
                $peserta = Peserta::create($data);
                
                if ($peserta) {
                    Log::info("Successfully created peserta: {$peserta->id} - {$peserta->nama}");
                    
                    // Buat entri Kehadiran default
                    $kehadiran = $peserta->kehadiran()->create([
                        'pleno_1' => 0,
                        'pleno_2' => 0, 
                        'pleno_3' => 0,
                        'pleno_4' => 0,
                        'total_kehadiran' => 0
                    ]);
                    
                    Log::info("Successfully created kehadiran: {$kehadiran->id} for peserta: {$peserta->id}");
                    $this->importedCount++;
                } else {
                    Log::error("Failed to create peserta for data: " . json_encode($data));
                    $this->errors[] = "Baris " . ($index + 2) . ": Gagal membuat peserta";
                }
            }

            Log::info("Import completed. Imported: {$this->importedCount}, Errors: " . count($this->errors));

            if (!empty($this->errors)) {
                throw new \Exception("Import completed with errors: " . implode(' | ', array_slice($this->errors, 0, 5)));
            }

            DB::commit();
            Log::info("=== IMPORT SUCCESS ===");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("=== IMPORT FAILED ===");
            Log::error("Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Method untuk handle berbagai format penulisan header kolom
     */
    private function getCellValue($row, $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            if (isset($row[$key])) {
                $value = $row[$key];
                return $value;
            }
        }
        return null;
    }

    /**
     * Cek jika baris kosong
     */
    private function isRowEmpty($row)
    {
        $values = array_filter($row->toArray(), function($value) {
            return !empty($value) && $value !== '';
        });
        return empty($values);
    }

    /**
     * Cek jika baris adalah header
     */
    private function isHeaderRow($nama, $asalPimpinan, $jenisKelamin)
    {
        $headerPatterns = [
            'nama' => ['nama', 'Nama', 'NAMA'],
            'asal_pimpinan' => ['asal_pimpinan', 'asal pimpinan', 'Asal Pimpinan', 'ASAL_PIMPINAN'],
            'jenis_kelamin' => ['jenis_kelamin', 'jenis kelamin', 'Jenis Kelamin', 'JENIS_KELAMIN']
        ];

        $nama = strtolower(trim($nama));
        $asalPimpinan = strtolower(trim($asalPimpinan));
        $jenisKelamin = strtolower(trim($jenisKelamin));

        return in_array($nama, $headerPatterns['nama']) || 
               in_array($asalPimpinan, $headerPatterns['asal_pimpinan']) || 
               in_array($jenisKelamin, $headerPatterns['jenis_kelamin']);
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}