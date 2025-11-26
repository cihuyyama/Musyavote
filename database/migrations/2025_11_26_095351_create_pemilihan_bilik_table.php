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
        Schema::create('pemilihan_bilik', function (Blueprint $table) {
            $table->foreignUlid('pemilihan_id')->constrained('pemilihan')->onDelete('cascade');
            $table->foreignUlid('bilik_id')->constrained('bilik')->onDelete('cascade');
            
            $table->primary(['pemilihan_id', 'bilik_id']); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemilihan_bilik');
    }
};
