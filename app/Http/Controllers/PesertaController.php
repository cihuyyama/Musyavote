<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PesertaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Peserta/Index', [
            "pesertas" => Peserta::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Peserta/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'file' => ['nullable', 'image', 'max:2048'],
            'asal_pimpinan' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
        ]);

        $fotoPath = null;

        if ($request->hasFile('file')) {
            $fotoPath = $request->file('file')->store('photos', 'public');
        }

        Peserta::create([
            'nama' => $validated['nama'],
            'foto' => $fotoPath,
            'asal_pimpinan' => $validated['asal_pimpinan'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'status' => $request->input('status'),
        ]);

        return to_route('peserta.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return Inertia::render('Peserta/Edit', [
            'peserta' => Peserta::findOrFail($id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Ambil instance model
        $peserta = Peserta::findOrFail($id);

        // 2. Lakukan Validasi
        // Catatan: Jika Anda mengirim data dengan Inertia, pastikan nama field file adalah 'file' 
        //         atau 'foto' sesuai yang digunakan di Vue. Kita asumsikan 'foto' adalah path lama.
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'asal_pimpinan' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'status' => ['nullable', 'in:Aktif,Tidak Aktif'],

            // Asumsikan field file yang diupload dari Vue bernama 'new_foto'
            'new_foto' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = $request->only([
            'nama', // Pastikan mapping nama field sesuai DB
            'asal_pimpinan',
            'jenis_kelamin',
            'status'
        ]);

        if ($request->hasFile('file')) {
            if ($peserta->foto) {
                Storage::disk('public')->delete($peserta->foto);
            }

            $data['foto'] = $request->file('file')->store('photos', 'public');
        } elseif ($request->input('remove_foto')) {
            if ($peserta->foto) {
                Storage::disk('public')->delete($peserta->foto);
            }
            $data['foto'] = null; // Set path foto di database menjadi NULL
        }
        $peserta->update($data);

        return to_route('peserta.index')->with('success', 'Data peserta berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $peserta = Peserta::findOrFail($id);

        if ($peserta->foto) {
            Storage::disk('public')->delete($peserta->foto);
        }

        $peserta->delete();
    }
}
