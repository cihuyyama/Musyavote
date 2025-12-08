<?php

namespace App\Http\Controllers;

use App\Models\Bilik;
use App\Models\Pemilihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class BilikController extends Controller
{
    /**
     * Menampilkan daftar Bilik (Hanya Admin).
     */
    public function index()
    {
        $biliks = Bilik::withCount('pemilihan')
            ->orderBy('nama')
            ->with('pemilihan')
            ->get()
            ->makeVisible('password_plain');

        return Inertia::render('Bilik/Index', [
            'biliks' => $biliks,
        ]);
    }

    /**
     * Menampilkan form pembuatan Bilik.
     */
    public function create()
    {
        $pemilihanOptions = Pemilihan::select('id', 'nama_pemilihan')->get();

        return Inertia::render('Bilik/Create', [
            'pemilihanOptions' => $pemilihanOptions,
        ]);
    }

    /**
     * Menyimpan data Bilik baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|unique:bilik,username|max:50',
            'password' => 'required|string|min:6|confirmed',
            'pemilihan_ids' => 'nullable|array',
            'pemilihan_ids.*' => 'exists:pemilihan,id',
        ]);

        $data = $request->only(['nama', 'username']);
        $data['password_plain'] = $request->password; // Simpan plain password
        // Password akan otomatis di-hash oleh mutator setPasswordPlainAttribute

        $bilik = Bilik::create($data);

        if ($request->pemilihan_ids) {
            $bilik->pemilihan()->sync($request->pemilihan_ids);
        }

        return redirect()->route('biliks.index')->with('success', 'Bilik berhasil dibuat.');
    }

    /**
     * Menampilkan form edit Bilik.
     */
    public function edit(Bilik $bilik)
    {
        $bilik->load('pemilihan');
        $pemilihanOptions = Pemilihan::select('id', 'nama_pemilihan')->get();

        return Inertia::render('Bilik/Edit', [
            'bilik' => $bilik,
            'pemilihanOptions' => $pemilihanOptions,
        ]);
    }

    /**
     * Memperbarui data Bilik.
     */
    public function update(Request $request, Bilik $bilik)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'status' => 'nullable|string|max:255',
            'username' => 'required|string|unique:bilik,username,' . $bilik->id . ',id|max:50',
            'password' => 'nullable|string|min:6|confirmed',
            'pemilihan_ids' => 'nullable|array',
            'pemilihan_ids.*' => 'exists:pemilihan,id',
        ]);

        $bilik->nama = $request->nama;
        $bilik->status = $request->status;
        $bilik->username = $request->username;
        
        // Update password jika diisi
        if ($request->filled('password')) {
            $bilik->password_plain = $request->password; // Akan trigger setPasswordPlainAttribute
        }
        
        $bilik->save();

        // Sync pemilihan
        if ($request->has('pemilihan_ids')) {
            $bilik->pemilihan()->sync($request->pemilihan_ids);
        } else {
            $bilik->pemilihan()->detach();
        }

        return redirect()->route('biliks.index')->with('success', 'Bilik berhasil diperbarui.');
    }

    /**
     * Menghapus Bilik.
     */
    public function destroy(Bilik $bilik)
    {
        $bilik->delete();
        return redirect()->route('biliks.index')->with('success', 'Bilik berhasil dihapus.');
    }

    /**
     * Reset password Bilik dengan password acak.
     */
    public function resetPassword(Bilik $bilik)
    {
        $newPassword = $bilik->generateNewPassword();
        
        return redirect()->route('biliks.index')
            ->with('success', 'Password berhasil direset.')
            ->with('new_password', $newPassword); // Kirim password baru untuk ditampilkan
    }

    /**
     * Update status Bilik.
     */
    public function updateStatus(Bilik $bilik, Request $request)
    {
        $request->validate([
            'status' => 'required|string|in:aktif,nonaktif',
        ]);

        $bilik->status = $request->status;
        $bilik->save();

        return redirect()->route('biliks.index')
            ->with('success', 'Status bilik berhasil diperbarui.');
    }
}