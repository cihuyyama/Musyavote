<?php
// File: database/migrations/2025_12_01_000001_drop_foreign_key_calon_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cara lebih aman: langsung drop berdasarkan kolom
        Schema::table('calon', function (Blueprint $table) {
            if (Schema::hasColumn('calon', 'peserta_id')) {
                // Coba drop foreign key dengan nama kolom
                $table->dropForeign(['peserta_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('calon', function (Blueprint $table) {
            // Pastikan kolom peserta_id ada sebelum menambahkan foreign key
            if (!Schema::hasColumn('calon', 'peserta_id')) {
                $table->foreignUlid('peserta_id')->nullable();
            }
            $table->foreign('peserta_id')->references('id')->on('peserta')->onDelete('cascade');
        });
    }
};