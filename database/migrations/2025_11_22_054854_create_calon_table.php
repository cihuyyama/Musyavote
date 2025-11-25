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
        Schema::create('calon', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('peserta_id')->constrained('peserta')->onDelete('cascade'); 
            $table->enum('jabatan', ['Ketua', 'Formatur']);
            $table->unique(['peserta_id', 'jabatan']); // Pastikan satu peserta hanya bisa jadi satu jenis calon (misal: tidak bisa dua kali Formatur)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calon');
    }
};
