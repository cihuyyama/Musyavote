<?php
// File: app/Imports/CalonImport.php - BARU

namespace App\Imports;

use App\Models\Calon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CalonImport implements ToCollection, WithHeadingRow
{
    private $rowCount = 0;
    private $errors = [];
    private $successCount = 0;

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        
        try {
            foreach ($rows as $row) {
                $this->rowCount++;
                
                // Validasi data minimal
                if (empty($row['nama']) || empty($row['asal_pimpinan']) || empty($row['nomor_urut']) || empty($row['jabatan'])) {
                    $this->errors[] = "Baris {$this->rowCount}: Data tidak lengkap (nama, asal, nomor urut, jabatan diperlukan)";
                    continue;
                }
                
                // Validasi jabatan
                $jabatan = ucfirst(strtolower(trim($row['jabatan'])));
                if (!in_array($jabatan, ['Ketua', 'Formatur'])) {
                    $this->errors[] = "Baris {$this->rowCount}: Jabatan '{$row['jabatan']}' tidak valid (harus Ketua/Formatur)";
                    continue;
                }
                
                // Validasi jenis kelamin
                $jenisKelamin = strtoupper(trim($row['jenis_kelamin'] ?? ''));
                if (!in_array($jenisKelamin, ['L', 'P'])) {
                    $this->errors[] = "Baris {$this->rowCount}: Jenis kelamin '{$row['jenis_kelamin']}' tidak valid (harus L/P)";
                    continue;
                }
                
                // Validasi nomor urut
                if (!is_numeric($row['nomor_urut']) || $row['nomor_urut'] < 1) {
                    $this->errors[] = "Baris {$this->rowCount}: Nomor urut '{$row['nomor_urut']}' tidak valid (harus angka positif)";
                    continue;
                }
                
                // Cek duplikasi nomor urut untuk jabatan yang sama
                $existingCalon = Calon::where('jabatan', $jabatan)
                    ->where('nomor_urut', $row['nomor_urut'])
                    ->exists();
                    
                if ($existingCalon) {
                    $this->errors[] = "Baris {$this->rowCount}: Nomor urut {$row['nomor_urut']} sudah digunakan untuk calon {$jabatan}";
                    continue;
                }
                
                try {
                    Calon::create([
                        'nama' => trim($row['nama']),
                        'asal_pimpinan' => trim($row['asal_pimpinan']),
                        'jenis_kelamin' => $jenisKelamin,
                        'nomor_urut' => (int) $row['nomor_urut'],
                        'jabatan' => $jabatan,
                        'foto' => $this->handleFoto($row) ?? null,
                    ]);
                    
                    $this->successCount++;
                    
                } catch (\Exception $e) {
                    $this->errors[] = "Baris {$this->rowCount}: Gagal menyimpan - " . $e->getMessage();
                    Log::error("Import error at row {$this->rowCount}: " . $e->getMessage());
                }
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import transaction failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Handle foto jika ada di Excel (misalnya path atau base64)
     */
    private function handleFoto($row)
    {
        if (empty($row['foto'])) {
            return null;
        }
        
        $fotoData = trim($row['foto']);
        
        // Jika berupa URL atau path
        if (filter_var($fotoData, FILTER_VALIDATE_URL) || file_exists($fotoData)) {
            // Implementasi download dari URL atau copy dari path
            // Sesuaikan dengan kebutuhan
            return null;
        }
        
        // Jika base64 image
        if (preg_match('/^data:image\/(\w+);base64,/', $fotoData, $matches)) {
            $imageData = substr($fotoData, strpos($fotoData, ',') + 1);
            $imageType = $matches[1];
            
            try {
                $decodedImage = base64_decode($imageData);
                if ($decodedImage === false) {
                    return null;
                }
                
                $filename = 'calon_' . Str::random(20) . '.' . $imageType;
                $path = 'calon_fotos/' . $filename;
                
                Storage::disk('public')->put($path, $decodedImage);
                
                return $path;
            } catch (\Exception $e) {
                Log::error("Failed to save base64 image: " . $e->getMessage());
                return null;
            }
        }
        
        return null;
    }
    
    public function getRowCount(): int
    {
        return $this->successCount;
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
}