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
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key ke Peserta
            $table->foreignUlid('peserta_id')->constrained('peserta')->onDelete('cascade')->unique(); // Hanya 1 record total per peserta
            
            // Status Kehadiran untuk 4 Pleno (1 = Hadir, 0 = Absen)
            $table->tinyInteger('pleno_1')->default(0);
            $table->tinyInteger('pleno_2')->default(0);
            $table->tinyInteger('pleno_3')->default(0);
            $table->tinyInteger('pleno_4')->default(0);

            // Kolom untuk menyimpan Total Kehadiran
            $table->integer('total_kehadiran')->default(0); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};
