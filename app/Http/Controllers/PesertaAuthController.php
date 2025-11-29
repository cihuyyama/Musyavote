<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PesertaAuthController extends Controller
{
    /**
     * Login peserta dengan kode unik dan password
     */
    public function login(Request $request)
    {
        $request->validate([
            'kode_unik' => 'required|string',
            'password' => 'required|string',
        ]);

        $peserta = Peserta::where('kode_unik', $request->kode_unik)->first();

        if (!$peserta || !Hash::check($request->password, $peserta->password)) {
            throw ValidationException::withMessages([
                'kode_unik' => ['Kode unik atau password salah.'],
            ]);
        }

        // Cek status peserta
        if ($peserta->status !== 'Aktif') {
            throw ValidationException::withMessages([
                'kode_unik' => ['Akun peserta tidak aktif.'],
            ]);
        }

        $token = $peserta->createToken('peserta-mobile-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'peserta' => $peserta->load('kehadiran'),
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Logout peserta
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Get profile peserta
     */
    public function profile(Request $request)
    {
        $peserta = $request->user()->load(['kehadiran', 'calon']);

        return response()->json([
            'success' => true,
            'data' => $peserta
        ]);
    }

    /**
     * Get QR Code peserta (untuk mobile app)
     */
    public function getMyQrCode(Request $request)
    {
        $peserta = $request->user();
        $qrCodeBase64 = $peserta->generateQrCodeBase64();

        return response()->json([
            'success' => true,
            'data' => [
                'kode_unik' => $peserta->kode_unik,
                'nama' => $peserta->nama,
                'qr_code_base64' => $qrCodeBase64,
                'qr_data' => $peserta->qr_code_data
            ]
        ]);
    }

    /**
     * Change password peserta
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $peserta = $request->user();

        if (!Hash::check($request->current_password, $peserta->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini salah.'],
            ]);
        }

        $peserta->password = Hash::make($request->new_password);
        $peserta->password_plain = $request->new_password; // Update plain password juga
        $peserta->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah'
        ]);
    }
}