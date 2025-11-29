<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Peserta;
use Illuminate\Http\Request;
use App\Exports\PesertaExport;
use App\Exports\KartuPesertaExport;
use Maatwebsite\Excel\Facades\Excel;

class PesertaExportController extends Controller
{
    /**
     * Export data peserta dengan password
     */
    public function exportPeserta(Request $request)
    {
        $format = $request->get('format', 'excel');
        
        if ($format === 'excel') {
            return Excel::download(new PesertaExport, 'data-peserta-' . date('Y-m-d') . '.xlsx');
        }
        
        if ($format === 'csv') {
            return Excel::download(new PesertaExport, 'data-peserta-' . date('Y-m-d') . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        // Default return JSON
        $pesertas = Peserta::orderBy('kode_unik')->get();
        $exportData = $pesertas->map(function ($peserta) {
            return $peserta->toExportArray();
        });

        return response()->json([
            'success' => true,
            'data' => $exportData,
            'count' => $exportData->count()
        ]);
    }

    /**
     * Export kartu peserta dalam format Excel
     */
    public function exportKartuPesertaExcel(Request $request)
    {
        $pesertaIds = $request->get('peserta_ids', []);
        
        return Excel::download(
            new KartuPesertaExport($pesertaIds), 
            'kartu-peserta-' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export single kartu peserta
     */
    public function exportKartuPeserta($pesertaId)
    {
        $peserta = Peserta::find($pesertaId);

        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta tidak ditemukan'
            ], 404);
        }

        $qrCodeBase64 = $peserta->generateQrCodeBase64();

        return response()->json([
            'success' => true,
            'data' => [
                'peserta' => $peserta,
                'qr_code_base64' => $qrCodeBase64,
                'kartu_data' => [
                    'kode_unik' => $peserta->kode_unik,
                    'nama' => $peserta->nama,
                    'asal_pimpinan' => $peserta->asal_pimpinan,
                    'password' => $peserta->password_plain,
                    'qr_code' => $qrCodeBase64
                ]
            ]
        ]);
    }

    /**
     * Bulk export kartu peserta
     */
    public function bulkExportKartuPeserta(Request $request)
    {
        $request->validate([
            'peserta_ids' => 'required|array',
            'peserta_ids.*' => 'exists:peserta,id'
        ]);

        $pesertas = Peserta::whereIn('id', $request->peserta_ids)
            ->orderBy('kode_unik')
            ->get();

        $kartuData = $pesertas->map(function ($peserta) {
            return [
                'kode_unik' => $peserta->kode_unik,
                'nama' => $peserta->nama,
                'asal_pimpinan' => $peserta->asal_pimpinan,
                'password' => $peserta->password_plain,
                'qr_code_base64' => $peserta->generateQrCodeBase64(150)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $kartuData,
            'count' => $kartuData->count()
        ]);
    }
}