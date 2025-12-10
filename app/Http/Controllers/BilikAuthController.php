<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BilikAuthController extends Controller
{
    /**
     * Menampilkan form login untuk Bilik.
     */
    public function showLoginForm()
    {
        // Jika bilik sudah login, arahkan ke dashboard bilik
        if (Auth::guard('bilik')->check()) {
            return redirect('/login');
        }
        
        return Inertia::render('Bilik/Login');
    }

    /**
     * Memproses login Bilik.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Coba login menggunakan guard 'bilik'
        if (Auth::guard('bilik')->attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            // Arahkan ke dashboard Bilik setelah sukses
            return redirect()->route('bilik.voting.index');
        }

        // Gagal login
        return back()->withErrors([
            'username' => 'Username atau Password salah.',
        ])->onlyInput('username');
    }

    /**
     * Logout Bilik.
     */
    public function logout(Request $request)
    {
        Auth::guard('bilik')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function dashboard()
    {
        $bilik = Auth::guard('bilik')->user(); 
        // Load semua Pemilihan yang terikat pada Bilik ini
        $pemilihanAktif = $bilik->pemilihan()->where('tanggal_mulai', '<=', now())->get(); 

        return Inertia::render('Bilik/Dashboard', [
            'bilik' => $bilik,
            'pemilihanAktif' => $pemilihanAktif,
        ]);
    }
}
