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
        Schema::create('voting_records', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('peserta_id');
            $table->string('pemilihan_id');
            $table->string('bilik_id');
            $table->json('pilihan_calon'); // Array of calon_id yang dipilih
            $table->boolean('tidak_memilih')->default(false);
            $table->timestamp('waktu_voting');
            $table->timestamps();

            $table->foreign('peserta_id')->references('id')->on('peserta')->onDelete('cascade');
            $table->foreign('pemilihan_id')->references('id')->on('pemilihan')->onDelete('cascade');
            $table->foreign('bilik_id')->references('id')->on('bilik')->onDelete('cascade');
            
            // Satu peserta hanya bisa voting sekali per pemilihan
            $table->unique(['peserta_id', 'pemilihan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_records');
    }
};
