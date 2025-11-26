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
        Schema::create('pemilihan_calon', function (Blueprint $table) {
            // FK: Pemilihan
            $table->foreignUlid('pemilihan_id')->constrained('pemilihan')->onDelete('cascade');
            // FK: Calon (entitas yang baru dibuat)
            $table->foreignUlid('calon_id')->constrained('calon')->onDelete('cascade'); 
            
            // Primary Key Komposit
            $table->primary(['pemilihan_id', 'calon_id']); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemilihan_calon');
    }
};
