<?php

use App\Models\Bilik;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bilik', function (Blueprint $table) {
            $table->string('password_plain')->after('password')->nullable(); // Kolom untuk plain password
        });

        // Update data existing - ambil dari password yang sudah ada atau generate baru
        Bilik::all()->each(function ($bilik) {
            // Jika sudah ada password yang dihash, kita tidak bisa mendapatkan plain text-nya
            // Jadi kita generate password baru untuk existing data
            $randomPassword = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $bilik->password = Hash::make($randomPassword);
            $bilik->password_plain = $randomPassword;
            $bilik->save();
        });
    }

    public function down(): void
    {
        Schema::table('bilik', function (Blueprint $table) {
            $table->dropColumn('password_plain');
        });
    }
};
