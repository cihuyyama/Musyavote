<?php
// File: database/migrations/2025_12_01_000003_drop_peserta_id_column_calon_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calon', function (Blueprint $table) {
            if (Schema::hasColumn('calon', 'peserta_id')) {
                $table->dropColumn('peserta_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calon', function (Blueprint $table) {
            $table->foreignUlid('peserta_id')->nullable();
        });
    }
};