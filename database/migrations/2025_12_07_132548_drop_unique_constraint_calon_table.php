<?php
// File: database/migrations/2025_12_01_000002_drop_unique_constraint_calon_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cari semua unique constraints yang melibatkan peserta_id
        $uniqueConstraints = DB::select("
            SELECT DISTINCT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'calon' 
            AND TABLE_SCHEMA = DATABASE()
            AND CONSTRAINT_NAME LIKE '%unique%'
            AND COLUMN_NAME IN ('peserta_id', 'jabatan')
        ");

        foreach ($uniqueConstraints as $constraint) {
            Schema::table('calon', function (Blueprint $table) use ($constraint) {
                try {
                    $table->dropUnique([$constraint->CONSTRAINT_NAME]);
                } catch (\Exception $e) {
                    // Jika gagal, coba cara lain
                    DB::statement("ALTER TABLE calon DROP INDEX {$constraint->CONSTRAINT_NAME}");
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('calon', function (Blueprint $table) {
            $table->unique(['peserta_id', 'jabatan']);
        });
    }
};