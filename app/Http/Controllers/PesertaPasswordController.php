<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PesertaPasswordController extends Controller
{
    /**
     * Get semua peserta dengan info password
     */
    public function index(Request $request)
    {
        $pesertas = Peserta::select([
                'id', 'kode_unik', 'nama', 'asal_pimpinan', 'jenis_kelamin', 'status', 
                'password_plain', 'created_at'
            ])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where('nama', 'like', '%' . $request->search . '%')
                      ->orWhere('kode_unik', 'like', '%' . $request->search . '%');
            })
            ->orderBy('kode_unik')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pesertas,
            'count' => $pesertas->count()
        ]);
    }

    /**
     * Reset password peserta ke random 6 digit
     */
    public function resetPassword(Request $request, $pesertaId)
    {
        $peserta = Peserta::find($pesertaId);

        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta tidak ditemukan'
            ], 404);
        }

        $newPassword = $peserta->generateNewPassword();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset',
            'data' => [
                'peserta' => [
                    'id' => $peserta->id,
                    'kode_unik' => $peserta->kode_unik,
                    'nama' => $peserta->nama,
                    'new_password' => $newPassword
                ]
            ]
        ]);
    }

    /**
     * Bulk reset password untuk multiple peserta
     */
    public function bulkResetPassword(Request $request)
    {
        $request->validate([
            'peserta_ids' => 'required|array',
            'peserta_ids.*' => 'exists:peserta,id'
        ]);

        $results = [];
        foreach ($request->peserta_ids as $pesertaId) {
            $peserta = Peserta::find($pesertaId);
            $newPassword = $peserta->generateNewPassword();
            
            $results[] = [
                'id' => $peserta->id,
                'kode_unik' => $peserta->kode_unik,
                'nama' => $peserta->nama,
                'new_password' => $newPassword
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset untuk ' . count($results) . ' peserta',
            'data' => $results
        ]);
    }

    /**
     * Set manual password untuk peserta
     */
    public function setManualPassword(Request $request, $pesertaId)
    {
        $request->validate([
            'new_password' => 'required|string|min:6'
        ]);

        $peserta = Peserta::find($pesertaId);

        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta tidak ditemukan'
            ], 404);
        }

        $peserta->password = Hash::make($request->new_password);
        $peserta->password_plain = $request->new_password;
        $peserta->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah',
            'data' => [
                'peserta' => [
                    'id' => $peserta->id,
                    'kode_unik' => $peserta->kode_unik,
                    'nama' => $peserta->nama
                ]
            ]
        ]);
    }
}