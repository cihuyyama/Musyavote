<?php
// File: app/Http/Controllers/CalonController.php - DIUBAH

namespace App\Http\Controllers;

use App\Models\Calon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Exports\CalonExport;
use App\Exports\GenericExport;
use App\Imports\CalonImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class CalonController extends Controller
{
    public function index(Request $request)
    {
        $calons = Calon::orderBy('jabatan')
            ->orderBy('nomor_urut')
            ->get();

        return Inertia::render('Calon/Index', [
            'calons' => $calons,
            'jabatanOptions' => ['Ketua', 'Formatur'],
        ]);
    }

    public function create()
    {
        // Generate nomor urut berikutnya untuk masing-masing jabatan
        $nextKetua = Calon::where('jabatan', 'Ketua')->max('nomor_urut') ?? 0;
        $nextFormatur = Calon::where('jabatan', 'Formatur')->max('nomor_urut') ?? 0;

        return Inertia::render('Calon/Create', [
            'jabatanOptions' => ['Ketua', 'Formatur'],
            'nextNomorUrut' => [
                'Ketua' => $nextKetua + 1,
                'Formatur' => $nextFormatur + 1,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'asal_pimpinan' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'nomor_urut' => ['required', 'integer', 'min:1'],
            'jabatan' => ['required', 'in:Ketua,Formatur'],
            'file' => ['nullable', 'image', 'max:2048'],
        ]);

        DB::beginTransaction();
        try {
            // Cek duplikasi nomor urut untuk jabatan yang sama
            $existingCalon = Calon::where('jabatan', $validated['jabatan'])
                ->where('nomor_urut', $validated['nomor_urut'])
                ->exists();

            if ($existingCalon) {
                throw ValidationException::withMessages([
                    'nomor_urut' => "Nomor urut {$validated['nomor_urut']} sudah digunakan untuk calon {$validated['jabatan']}.",
                ]);
            }

            // Handle upload foto
            $fotoPath = null;
            if ($request->hasFile('file')) {
                $fotoPath = $request->file('file')->store('calon_fotos', 'public');
            }

            Calon::create([
                'nama' => $validated['nama'],
                'asal_pimpinan' => $validated['asal_pimpinan'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'nomor_urut' => $validated['nomor_urut'],
                'jabatan' => $validated['jabatan'],
                'foto' => $fotoPath,
            ]);

            DB::commit();

            return redirect()->route('calon.index')->with('success', 'Calon berhasil ditambahkan.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating calon: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data calon.');
        }
    }

    public function edit(string $id)
    {
        $calon = Calon::findOrFail($id);

        return Inertia::render('Calon/Edit', [
            'calon' => $calon,
            'jabatanOptions' => ['Ketua', 'Formatur'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $calon = Calon::findOrFail($id);

        Log::info('Update calon request:', [
            'hasFile' => $request->hasFile('file'),
            'method' => $request->method(),
            'isPatch' => $request->isMethod('patch'),
            'isPost' => $request->isMethod('post'),
            'has_method' => $request->has('_method')
        ]);

        // Handle untuk update foto saja (bisa dari PATCH langsung atau POST dengan _method)
        if ($request->hasFile('file') && ($request->isMethod('patch') || ($request->isMethod('post') && $request->has('_method')))) {
            Log::info('Processing photo update');

            $request->validate([
                'file' => ['required', 'image', 'max:2048'],
            ]);

            // Hapus foto lama jika ada
            if ($calon->foto) {
                Storage::disk('public')->delete($calon->foto);
                Log::info('Deleted old photo:', ['old_photo' => $calon->foto]);
            }

            // Upload foto baru
            try {
                $fotoPath = $request->file('file')->store('calon_fotos', 'public');

                Log::info('New photo uploaded:', [
                    'path' => $fotoPath,
                    'full_path' => storage_path('app/public/' . $fotoPath),
                    'file_exists' => file_exists(storage_path('app/public/' . $fotoPath))
                ]);

                $calon->update(['foto' => $fotoPath]);

                Log::info('Calon photo updated successfully:', [
                    'id' => $calon->id,
                    'new_foto' => $fotoPath
                ]);

                return redirect()->back()->with('success', 'Foto calon berhasil diupdate.');
            } catch (\Exception $e) {
                Log::error('Error uploading photo:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return redirect()->back()->with('error', 'Gagal mengupload foto: ' . $e->getMessage());
            }
        }

        // Update data biasa (dari form edit lengkap)
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'asal_pimpinan' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'nomor_urut' => ['required', 'integer', 'min:1'],
            'jabatan' => ['required', 'in:Ketua,Formatur'],
            'file' => ['nullable', 'image', 'max:2048'],
        ]);

        DB::beginTransaction();
        try {
            // Cek duplikasi nomor urut untuk jabatan yang sama (kecuali untuk calon ini)
            $existingCalon = Calon::where('jabatan', $validated['jabatan'])
                ->where('nomor_urut', $validated['nomor_urut'])
                ->where('id', '!=', $id)
                ->exists();

            if ($existingCalon) {
                throw ValidationException::withMessages([
                    'nomor_urut' => "Nomor urut {$validated['nomor_urut']} sudah digunakan untuk calon {$validated['jabatan']}.",
                ]);
            }

            // Handle upload foto (jika dari form edit)
            if ($request->hasFile('file')) {
                // Hapus foto lama jika ada
                if ($calon->foto) {
                    Storage::disk('public')->delete($calon->foto);
                }

                $fotoPath = $request->file('file')->store('calon_fotos', 'public');
                $validated['foto'] = $fotoPath;
            } else {
                // Jika tidak ada file baru, pertahankan foto lama
                $validated['foto'] = $calon->foto;
            }

            $calon->update([
                'nama' => $validated['nama'],
                'asal_pimpinan' => $validated['asal_pimpinan'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'nomor_urut' => $validated['nomor_urut'],
                'jabatan' => $validated['jabatan'],
                'foto' => $validated['foto'],
            ]);

            DB::commit();

            return redirect()->route('calon.index')->with('success', 'Calon berhasil diperbarui.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating calon: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data calon.');
        }
    }

    public function destroy(string $id)
    {
        $calon = Calon::findOrFail($id);

        // Hapus foto jika ada
        if ($calon->foto) {
            Storage::disk('public')->delete($calon->foto);
        }

        $calon->delete();

        return redirect()->back()->with('success', 'Calon berhasil dihapus.');
    }

    /**
     * Import data calon dari Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xls,xlsx,csv|max:10240',
        ]);

        Log::info("Starting Calon import process");

        try {
            $import = new CalonImport();

            Log::info("Excel file received: " . $request->file('file_excel')->getClientOriginalName());

            Excel::import($import, $request->file('file_excel'));

            $importedCount = $import->getRowCount();
            $errors = $import->getErrors();

            $message = "Data calon berhasil diimpor dari Excel. {$importedCount} data ditambahkan.";

            if (!empty($errors)) {
                $errorMessage = implode(', ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $errorMessage .= ' dan ' . (count($errors) - 5) . ' error lainnya';
                }
                $message .= " Beberapa error ditemukan: " . $errorMessage;
            }

            if ($importedCount > 0) {
                return redirect()
                    ->route('calon.index')
                    ->with('success', $message);
            } else {
                return redirect()
                    ->back()
                    ->with('warning', 'Tidak ada data yang diimport. Pastikan format file sesuai template.')
                    ->withInput();
            }
        } catch (\Exception $e) {
            Log::error("Calon import failed: " . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Gagal impor data calon: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Export data calon ke Excel
     */
    public function export()
    {
        Log::info("Exporting calon data to Excel");

        $filename = 'data-calon-' . date('Y-m-d-H-i-s') . '.xlsx';

        return Excel::download(new CalonExport, $filename);
    }

    /**
     * Download template Excel untuk import
     */
    public function downloadTemplate()
    {
        Log::info("Downloading calon import template");

        $filename = 'template-import-calon.xlsx';

        // Buat array data kosong dengan header saja
        $data = [[
            'Nama',
            'Asal Pimpinan',
            'Jenis Kelamin (L/P)',
            'Nomor Urut',
            'Jabatan (Ketua/Formatur)',
            'Foto (opsional)'
        ]];

        return Excel::download(
            new GenericExport($data, 'Template Import Calon'),
            $filename
        );
    }
}
