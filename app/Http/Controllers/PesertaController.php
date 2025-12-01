<?php

namespace App\Http\Controllers;

use App\Exports\PesertaExport;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PesertaImport;
use Illuminate\Support\Facades\Log;

class PesertaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Peserta/Index', [
            "pesertas" => Peserta::get(),
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
            // HAPUS VALIDASI STATUS
        ]);

        $fotoPath = null;

        if ($request->hasFile('file')) {
            $fotoPath = $request->file('file')->store('photos', 'public');
        }

        $peserta = Peserta::create([
            'nama' => $validated['nama'],
            'foto' => $fotoPath,
            'asal_pimpinan' => $validated['asal_pimpinan'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'status' => 'Aktif', // DEFAULT VALUE
        ]);

        // Buat kehadiran default
        $peserta->kehadiran()->create([
            'pleno_1' => 0,
            'pleno_2' => 0,
            'pleno_3' => 0,
            'pleno_4' => 0,
            'total_kehadiran' => 0
        ]);

        return to_route('peserta.index')->with('success', 'Peserta berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return Inertia::render('Peserta/Edit', [
            'peserta' => Peserta::with('kehadiran')->findOrFail($id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $peserta = Peserta::findOrFail($id);

        $validated = $request->validate([
            'nama' => ['sometimes', 'required', 'string', 'max:255'],
            'asal_pimpinan' => ['sometimes', 'required', 'string', 'max:255'],
            'jenis_kelamin' => ['sometimes', 'required', 'in:L,P'],
            'file' => ['sometimes', 'nullable', 'image', 'max:2048'],
        ]);

        $data = [];

        // Handle update data biasa
        if ($request->has('nama')) {
            $data['nama'] = $validated['nama'];
        }
        if ($request->has('asal_pimpinan')) {
            $data['asal_pimpinan'] = $validated['asal_pimpinan'];
        }
        if ($request->has('jenis_kelamin')) {
            $data['jenis_kelamin'] = $validated['jenis_kelamin'];
        }

        // Handle upload foto
        if ($request->hasFile('file')) {
            if ($peserta->foto) {
                Storage::disk('public')->delete($peserta->foto);
            }
            $data['foto'] = $request->file('file')->store('photos', 'public');
        }

        $peserta->update($data);

        // Untuk Inertia, redirect back dengan data terbaru
        if ($request->hasFile('file')) {
            // Jika hanya update foto, redirect back dengan success message
            return redirect()->back()->with('success', 'Foto peserta berhasil diupdate.');
        }

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

        return redirect()->back()->with('success', 'Peserta berhasil dihapus.');
    }

    /**
     * Memproses file Excel dan mengimpor data.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xls,xlsx|max:10240',
        ]);

        Log::info("Starting Excel import process");

        try {
            $import = new PesertaImport();

            Log::info("Excel file received: " . $request->file('file_excel')->getClientOriginalName());

            // Import data dari Excel - akan otomatis hanya membaca sheet "Data Peserta"
            Excel::import($import, $request->file('file_excel'));

            // Untuk multiple sheets, kita perlu mendapatkan hasil dari sheet yang spesifik
            $importedCount = 0;
            $errors = [];

            // Jika ingin lebih spesifik, bisa diubah sesuai kebutuhan
            Log::info("Import completed for multiple sheets");

            if ($importedCount > 0) {
                return redirect()
                    ->route('peserta.index')
                    ->with('success', "Data peserta berhasil diimpor dari Excel. {$importedCount} data ditambahkan.");
            } else {
                return redirect()
                    ->back()
                    ->with('warning', 'Tidak ada data yang diimport. Pastikan data berada di sheet "Data Peserta" dan format sesuai template.')
                    ->withInput();
            }
        } catch (\Exception $e) {
            Log::error("Import failed with exception: " . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Gagal impor data Excel: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Export data peserta ke Excel
     */
    public function export()
    {
        Log::info("Exporting peserta data to Excel");
        
        $filename = 'data-peserta-' . date('Y-m-d-H-i-s') . '.xlsx';
        
        return Excel::download(new PesertaExport, $filename);
    }
}
