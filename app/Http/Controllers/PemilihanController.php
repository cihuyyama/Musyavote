<?php

namespace App\Http\Controllers;

use App\Models\Pemilihan;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PemilihanController extends Controller
{
    /**
     * Menampilkan daftar semua Pemilihan.
     */
    public function index()
    {
        // Menghitung jumlah Bilik dan Calon
        $pemilihan = Pemilihan::withCount(['biliks', 'calon'])->get();
        return Inertia::render('Pemilihan/Index', ['pemilihan' => $pemilihan]);
    }

    /**
     * Menampilkan form pembuatan Pemilihan baru.
     */
    public function create()
    {
        $pesertas = Peserta::has('calon')->with('calon')->get();

        // Di sini kita bisa kirim data default atau opsi lainnya
        return Inertia::render('Pemilihan/Create', [
            'pesertas' => $pesertas,
            'jabatanOptions' => ['Ketua', 'Formatur'],
        ]);
    }

    /**
     * Menyimpan data Pemilihan baru.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'nama_pemilihan' => 'required|string|max:255',
            'minimal_kehadiran' => 'required|integer|min:0|max:4',
            'boleh_tidak_memilih' => 'required|boolean',
            
            'calons' => 'nullable|array',
            'calons.*' => 'exists:calon,id',
        ]);

        DB::beginTransaction();
        try {
            $pemilihan = Pemilihan::create($request->except('calons'));

            $calonIds = $request->input('calons', []); 

            if (!empty($calonIds)) {
                $pemilihan->calon()->sync($calonIds);
            }

            DB::commit();
            return redirect()->route('pemilihan.index')->with('success', 'Pemilihan dan daftar Calon berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat Pemilihan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan form edit Pemilihan.
     */
    public function edit(Pemilihan $pemilihan)
    {
        // Load relasi Bilik dan Calon jika diperlukan di form edit
        $pemilihan->load(['biliks', 'calon']); 
        
        return Inertia::render('Pemilihan/Edit', [
            'pemilihan' => $pemilihan,
        ]);
    }

    /**
     * Memperbarui data Pemilihan yang sudah ada.
     */
    public function update(Request $request, Pemilihan $pemilihan)
    {
        $request->validate([
            'nama_pemilihan' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'minimal_kehadiran' => 'required|integer|min:0|max:4', 
            'boleh_tidak_memilih' => 'required|boolean', 
        ]);

        $pemilihan->update($request->all());

        return redirect()->route('pemilihan.index')->with('success', 'Pemilihan berhasil diperbarui.');
    }

    /**
     * Menghapus Pemilihan.
     * Otomatis akan menghapus Bilik, Calon Pivot, dan Suara terkait (berkat onDelete('cascade')).
     */
    public function destroy(Pemilihan $pemilihan)
    {
        $pemilihan->delete();

        return redirect()->route('pemilihan.index')->with('success', 'Pemilihan berhasil dihapus.');
    }
}
