<?php

namespace App\Http\Controllers;

use App\Models\Pemilihan;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PemilihanCalonController extends Controller
{
    /**
     * Menampilkan daftar Calon yang terikat pada Pemilihan tertentu.
     * (READ)
     */
    public function index(Pemilihan $pemilihan)
    {
        // Eager load calon beserta data pesertanya
        $calons = $pemilihan->calon()->orderBy('jabatan')->get();
        
        // Ambil daftar semua peserta yang belum menjadi calon di pemilihan ini (untuk opsi tambah)
        $existingCalonIds = $calons->pluck('id')->toArray();
        $availablePesertas = Peserta::whereNotIn('id', $existingCalonIds)
                                    ->select('id', 'nama', 'asal_pimpinan')
                                    ->get();

        return Inertia::render('Pemilihan/Calon/Index', [
            'pemilihan' => $pemilihan,
            'calons' => $calons,
            'availablePesertas' => $availablePesertas,
            'jabatanOptions' => ['Ketua', 'Formatur'],
        ]);
    }

    /**
     * Menyimpan satu atau lebih Peserta sebagai Calon di Pemilihan ini.
     * (CREATE)
     */
    public function store(Request $request, Pemilihan $pemilihan)
    {
        $data = $request->validate([
            // Menerima array untuk penambahan massal
            'calons' => ['required', 'array', 'min:1'],
            'calons.*.peserta_id' => ['required', 'exists:peserta,id'],
            'calons.*.jabatan' => ['required', 'in:Ketua,Formatur'],
        ]);

        DB::beginTransaction();
        try {
            $existingCount = 0;
            $newCount = 0;

            foreach ($data['calons'] as $calonData) {
                // Gunakan attach() atau findOrCreate
                $result = $pemilihan->calon()->syncWithoutDetaching([
                    $calonData['peserta_id'] => [
                        'jabatan' => $calonData['jabatan']
                    ]
                ]);

                // Hitung yang berhasil ditambahkan (Laravel 9+ returns array with 'attached')
                if (in_array($calonData['peserta_id'], $result['attached'])) {
                    $newCount++;
                } else {
                    $existingCount++;
                }
            }
            
            DB::commit();

            if ($existingCount > 0) {
                 return redirect()->back()->with('warning', "{$newCount} calon ditambahkan. {$existingCount} peserta sudah terdaftar sebagai calon dengan jabatan tersebut.");
            }
            
            return redirect()->back()->with('success', "{$newCount} calon berhasil ditetapkan untuk {$pemilihan->nama_pemilihan}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan calon. Pastikan data unik.');
        }
    }

    /**
     * Menghapus Peserta dari daftar Calon di Pemilihan ini.
     * (DELETE)
     */
    public function destroy(Pemilihan $pemilihan, Peserta $peserta, string $jabatan)
    {
        // Hapus relasi berdasarkan ID peserta dan nilai jabatan di tabel pivot
        $pemilihan->calon()->wherePivot('jabatan', $jabatan)->detach($peserta->id);

        return redirect()->back()->with('success', "Calon {$peserta->nama} ($jabatan) berhasil dihapus dari pemilihan.");
    }
}
