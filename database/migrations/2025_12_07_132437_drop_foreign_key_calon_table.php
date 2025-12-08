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
        // Cari nama foreign key
        $foreignKey = DB::selectOne("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'calon' 
            AND TABLE_SCHEMA = DATABASE()
            AND REFERENCED_TABLE_NAME = 'peserta'
        ");

        if ($foreignKey) {
            Schema::table('calon', function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign([$foreignKey->CONSTRAINT_NAME]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('calon', function (Blueprint $table) {
            $table->foreign('peserta_id')->references('id')->on('peserta')->onDelete('cascade');
        });
    }
};