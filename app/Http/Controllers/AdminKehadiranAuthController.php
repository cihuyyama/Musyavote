<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Peserta;
use App\Models\AdminPresensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AdminKehadiranAuthController extends Controller
{
    /**
     * Menampilkan form login admin kehadiran
     */
    public function login()
    {
        return Inertia::render('AdminKehadiran/Login');
    }

    /**
     * Memproses login admin kehadiran
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'remember' => 'boolean'
        ]);

        // Cek apakah admin ada dan aktif
        $admin = AdminPresensi::where('username', $credentials['username'])->first();

        // Validasi admin aktif
        if ($admin && $admin->status !== 'active') {
            throw ValidationException::withMessages([
                'username' => 'Akun admin tidak aktif.',
            ]);
        }

        // Coba login menggunakan guard 'admin_kehadiran'
        if (Auth::guard('admin_kehadiran')->attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password']
        ], $request->boolean('remember', false))) {

            $request->session()->regenerate();

            return redirect()->intended(route('admin-kehadiran.dashboard'));
        }

        // Gagal login
        throw ValidationException::withMessages([
            'username' => 'Username atau password salah.',
        ]);
    }

    /**
     * Logout admin kehadiran
     */
    public function logout(Request $request)
    {
        Auth::guard('admin_kehadiran')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Dashboard admin kehadiran
     */
    public function dashboard()
    {
        $admin = Auth::guard('admin_kehadiran')->user();
        $peserta = Peserta::with('kehadiran')->orderBy('nama')->get();

        if (!$admin) {
            return redirect()->route('admin-kehadiran.login');
        }

        // Validasi admin masih aktif
        if ($admin->status !== 'active') {
            Auth::guard('admin_kehadiran')->logout();
            return redirect()->route('admin-kehadiran.login')->withErrors([
                'message' => 'Akun Anda telah dinonaktifkan.'
            ]);
        }

        return Inertia::render('AdminKehadiran/Dashboard', [
            'admin' => [
                'nama' => $admin->nama,
                'username' => $admin->username,
                'pleno_akses' => $admin->pleno_akses,
                'status' => $admin->status,
            ],
            'peserta' => $peserta,
            'totalPeserta' => $peserta->count(),
            'kehadiranStats' => [
                'pleno_1' => $peserta->where('kehadiran.pleno_1', 1)->count(),
                'pleno_2' => $peserta->where('kehadiran.pleno_2', 1)->count(),
                'pleno_3' => $peserta->where('kehadiran.pleno_3', 1)->count(),
                'pleno_4' => $peserta->where('kehadiran.pleno_4', 1)->count(),
            ],
        ]);
    }
}