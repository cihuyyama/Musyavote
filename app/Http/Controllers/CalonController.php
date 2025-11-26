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
    public function index(Request $request)
    {

        // 3. Eager Load Relasi dan Ambil Data
        $pesertas = Peserta::has('calon')->with('calon')->get();

        // dd($pesertas); // Hapus ini setelah testing

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
        // 1. Validasi Array data yang dikirim dari Vue
        $data = $request->validate([
            'calons' => ['required', 'array', 'min:1'],
            'calons.*.peserta_id' => [
                'required',
                'exists:peserta,id',
            ],
            'calons.*.jabatan' => ['required', 'in:Ketua,Formatur'],
        ]);

        DB::beginTransaction();
        try {
            $newCount = 0;
            $duplicateCheck = [];

            foreach ($data['calons'] as $index => $calonData) {
                $pesertaId = $calonData['peserta_id'];
                $jabatanValue = $calonData['jabatan'];

                // Cek 1: Duplikasi dalam satu request
                if (isset($duplicateCheck["{$pesertaId}-{$jabatanValue}"])) {
                    throw ValidationException::withMessages([
                        "calons.{$index}.peserta_id" => "Peserta ini sudah dipilih di form lain sebagai {$jabatanValue}.",
                    ]);
                }
                $duplicateCheck["{$pesertaId}-{$jabatanValue}"] = true;

                // Cek 2: Duplikasi di database (Melanggar UNIQUE KEY (peserta_id, jabatan))
                $existingCalon = Calon::where('peserta_id', $pesertaId)
                    ->where('jabatan', $jabatanValue)
                    ->exists();

                if ($existingCalon) {
                    throw ValidationException::withMessages([
                        "calons.{$index}.peserta_id" => "Peserta ini sudah terdaftar sebagai Calon {$jabatanValue}.",
                    ]);
                }

                // Buat entri Calon baru
                Calon::create([
                    'peserta_id' => $pesertaId,
                    'jabatan' => $jabatanValue, // Menyimpan ke kolom 'jabatan'
                ]);
                $newCount++;
            }

            DB::commit();

            return redirect()->route('calon.index')->with('success', "{$newCount} Calon baru berhasil ditambahkan.");
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e; // Throw back ValidationException ke Vue
        } catch (\Exception $e) {
            DB::rollBack();
            // Menangani QueryException umum atau error sistem
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data calon.');
        }
    }

    public function destroy(string $id)
    {
        $calon = Calon::findOrFail($id);
        $calon->delete();
    }
}
