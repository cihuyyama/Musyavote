<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use App\Models\Kehadiran;
use App\Models\Pemilihan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PresensiController extends Controller
{
    // Scan QR Code untuk presensi
    public function scanPresensi(Request $request, $pleno)
    {
        $request->validate([
            'kode_unik' => 'required|string'
        ]);

        // Cari peserta berdasarkan kode unik
        $peserta = Peserta::byKodeUnik($request->kode_unik)->first();

        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta tidak ditemukan'
            ], 404);
        }

        // Validasi pleno (1-4)
        if (!in_array($pleno, [1, 2, 3, 4])) {
            return response()->json([
                'success' => false,
                'message' => 'Pleno tidak valid. Harus antara 1-4'
            ], 400);
        }

        // Cek atau buat data kehadiran
        $kehadiran = Kehadiran::firstOrCreate(
            ['peserta_id' => $peserta->id],
            [
                'pleno_1' => 0,
                'pleno_2' => 0,
                'pleno_3' => 0,
                'pleno_4' => 0,
                'total_kehadiran' => 0
            ]
        );

        // Update kehadiran berdasarkan pleno
        $plenoField = "pleno_{$pleno}";
        
        if ($kehadiran->$plenoField == 0) {
            $kehadiran->$plenoField = 1;
            $kehadiran->total_kehadiran = $kehadiran->pleno_1 + $kehadiran->pleno_2 + $kehadiran->pleno_3 + $kehadiran->pleno_4;
            $kehadiran->save();

            return response()->json([
                'success' => true,
                'message' => "Presensi Pleno {$pleno} berhasil untuk {$peserta->nama}",
                'data' => [
                    'peserta' => $peserta,
                    'kehadiran' => $kehadiran
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "{$peserta->nama} sudah melakukan presensi Pleno {$pleno}"
        ], 400);
    }

    // Get data peserta by kode unik (untuk login bilik)
    public function getPesertaByKode(Request $request, $kode_unik)
    {
        $peserta = Peserta::with(['kehadiran', 'calon'])
                        ->byKodeUnik($kode_unik)
                        ->first();

        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta tidak ditemukan'
            ], 404);
        }

        // Cek jika ada pemilihan_id dalam request untuk validasi eligibility
        if ($request->has('pemilihan_id')) {
            $pemilihan = Pemilihan::find($request->pemilihan_id);
            $eligible = $pemilihan ? $peserta->isEligibleToVote($pemilihan) : false;
            
            return response()->json([
                'success' => true,
                'data' => $peserta,
                'eligible_to_vote' => $eligible,
                'minimal_kehadiran' => $pemilihan->minimal_kehadiran ?? null,
                'total_kehadiran' => $peserta->kehadiran->total_kehadiran ?? 0
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $peserta
        ]);
    }

    // Get riwayat kehadiran peserta
    public function getRiwayatKehadiran($kode_unik)
    {
        $peserta = Peserta::with('kehadiran')->byKodeUnik($kode_unik)->first();

        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta tidak ditemukan'
            ], 404);
        }

        $kehadiran = $peserta->kehadiran;

        return response()->json([
            'success' => true,
            'data' => [
                'peserta' => $peserta,
                'kehadiran' => $kehadiran,
                'riwayat' => [
                    'pleno_1' => $kehadiran->pleno_1 ?? 0,
                    'pleno_2' => $kehadiran->pleno_2 ?? 0,
                    'pleno_3' => $kehadiran->pleno_3 ?? 0,
                    'pleno_4' => $kehadiran->pleno_4 ?? 0,
                    'total_kehadiran' => $kehadiran->total_kehadiran ?? 0
                ]
            ]
        ]);
    }

    public function getAllRiwayatKehadiran()
    {
        $kehadiranList = Peserta::with('kehadiran')->get();
        return Inertia::render('Kehadiran/Index', [
            'pesertas' => $kehadiranList
        ]);
    }
}