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
        Schema::create('pemilihan', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('nama_pemilihan');
            $table->unsignedTinyInteger('minimal_kehadiran')->default(3); 
            $table->unsignedTinyInteger('jumlah_formatur_terpilih')->default(12)->nullable(); 
            $table->boolean('boleh_tidak_memilih')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemilihan');
    }
};
