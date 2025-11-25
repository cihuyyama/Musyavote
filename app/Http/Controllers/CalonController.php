<?php

namespace App\Http\Controllers;

use App\Models\Calon;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CalonController extends Controller
{
    // Menampilkan daftar peserta yang bisa dijadikan calon (Calon Manager View)
    public function index()
    {
        // Eager load data pencalonan
        $pesertas = Peserta::has('calon')->with('calon')->orderBy('nama')->get();

        // dd( $pesertas );

        return Inertia::render('Calon/Index', [
            'pesertas' => $pesertas,
            'jabatanOptions' => ['Ketua', 'Formatur'],
        ]);
    }

    public function create()
    {
        return Inertia::render('Calon/Create', [
            'pesertas' => Peserta::orderBy('nama')->get(),
            'jabatanOptions' => ['Ketua', 'Formatur'],
        ]);
    }

    // Menambahkan status pencalonan (Create)
    public function store(Request $request)
    {
        $data = $request->validate([
            'calons' => ['required', 'array', 'min:1'],

            'calons.*.peserta_id' => ['required', 'exists:peserta,id'],
            'calons.*.jabatan' => ['required', 'in:Ketua,Formatur'],
        ]);

        // 2. Proses dan Simpan Data
        DB::beginTransaction();
        try {
            $submittedEntries = [];
            $newlyCreatedCount = 0;

            foreach ($data['calons'] as $index => $calonData) {
                $pesertaId = $calonData['peserta_id'];
                $jabatan = $calonData['jabatan'];

                if (isset($submittedEntries[$pesertaId][$jabatan])) {
                    throw ValidationException::withMessages([
                        "calons.{$index}.peserta_id" => "Duplikasi: Peserta ini sudah dipilih sebagai calon '{$jabatan}' di form lain dalam pengiriman ini.",
                    ]);
                }
                $submittedEntries[$pesertaId][$jabatan] = true;

                $calon = Calon::firstOrCreate([
                    'peserta_id' => $pesertaId,
                    'jabatan' => $jabatan,
                ]);

                if ($calon->wasRecentlyCreated) {
                    $newlyCreatedCount++;
                }
            }

            DB::commit();

            return redirect()->route('calon.index')->with('success', "Berhasil menetapkan **{$newlyCreatedCount}** status calon baru.");
        } catch (\Exception $e) {
            DB::rollBack();

            if ($e instanceof ValidationException) {
                throw $e;
            }

            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'integrity constraint violation')) {
                return redirect()->back()->withErrors(['calons' => 'Salah satu atau lebih peserta yang Anda coba simpan sudah terdaftar sebagai calon dengan jabatan tersebut.']);
            }

            return redirect()->back()->withErrors(['calons' => 'Terjadi kesalahan sistem saat menyimpan data calon.']);
        }
    }

    public function destroy(Calon $calon)
    {
        $calon->delete();
        return redirect()->route('calon.index')->with('success', 'Status calon berhasil dihapus.');
    }
}
