<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->string('kode_unik')->unique()->nullable()->after('id');
        });

        // Generate kode unik untuk data yang sudah ada
        \App\Models\Peserta::all()->each(function ($peserta) {
            $peserta->save(); // Ini akan trigger boot method untuk generate kode_unik
        });
    }

    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->dropColumn('kode_unik');
        });
    }
};
