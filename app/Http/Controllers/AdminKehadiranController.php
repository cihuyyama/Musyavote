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
        $admins = AdminPresensi::all()->makeVisible('password_plain'); // Make visible untuk admin
        
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
            'pleno_akses' => 'nullable|array',
            'pleno_akses.*' => 'integer|in:1,2,3,4',
        ]);

        $data = $request->only(['nama', 'username']);
        $data['password_plain'] = $request->password; // Simpan plain password
        $data['pleno_akses'] = $request->pleno_akses ?? [];

        // Password akan otomatis di-hash oleh mutator setPasswordPlainAttribute
        AdminPresensi::create($data);

        return redirect()->route('admin-presensi.index')
            ->with('success', 'Admin berhasil ditambahkan.');
    }

    public function edit(AdminPresensi $adminPresensi)
    {
        $adminPresensi->makeVisible('password_plain'); // Make visible untuk edit
        
        return inertia('AdminKehadiran/Edit', [
            'admin' => [
                'id' => $adminPresensi->id,
                'nama' => $adminPresensi->nama,
                'username' => $adminPresensi->username,
                'password_plain' => $adminPresensi->password_plain,
                'pleno_akses' => $adminPresensi->pleno_akses ?? [],
            ],
        ]);
    }

    public function update(Request $request, AdminPresensi $adminPresensi)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:admin_kehadiran,username,' . $adminPresensi->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'pleno_akses' => 'nullable|array',
            'pleno_akses.*' => 'integer|in:1,2,3,4',
        ]);

        $adminPresensi->nama = $request->nama;
        $adminPresensi->username = $request->username;
        $adminPresensi->pleno_akses = $request->pleno_akses ?? [];

        // Update password jika diisi
        if ($request->filled('password')) {
            $adminPresensi->password_plain = $request->password; // Akan trigger setPasswordPlainAttribute
        }

        $adminPresensi->save();

        return redirect()->route('admin-presensi.index')
            ->with('success', 'Admin berhasil diperbarui.');
    }

    public function destroy(AdminPresensi $adminPresensi)
    {
        $adminPresensi->delete();

        return redirect()->route('admin-presensi.index')
            ->with('success', 'Admin berhasil dihapus.');
    }
    
    /**
     * Reset password Admin dengan password acak.
     */
    public function resetPassword(AdminPresensi $adminPresensi)
    {
        $newPassword = $adminPresensi->generateNewPassword();
        
        return redirect()->route('admin-presensi.index')
            ->with('success', 'Password berhasil direset.')
            ->with('new_password', $newPassword); // Kirim password baru untuk ditampilkan
    }
    
    // Scan QR untuk mendapatkan data peserta
    public function scanQR(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string'
        ]);

        // Extract kode unik dari QR data
        $qrData = $request->qr_data;
        $kodeUnik = $qrData;

        if (strpos($qrData, '{') === 0) {
            $decoded = json_decode($qrData, true);
            $kodeUnik = $decoded['kode_unik'] ?? $qrData;
        }

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
        ]);

        $admin = $request->user();
        
        // Validasi admin memiliki akses pleno
        if (empty($admin->pleno_akses)) {
            return back()->with([
                'success' => false,
                'message' => 'Admin tidak memiliki akses ke pleno manapun'
            ]);
        }

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

        $successMessages = [];
        $errorMessages = [];
        
        // Presensi untuk semua pleno yang diakses admin
        foreach ($admin->pleno_akses as $pleno) {
            $plenoField = "pleno_{$pleno}";
            
            if ($kehadiran->$plenoField == 0) {
                $kehadiran->$plenoField = 1;
                $successMessages[] = "Pleno {$pleno}";
            } else {
                $errorMessages[] = "Pleno {$pleno}";
            }
        }
        
        // Hitung total kehadiran
        $kehadiran->total_kehadiran = $kehadiran->pleno_1 + $kehadiran->pleno_2 + $kehadiran->pleno_3 + $kehadiran->pleno_4;
        $kehadiran->save();

        // Buat pesan respons
        $message = '';
        if (!empty($successMessages)) {
            $message .= "Presensi berhasil untuk: " . implode(', ', $successMessages);
        }
        if (!empty($errorMessages)) {
            if ($message) $message .= " | ";
            $message .= "Sudah presensi untuk: " . implode(', ', $errorMessages);
        }

        return back()->with([
            'success' => !empty($successMessages),
            'message' => $message,
            'data' => [
                'peserta' => $peserta,
                'kehadiran' => $kehadiran
            ]
        ]);
    }
}