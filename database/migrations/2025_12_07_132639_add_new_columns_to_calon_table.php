<?php
// File: database/migrations/2025_12_01_000004_add_new_columns_to_calon_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calon', function (Blueprint $table) {
            if (!Schema::hasColumn('calon', 'nama')) {
                $table->string('nama');
            }
            
            if (!Schema::hasColumn('calon', 'asal_pimpinan')) {
                $table->string('asal_pimpinan');
            }
            
            if (!Schema::hasColumn('calon', 'jenis_kelamin')) {
                $table->enum('jenis_kelamin', ['L', 'P']);
            }
            
            if (!Schema::hasColumn('calon', 'foto')) {
                $table->string('foto')->nullable();
            }
            
            if (!Schema::hasColumn('calon', 'nomor_urut')) {
                $table->integer('nomor_urut');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calon', function (Blueprint $table) {
            $columns = ['nama', 'asal_pimpinan', 'jenis_kelamin', 'foto', 'nomor_urut'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('calon', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};