<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->string('password')->after('kode_unik');
            $table->string('password_plain')->after('password')->nullable(); // Kolom untuk plain password
            $table->rememberToken()->after('password_plain');
        });

        // Update data existing dengan random 6 digit password
        \App\Models\Peserta::all()->each(function ($peserta) {
            $randomPassword = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $peserta->password = Hash::make($randomPassword);
            $peserta->password_plain = $randomPassword;
            $peserta->save();
        });
    }

    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->dropColumn(['password', 'password_plain', 'remember_token']);
        });
    }
};