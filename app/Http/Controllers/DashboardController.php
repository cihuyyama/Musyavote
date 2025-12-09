<?php

namespace App\Http\Controllers;

use App\Models\AdminPresensi;
use App\Models\Bilik;
use App\Models\Calon;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $peserta = Peserta::with('kehadiran')->orderBy('nama')->get();

        $totalPeserta = $peserta->count();

        $jenisKelaminStats = [
            'laki_laki' => [
                'count' => $peserta->where('jenis_kelamin', 'L')->count(),
                'percentage' => $totalPeserta > 0
                    ? round(($peserta->where('jenis_kelamin', 'L')->count() / $totalPeserta) * 100, 2)
                    : 0,
            ],
            'perempuan' => [
                'count' => $peserta->where('jenis_kelamin', 'P')->count(),
                'percentage' => $totalPeserta > 0
                    ? round(($peserta->where('jenis_kelamin', 'P')->count() / $totalPeserta) * 100, 2)
                    : 0,
            ],
            'total' => $totalPeserta,
        ];

        $totalBilik = Bilik::count();
        $activeBilik = Bilik::where('status', 'active')->count();

        $bilikStats = [
            'total' => $totalBilik,
            'active' => $activeBilik,
            'inactive' => $totalBilik - $activeBilik,
        ];

        // Statistik Calon (berdasarkan jabatan)
        $totalCalon = Calon::count();
        $calonKetua = Calon::where('jabatan', 'ketua')->count();
        $calonFormatur = Calon::where('jabatan', 'formatur')->count();

        // Jika ada jabatan lain
        $jabatanCounts = Calon::selectRaw('jabatan, COUNT(*) as count')
            ->groupBy('jabatan')
            ->pluck('count', 'jabatan')
            ->toArray();

        $calonStats = [
            'total' => $totalCalon,
            'calon_ketua' => $calonKetua,
            'calon_formatur' => $calonFormatur,
            'calon_lainnya' => $totalCalon - $calonKetua - $calonFormatur,
            'detail_by_jabatan' => $jabatanCounts,
        ];

        return Inertia::render('Dashboard', [
            'peserta' => $peserta,
            'totalPeserta' => $totalPeserta,
            'kehadiranStats' => [
                'pleno_1' => $peserta->where('kehadiran.pleno_1', 1)->count(),
                'pleno_2' => $peserta->where('kehadiran.pleno_2', 1)->count(),
                'pleno_3' => $peserta->where('kehadiran.pleno_3', 1)->count(),
                'pleno_4' => $peserta->where('kehadiran.pleno_4', 1)->count(),
            ],
            'jenisKelaminStats' => $jenisKelaminStats,
            'bilikStats' => $bilikStats, // Tambahkan statistik bilik
            'calonStats' => $calonStats, // Tambahkan statistik calon
        ]);
    }
}
