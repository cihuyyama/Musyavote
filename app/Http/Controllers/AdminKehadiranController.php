<?php

namespace App\Http\Controllers;

use App\Models\AdminPresensi;
use App\Models\Kehadiran;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminKehadiranController extends Controller
{
    public function index()
    {
        $admins = AdminPresensi::all();
        return inertia('AdminKehadiran/Index', [
            'admins' => $admins,
        ]);
    }

    public function create()
    {
        return inertia('AdminKehadiran/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:admin_kehadiran',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        AdminPresensi::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin-presensi.index')
            ->with('success', 'Admin berhasil ditambahkan.');
    }

    public function edit(AdminPresensi $adminPresensi)
    {
        return inertia('AdminKehadiran/Edit', [
            'admin' => $adminPresensi,
        ]);
    }

    public function update(Request $request, AdminPresensi $adminPresensi)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:admin_kehadiran,username,' . $adminPresensi->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $data = [
            'nama' => $request->nama,
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $adminPresensi->update($data);

        return redirect()->route('admin-presensi.index')
            ->with('success', 'Admin berhasil diperbarui.');
    }

    public function destroy(AdminPresensi $adminPresensi)
    {
        $adminPresensi->delete();

        return redirect()->route('admin-presensi.index')
            ->with('success', 'Admin berhasil dihapus.');
    }
    
    // Scan QR untuk mendapatkan data peserta
    public function scanQR(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string'
        ]);

        // Extract kode unik dari QR data (bisa berupa kode unik langsung atau JSON)
        $qrData = $request->qr_data;
        $kodeUnik = $qrData;

        // Jika QR data berupa JSON, extract kode unik
        if (strpos($qrData, '{') === 0) {
            $decoded = json_decode($qrData, true);
            $kodeUnik = $decoded['kode_unik'] ?? $qrData;
        }

        // Cari peserta berdasarkan kode unik
        $peserta = Peserta::with('kehadiran')
                        ->where('kode_unik', $kodeUnik)
                        ->first();

        if (!$peserta) {
            return back()->with([
                'success' => false,
                'message' => 'Peserta tidak ditemukan'
            ]);
        }

        return back()->with([
            'success' => true,
            'message' => 'Data peserta berhasil ditemukan',
            'data' => [
                'peserta' => $peserta
            ]
        ]);
    }

    // Proses presensi setelah konfirmasi
    public function presensi(Request $request)
    {
        $request->validate([
            'kode_unik' => 'required|string',
            'pleno' => 'required|integer|in:1,2,3,4'
        ]);

        $peserta = Peserta::where('kode_unik', $request->kode_unik)->first();

        if (!$peserta) {
            return back()->with([
                'success' => false,
                'message' => 'Peserta tidak ditemukan'
            ]);
        }

        // Cek atau buat data kehadiran
        $kehadiran = Kehadiran::firstOrCreate(
            ['peserta_id' => $peserta->id],
            [
                'pleno_1' => 0,
                'pleno_2' => 0,
                'pleno_3' => 0,
                'pleno_4' => 0,
                'total_kehadiran' => 0
            ]
        );

        // Update kehadiran berdasarkan pleno
        $plenoField = "pleno_{$request->pleno}";
        
        if ($kehadiran->$plenoField == 0) {
            $kehadiran->$plenoField = 1;
            $kehadiran->total_kehadiran = $kehadiran->pleno_1 + $kehadiran->pleno_2 + $kehadiran->pleno_3 + $kehadiran->pleno_4;
            $kehadiran->save();

            return back()->with([
                'success' => true,
                'message' => "Presensi Pleno {$request->pleno} berhasil untuk {$peserta->nama}",
                'data' => [
                    'peserta' => $peserta,
                    'kehadiran' => $kehadiran
                ]
            ]);
        }

        return back()->with([
            'success' => false,
            'message' => "{$peserta->nama} sudah melakukan presensi Pleno {$request->pleno}"
        ]);
    }
}
