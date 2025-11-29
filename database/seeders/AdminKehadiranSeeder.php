<?php

namespace Database\Seeders;

use App\Models\AdminKehadiran;
use App\Models\AdminPresensi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminKehadiranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        AdminPresensi::truncate();

        // Create admin kehadiran accounts
        $admins = [
            [
                'nama' => 'Admin Presensi Utama',
                'username' => 'adminpresensi',
                'password' => Hash::make('presensi123'),
            ],
            [
                'nama' => 'Panitia Kehadiran 1',
                'username' => 'panitia1',
                'password' => Hash::make('panitia123'),
            ],
            [
                'nama' => 'Panitia Kehadiran 2', 
                'username' => 'panitia2',
                'password' => Hash::make('panitia123'),
            ],
        ];

        foreach ($admins as $admin) {
            AdminPresensi::create($admin);
        }

        $this->command->info('Admin Kehadiran berhasil dibuat!');
        $this->command->info('Username: adminpresensi | Password: presensi123');
        $this->command->info('Username: panitia1 | Password: panitia123');
        $this->command->info('Username: panitia2 | Password: panitia123');
    }
}