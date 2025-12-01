<?php
// app/Http/Controllers/HasilPemilihanController.php
namespace App\Http\Controllers;

use App\Models\Pemilihan;
use App\Models\VotingRecord;
use App\Models\Calon;
use App\Models\Peserta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HasilPemilihanController extends Controller
{
    // Halaman index hasil pemilihan
    public function index()
    {
        $pemilihans = Pemilihan::withCount(['calon', 'voting_records'])->get();

        return Inertia::render('HasilPemilihan/Index', [
            'pemilihans' => $pemilihans
        ]);
    }

    // Detail hasil pemilihan
    public function show($pemilihanId)
    {
        $pemilihan = Pemilihan::with(['calon.peserta'])->findOrFail($pemilihanId);

        // Hitung hasil pemilihan secara real-time
        $hasil = $this->hitungHasilPemilihan($pemilihan);
        $statistik = $this->hitungStatistik($pemilihan);

        return Inertia::render('HasilPemilihan/Show', [
            'pemilihan' => $pemilihan,
            'hasil' => $hasil,
            'statistik' => $statistik
        ]);
    }

    // API untuk mendapatkan data hasil (real-time)
    public function getHasilData($pemilihanId)
    {
        $pemilihan = Pemilihan::with(['calon.peserta'])->findOrFail($pemilihanId);
        $hasil = $this->hitungHasilPemilihan($pemilihan);
        $statistik = $this->hitungStatistik($pemilihan);

        return response()->json([
            'hasil' => $hasil,
            'statistik' => $statistik,
            'updated_at' => now()->toISOString()
        ]);
    }

    // Method untuk menghitung hasil pemilihan REAL-TIME
    private function hitungHasilPemilihan(Pemilihan $pemilihan)
    {
        $calonWithVotes = [];

        // Total suara sah (yang memilih, bukan tidak memilih)
        $totalSuaraSah = $pemilihan->voting_records()
            ->where('tidak_memilih', false)
            ->count();

        foreach ($pemilihan->calon as $calon) {
            // Hitung suara untuk setiap calon
            $jumlahSuara = $pemilihan->voting_records()
                ->where('tidak_memilih', false)
                ->whereJsonContains('pilihan_calon', $calon->id)
                ->count();

            $persentase = $totalSuaraSah > 0 ? ($jumlahSuara / $totalSuaraSah) * 100 : 0;

            $calonWithVotes[] = [
                'calon' => $calon,
                'jumlah_suara' => $jumlahSuara,
                'persentase' => round($persentase, 2),
                'status_terpilih' => false // Default value, akan diupdate setelah sorting
            ];
        }

        // Urutkan berdasarkan jumlah suara terbanyak
        usort($calonWithVotes, function ($a, $b) {
            return $b['jumlah_suara'] <=> $a['jumlah_suara'];
        });

        // Tambahkan peringkat dan update status terpilih
        foreach ($calonWithVotes as $index => &$calon) {
            $calon['peringkat'] = $index + 1;
            $calon['status_terpilih'] = $calon['peringkat'] <= $pemilihan->jumlah_formatur_terpilih;
        }

        return $calonWithVotes;
    }

    // Method untuk menghitung statistik REAL-TIME
    private function hitungStatistik(Pemilihan $pemilihan)
    {
        $totalPeserta = Peserta::count();
        $totalVoting = $pemilihan->voting_records()->count();
        $tidakMemilihCount = $pemilihan->voting_records()
            ->where('tidak_memilih', true)
            ->count();
        $memilihCount = $totalVoting - $tidakMemilihCount;

        return [
            'total_peserta' => $totalPeserta,
            'total_voting' => $totalVoting,
            'persentase_partisipasi' => $totalPeserta > 0 ? round(($totalVoting / $totalPeserta) * 100, 2) : 0,
            'tidak_memilih' => $tidakMemilihCount,
            'memilih' => $memilihCount,
            'belum_memilih' => $totalPeserta - $totalVoting,
            'total_suara_sah' => $memilihCount,
            'kuorum_terpenuhi' => $totalVoting >= $pemilihan->minimal_kehadiran // TAMBAHKAN INI
        ];
    }
    // Export Excel
    private function exportExcel($pemilihan, $hasil, $statistik)
    {
        // Implementasi export Excel
        // Anda bisa menggunakan package seperti Maatwebsite/Laravel-Excel
        return response()->json(['message' => 'Export Excel akan diimplementasikan']);
    }

    // Export PDF
    public function exportPDF($pemilihanId)
    {
        $pemilihan = Pemilihan::with(['calon.peserta'])->findOrFail($pemilihanId);
        $hasil = $this->hitungHasilPemilihan($pemilihan);
        $statistik = $this->hitungStatistik($pemilihan);

        $data = [
            'pemilihan' => $pemilihan,
            'hasil' => $hasil,
            'statistik' => $statistik,
            'tanggal_cetak' => now()->translatedFormat('d F Y H:i:s'),
            'calon_terpilih' => array_slice($hasil, 0, $pemilihan->jumlah_formatur_terpilih)
        ];

        $pdf = Pdf::loadView('exports.hasil-pemilihan', $data);

        $filename = "Hasil_Pemilihan_{$pemilihan->nama_pemilihan}_" . now()->format('Y-m-d_H-i-s') . ".pdf";

        return $pdf->download($filename);
    }
}
