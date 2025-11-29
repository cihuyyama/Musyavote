<?php
// app/Http/Controllers/VotingController.php
namespace App\Http\Controllers;

use App\Models\Bilik;
use App\Models\Peserta;
use App\Models\Pemilihan;
use App\Models\Calon;
use App\Models\VotingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VotingController extends Controller
{
    // Halaman input kode unik peserta (untuk semua pemilihan)
    public function index()
    {
        $bilik = Auth::guard('bilik')->user();
        $pemilihans = $bilik->pemilihan()->withCount('calon')->get();

        if ($pemilihans->isEmpty()) {
            return redirect()->route('login.index')
                ->with('error', 'Tidak ada pemilihan yang terhubung dengan bilik ini');
        }

        return Inertia::render('Bilik/Voting/Index', [
            'bilik' => $bilik,
            'pemilihans' => $pemilihans
        ]);
    }

    // Verifikasi peserta untuk semua pemilihan
    public function verifyPeserta(Request $request)
    {
        $request->validate([
            'kode_unik' => 'required|string',
            'password' => 'required|string'
        ]);

        $bilik = Auth::guard('bilik')->user();
        $pemilihans = $bilik->pemilihan()->get();

        if ($pemilihans->isEmpty()) {
            return back()->withErrors(['error' => 'Tidak ada pemilihan yang terhubung']);
        }

        $peserta = Peserta::byKodeUnik($request->kode_unik)->first();

        if (!$peserta) {
            return back()->withErrors(['kode_unik' => 'Kode unik tidak ditemukan']);
        }

        // Verifikasi password peserta
        if (!Auth::guard('peserta')->attempt([
            'kode_unik' => $request->kode_unik,
            'password' => $request->password
        ])) {
            return back()->withErrors(['password' => 'Password salah']);
        }

        // Cek untuk setiap pemilihan apakah sudah voting
        $pemilihanStatus = [];
        foreach ($pemilihans as $pemilihan) {
            $sudahVoting = VotingRecord::where('peserta_id', $peserta->id)
                ->where('pemilihan_id', $pemilihan->id)
                ->exists();

            $eligible = $peserta->isEligibleToVote($pemilihan);

            $pemilihanStatus[] = [
                'pemilihan' => $pemilihan,
                'sudah_voting' => $sudahVoting,
                'eligible' => $eligible
            ];
        }

        // Simpan session voting
        session([
            'voting_peserta' => $peserta->id,
            'voting_pemilihans' => $pemilihanStatus
        ]);

        return redirect()->route('bilik.voting.calon');
    }

    // Halaman daftar calon untuk semua pemilihan
    public function showCalon()
    {
        $pesertaId = session('voting_peserta');
        $pemilihanStatus = session('voting_pemilihans');
        
        if (!$pesertaId || !$pemilihanStatus) {
            return redirect()->route('bilik.voting.index');
        }

        $peserta = Peserta::findOrFail($pesertaId);
        $bilik = Auth::guard('bilik')->user();

        // Load data lengkap untuk setiap pemilihan
        $pemilihansWithCalon = [];
        foreach ($pemilihanStatus as $status) {
            if ($status['eligible'] && !$status['sudah_voting']) {
                $pemilihan = Pemilihan::with(['calon.peserta'])->find($status['pemilihan']->id);
                if ($pemilihan) {
                    $pemilihansWithCalon[] = $pemilihan;
                }
            }
        }

        // Jika tidak ada pemilihan yang eligible, redirect back
        if (empty($pemilihansWithCalon)) {
            session()->forget(['voting_peserta', 'voting_pemilihans']);
            Auth::guard('peserta')->logout();
            return redirect()->route('bilik.voting.index')
                ->withErrors(['error' => 'Tidak ada pemilihan yang dapat diikuti atau sudah melakukan voting untuk semua pemilihan']);
        }

        return Inertia::render('Bilik/Voting/Calon', [
            'peserta' => $peserta,
            'pemilihans' => $pemilihansWithCalon,
            'bilik' => $bilik,
            'pemilihanStatus' => $pemilihanStatus
        ]);
    }

    // Proses voting untuk semua pemilihan sekaligus
    public function submitVoting(Request $request)
    {
        $pesertaId = session('voting_peserta');
        $pemilihanStatus = session('voting_pemilihans');
        
        if (!$pesertaId || !$pemilihanStatus) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        $request->validate([
            'pilihan' => 'required|array',
            'pilihan.*.pemilihan_id' => 'required|exists:pemilihan,id',
            'pilihan.*.calon_ids' => 'nullable|array',
            'pilihan.*.calon_ids.*' => 'exists:calon,id',
            'pilihan.*.tidak_memilih' => 'boolean'
        ]);

        $bilik = Auth::guard('bilik')->user();
        $errors = [];
        $successCount = 0;

        // Proses setiap pemilihan
        foreach ($request->pilihan as $pilihanData) {
            $pemilihanId = $pilihanData['pemilihan_id'];
            $calonIds = $pilihanData['calon_ids'] ?? [];
            $tidakMemilih = $pilihanData['tidak_memilih'] ?? false;

            // Cari data pemilihan
            $pemilihan = null;
            foreach ($pemilihanStatus as $status) {
                if ($status['pemilihan']->id == $pemilihanId) {
                    $pemilihan = $status['pemilihan'];
                    break;
                }
            }

            if (!$pemilihan) {
                continue;
            }

            // Double check sudah voting
            $sudahVoting = VotingRecord::where('peserta_id', $pesertaId)
                ->where('pemilihan_id', $pemilihanId)
                ->exists();

            if ($sudahVoting) {
                $errors[] = "Anda sudah melakukan voting untuk {$pemilihan->nama_pemilihan}";
                continue;
            }

            // Validasi: jika tidak memilih diizinkan
            if ($tidakMemilih && !$pemilihan->boleh_tidak_memilih) {
                $errors[] = "Tidak memilih tidak diizinkan untuk {$pemilihan->nama_pemilihan}";
                continue;
            }

            // Validasi: harus memilih minimal 1 jika tidak memilih false
            if (!$tidakMemilih && empty($calonIds)) {
                $errors[] = "Silakan pilih calon atau pilih tidak memilih untuk {$pemilihan->nama_pemilihan}";
                continue;
            }

            // Validasi: maksimal pilihan
            if (!$tidakMemilih && count($calonIds) > $pemilihan->jumlah_formatur_terpilih) {
                $errors[] = "Maksimal memilih {$pemilihan->jumlah_formatur_terpilih} calon untuk {$pemilihan->nama_pemilihan}";
                continue;
            }

            // Simpan voting record
            VotingRecord::create([
                'peserta_id' => $pesertaId,
                'pemilihan_id' => $pemilihanId,
                'bilik_id' => $bilik->id,
                'pilihan_calon' => $calonIds,
                'tidak_memilih' => $tidakMemilih,
                'waktu_voting' => now()
            ]);

            $successCount++;
        }

        // Hapus session voting & logout peserta
        session()->forget(['voting_peserta', 'voting_pemilihans']);
        Auth::guard('peserta')->logout();

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Beberapa pemilihan gagal disimpan',
                'errors' => $errors,
                'success_count' => $successCount
            ], 422);
        }

        return redirect()->route('bilik.voting.index')
            ->with('success', 'Voting berhasil disimpan untuk semua pemilihan');
    }

    // Logout dari voting session
    public function logout()
    {
        // Hapus session voting & logout peserta jika ada
        session()->forget(['voting_peserta', 'voting_pemilihans']);
        Auth::guard('peserta')->logout();

        return redirect()->route('bilik.voting.index');
    }
}