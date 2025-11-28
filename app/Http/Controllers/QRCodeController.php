<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    /**
     * Generate QR Code untuk peserta (return sebagai PNG image)
     */
    public function generate($pesertaId, Request $request)
    {
        $size = $request->get('size', 300);
        
        $peserta = Peserta::find($pesertaId);
        
        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta tidak ditemukan'
            ], 404);
        }

        try {
            $qrCode = $peserta->generateQrCode($size);

            return response($qrCode)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'inline; filename="qrcode-' . $peserta->kode_unik . '.png"');
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating QR Code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download QR Code sebagai file PNG
     */
    public function download($pesertaId, Request $request)
    {
        $size = $request->get('size', 300);
        
        $peserta = Peserta::find($pesertaId);
        
        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta tidak ditemukan'
            ], 404);
        }

        try {
            $qrCode = $peserta->generateQrCode($size);

            $filename = "QRCode-{$peserta->kode_unik}-{$peserta->nama}.png";
            // Clean filename from special characters
            $filename = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $filename);

            return response($qrCode)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating QR Code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get QR Code sebagai base64 (untuk frontend)
     */
    public function base64($pesertaId, Request $request)
    {
        $size = $request->get('size', 300);
        
        $peserta = Peserta::find($pesertaId);
        
        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta tidak ditemukan'
            ], 404);
        }

        try {
            $qrCodeBase64 = $peserta->generateQrCodeBase64($size);

            return response()->json([
                'success' => true,
                'data' => [
                    'kode_unik' => $peserta->kode_unik,
                    'nama' => $peserta->nama,
                    'asal_pimpinan' => $peserta->asal_pimpinan,
                    'qr_code_base64' => $qrCodeBase64,
                    'qr_data' => $peserta->qr_code_data
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating QR Code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate QR Code untuk semua peserta
     */
    public function generateAll(Request $request)
    {
        $size = $request->get('size', 200);
        $pesertas = Peserta::all();
        
        $result = [];
        foreach ($pesertas as $peserta) {
            try {
                $qrCodeBase64 = $peserta->generateQrCodeBase64($size);
                
                $result[] = [
                    'id' => $peserta->id,
                    'nama' => $peserta->nama,
                    'kode_unik' => $peserta->kode_unik,
                    'asal_pimpinan' => $peserta->asal_pimpinan,
                    'qr_code_base64' => $qrCodeBase64,
                    'download_url' => url("/api/qrcode/{$peserta->id}/download")
                ];
            } catch (\Exception $e) {
                // Skip error untuk peserta ini
                continue;
            }
        }

        return response()->json([
            'success' => true,
            'count' => count($result),
            'data' => $result
        ]);
    }

    /**
     * Generate QR Code berdasarkan kode unik peserta
     */
    public function generateByKodeUnik($kodeUnik, Request $request)
    {
        $size = $request->get('size', 300);
        
        $peserta = Peserta::where('kode_unik', $kodeUnik)->first();
        
        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta dengan kode ' . $kodeUnik . ' tidak ditemukan'
            ], 404);
        }

        try {
            $qrCode = $peserta->generateQrCode($size);

            return response($qrCode)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'inline; filename="qrcode-' . $peserta->kode_unik . '.png"');
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating QR Code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download QR Code berdasarkan kode unik
     */
    public function downloadByKodeUnik($kodeUnik, Request $request)
    {
        $size = $request->get('size', 300);
        
        $peserta = Peserta::where('kode_unik', $kodeUnik)->first();
        
        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta dengan kode ' . $kodeUnik . ' tidak ditemukan'
            ], 404);
        }

        try {
            $qrCode = $peserta->generateQrCode($size);

            $filename = "QRCode-{$peserta->kode_unik}-{$peserta->nama}.png";
            $filename = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $filename);

            return response($qrCode)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating QR Code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get QR Code base64 berdasarkan kode unik
     */
    public function base64ByKodeUnik($kodeUnik, Request $request)
    {
        $size = $request->get('size', 300);
        
        $peserta = Peserta::where('kode_unik', $kodeUnik)->first();
        
        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta dengan kode ' . $kodeUnik . ' tidak ditemukan'
            ], 404);
        }

        try {
            $qrCodeBase64 = $peserta->generateQrCodeBase64($size);

            return response()->json([
                'success' => true,
                'data' => [
                    'kode_unik' => $peserta->kode_unik,
                    'nama' => $peserta->nama,
                    'asal_pimpinan' => $peserta->asal_pimpinan,
                    'qr_code_base64' => $qrCodeBase64,
                    'qr_data' => $peserta->qr_code_data
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating QR Code: ' . $e->getMessage()
            ], 500);
        }
    }
}