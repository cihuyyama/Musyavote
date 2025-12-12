<?php

namespace App\Imports;

use App\Models\AdminPresensi;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class AdminKehadiranImport implements ToCollection, WithStartRow, SkipsEmptyRows, WithMultipleSheets
{
    /**
     * Hanya import dari sheet 'Data Admin'
     */
    public function sheets(): array
    {
        return [
            'Data Admin' => $this, // Hanya membaca sheet 'Data Admin'
        ];
    }

    /**
     * Mulai dari baris ke-2 (skip header)
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        // Validasi minimal ada 1 data
        if ($rows->isEmpty()) {
            throw new \Exception('File Excel tidak mengandung data yang valid.');
        }

        $rowNumber = 2; // Mulai dari baris 2 (setelah header)
        $errors = [];
        $importedCount = 0;

        foreach ($rows as $row) {
            // Mapping kolom berdasarkan index (A=0, B=1, C=2, D=3, E=4)
            $data = [
                'nama' => isset($row[0]) ? trim($row[0]) : null,
                'username' => isset($row[1]) ? trim($row[1]) : null,
                'password' => isset($row[2]) ? trim($row[2]) : null,
                'pleno_akses' => isset($row[3]) ? trim($row[3]) : null,
                'status' => isset($row[4]) ? trim(strtolower($row[4])) : null,
            ];

            // Skip jika semua kolom kosong
            if (empty($data['nama']) && empty($data['username']) && empty($data['password'])) {
                $rowNumber++;
                continue;
            }

            // Validasi data
            $validator = Validator::make($data, [
                'nama' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:admin_kehadiran,username',
                'password' => 'required|string|min:6',
                'pleno_akses' => 'nullable|string',
                'status' => 'required|in:active,inactive',
            ], [
                'nama.required' => 'Kolom nama wajib diisi',
                'username.required' => 'Kolom username wajib diisi',
                'username.unique' => 'Username sudah digunakan',
                'password.required' => 'Kolom password wajib diisi',
                'password.min' => 'Password minimal 6 karakter',
                'status.required' => 'Kolom status wajib diisi',
                'status.in' => 'Status harus active atau inactive',
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $errors[] = "Baris {$rowNumber}: {$error}";
                }
                $rowNumber++;
                continue;
            }

            // Proses pleno_akses dari string ke array
            $plenoAkses = [];
            if (!empty($data['pleno_akses'])) {
                // Hapus spasi dan pisahkan dengan koma
                $plenoString = preg_replace('/\s+/', '', $data['pleno_akses']);
                $plenoArray = explode(',', $plenoString);
                
                // Filter hanya angka 1-4
                $plenoAkses = array_filter($plenoArray, function($value) {
                    return is_numeric($value) && $value >= 1 && $value <= 4;
                });
                
                $plenoAkses = array_map('intval', $plenoAkses);
                $plenoAkses = array_unique($plenoAkses);
                sort($plenoAkses);
            }

            // Cek apakah username sudah ada
            $existingAdmin = AdminPresensi::where('username', $data['username'])->first();
            
            if ($existingAdmin) {
                // Update admin yang sudah ada
                $existingAdmin->update([
                    'nama' => $data['nama'],
                    'password_plain' => $data['password'],
                    'pleno_akses' => $plenoAkses,
                    'status' => $data['status'],
                ]);
            } else {
                // Buat admin baru
                AdminPresensi::create([
                    'nama' => $data['nama'],
                    'username' => $data['username'],
                    'password_plain' => $data['password'],
                    'pleno_akses' => $plenoAkses,
                    'status' => $data['status'],
                ]);
            }

            $importedCount++;
            $rowNumber++;
        }

        // Jika ada error, throw exception
        if (!empty($errors)) {
            $errorMessage = implode('; ', $errors);
            if ($importedCount > 0) {
                $errorMessage .= " (Berhasil mengimport {$importedCount} data, gagal " . count($errors) . " data)";
            }
            throw new \Exception($errorMessage);
        }

        // Jika tidak ada data yang berhasil diimport
        if ($importedCount === 0) {
            throw new \Exception('Tidak ada data yang berhasil diimport. Periksa format file Excel Anda.');
        }
    }

    /**
     * Aturan untuk skip baris kosong
     */
    public function rules(): array
    {
        return [
            // Aturan validasi
        ];
    }
}