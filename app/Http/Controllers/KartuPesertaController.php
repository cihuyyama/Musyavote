<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class KartuPesertaController extends Controller
{
    /**
     * Export kartu peserta untuk semua peserta
     */
    public function exportAll()
    {
        $pesertas = Peserta::all();
        
        $data = [
            'pesertas' => $pesertas,
            'tanggal_cetak' => now()->translatedFormat('d F Y'),
            'total_peserta' => $pesertas->count()
        ];

        $pdf = Pdf::loadView('exports.kartu-peserta-all', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOption('isHtml5ParserEnabled', true)
                  ->setOption('isRemoteEnabled', true);

        $filename = "Kartu_Peserta_Semua_" . now()->format('Y-m-d') . ".pdf";
        
        return $pdf->download($filename);
    }

    /**
     * Export kartu peserta untuk satu peserta
     */
    public function exportSingle($pesertaId)
    {
        $peserta = Peserta::with('calon')->findOrFail($pesertaId);
        
        $data = [
            'pesertas' => [$peserta], // Tetap pakai array untuk konsistensi view
            'tanggal_cetak' => now()->translatedFormat('d F Y H:i:s'),
            'total_peserta' => 1
        ];

        $pdf = Pdf::loadView('exports.kartu-peserta-all', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOption('isHtml5ParserEnabled', true)
                  ->setOption('isRemoteEnabled', true);

        $filename = "Kartu_Peserta_{$peserta->kode_unik}_" . now()->format('Y-m-d_H-i-s') . ".pdf";
        
        return $pdf->download($filename);
    }

    /**
     * Export kartu peserta berdasarkan kode unik
     */
    public function exportByKodeUnik($kodeUnik)
    {
        $peserta = Peserta::with('calon')->where('kode_unik', $kodeUnik)->firstOrFail();
        
        $data = [
            'pesertas' => [$peserta],
            'tanggal_cetak' => now()->translatedFormat('d F Y H:i:s'),
            'total_peserta' => 1
        ];

        $pdf = Pdf::loadView('exports.kartu-peserta-all', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOption('isHtml5ParserEnabled', true)
                  ->setOption('isRemoteEnabled', true);

        $filename = "Kartu_Peserta_{$peserta->kode_unik}_" . now()->format('Y-m-d_H-i-s') . ".pdf";
        
        return $pdf->download($filename);
    }

    /**
     * Preview kartu peserta (untuk testing)
     */
    public function preview($pesertaId = null)
    {
        if ($pesertaId) {
            $pesertas = Peserta::with('calon')->where('id', $pesertaId)->get();
        } else {
            $pesertas = Peserta::with('calon')->limit(20)->get();
        }
        
        $data = [
            'pesertas' => $pesertas,
            'tanggal_cetak' => now()->translatedFormat('d F Y H:i:s'),
            'total_peserta' => $pesertas->count()
        ];

        return view('exports.kartu-peserta-all', $data);
    }
}