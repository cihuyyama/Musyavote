<?php
// File: database/migrations/2025_12_07_132740_add_unique_constraint_to_calon_table.php - DIPERBAIKI

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calon', function (Blueprint $table) {
            // 1. Pertama, cek dan perbaiki data duplikat
            $this->fixDuplicateData();
            
            // 2. Pastikan semua data memiliki nomor_urut yang valid
            DB::statement("UPDATE calon SET nomor_urut = 1 WHERE nomor_urut IS NULL OR nomor_urut <= 0");
            
            // 3. Sekarang tambahkan unique constraint
            $table->unique(['nomor_urut', 'jabatan']);
        });
    }

    public function down(): void
    {
        Schema::table('calon', function (Blueprint $table) {
            $table->dropUnique(['nomor_urut', 'jabatan']);
        });
    }

    /**
     * Perbaiki data duplikat nomor_urut per jabatan
     */
    private function fixDuplicateData(): void
    {
        // Cari jabatan yang ada
        $jabatans = DB::table('calon')->select('jabatan')->distinct()->pluck('jabatan');
        
        foreach ($jabatans as $jabatan) {
            // Ambil semua calon untuk jabatan ini
            $calons = DB::table('calon')
                ->where('jabatan', $jabatan)
                ->orderBy('created_at')
                ->get();
            
            $nomorUrutMap = [];
            $duplicates = [];
            
            // Identifikasi duplikat
            foreach ($calons as $calon) {
                $key = $calon->nomor_urut . '-' . $calon->jabatan;
                
                if (isset($nomorUrutMap[$key])) {
                    $duplicates[] = $calon->id;
                } else {
                    $nomorUrutMap[$key] = $calon->id;
                }
            }
            
            // Perbaiki duplikat dengan memberikan nomor urut baru
            if (!empty($duplicates)) {
                $maxNomorUrut = DB::table('calon')
                    ->where('jabatan', $jabatan)
                    ->max('nomor_urut') ?? 0;
                
                foreach ($duplicates as $duplicateId) {
                    $maxNomorUrut++;
                    DB::table('calon')
                        ->where('id', $duplicateId)
                        ->update(['nomor_urut' => $maxNomorUrut]);
                }
            }
        }
    }
};