<?php

namespace App\Imports;

use App\Models\Peserta;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PesertaImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // 1. Validasi Massal
        $validationRules = [
            'nama' => 'required|string|max:255',
            'asal_pimpinan' => 'required|string|max:255',
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'status' => ['required', Rule::in(['Aktif', 'Non Aktif'])],
        ];

        // Jalankan seluruh operasi dalam transaksi
        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                // Konversi data dari Excel (huruf kecil) ke format yang sesuai
                $data = [
                    'nama' => $row['nama'] ?? null,
                    'asal_pimpinan' => $row['asal_pimpinan'] ?? null,
                    'jenis_kelamin' => strtoupper($row['jenis_kelamin'] ?? ''), // Pastikan L/P
                    'status' => ucwords($row['status'] ?? 'Aktif'), 
                ];

                $validator = Validator::make($data, $validationRules);

                if ($validator->fails()) {
                    // Anda dapat melempar exception atau mencatat baris yang gagal
                    throw new \Exception("Validasi gagal pada baris: " . json_encode($row->toArray()) . " | Errors: " . json_encode($validator->errors()));
                }

                // 2. Buat Peserta
                $peserta = Peserta::create($data);
                
                // 3. Opsional: Buat entri Kehadiran default (penting!)
                // Karena kelayakan voting bergantung pada tabel 'kehadiran'
                if ($peserta) {
                    $peserta->kehadiran()->create(['total_kehadiran' => 0]); 
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Penting: Throw error untuk ditampilkan di Inertia
            throw $e; 
        }
    }
}
