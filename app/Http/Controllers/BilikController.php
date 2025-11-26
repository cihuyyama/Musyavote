<?php

namespace App\Http\Controllers;

use App\Models\Bilik;
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
        $biliks = Bilik::orderBy('nama')->get();

        return Inertia::render('Bilik/Index', [
            'biliks' => $biliks,
        ]);
    }

    /**
     * Menampilkan form pembuatan Bilik.
     */
    public function create()
    {
        return Inertia::render('Bilik/Create');
    }

    /**
     * Menyimpan data Bilik baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|unique:bilik,username|max:50',
            'password' => 'required|string|min:6|confirmed', // Harus ada field password_confirmation
        ]);

        Bilik::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password' => Hash::make($request->password), // Hashing password
        ]);

        return redirect()->route('biliks.index')->with('success', 'Bilik berhasil dibuat.');
    }

    /**
     * Menampilkan form edit Bilik.
     */
    public function edit(Bilik $bilik)
    {
        return Inertia::render('Bilik/Edit', [
            'bilik' => $bilik,
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
            'password' => 'nullable|string|min:6|confirmed', // Password opsional saat edit
        ]);
        $bilik->nama = $request->nama;
        $bilik->status = $request->status;
        $bilik->username = $request->username;
        if ($request->filled('password')) {
            $bilik->password = Hash::make($request->password);
        }
        $bilik->save();
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
}
