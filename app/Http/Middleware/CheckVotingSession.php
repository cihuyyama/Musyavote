<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVotingSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()->getName();
        
        // Untuk routes yang membutuhkan session voting (calon page)
        if ($routeName === 'bilik.voting.calon') {
            if (!session('voting_peserta') || !session('voting_pemilihans')) {
                return redirect()->route('bilik.voting.index')
                    ->with('error', 'Sesi voting tidak valid. Silakan verifikasi ulang.');
            }
            
            // Cek apakah ada pemilihan yang eligible
            $pemilihanStatus = session('voting_pemilihans');
            $hasEligiblePemilihan = false;
            
            foreach ($pemilihanStatus as $status) {
                if ($status['eligible'] && !$status['sudah_voting']) {
                    $hasEligiblePemilihan = true;
                    break;
                }
            }
            
            if (!$hasEligiblePemilihan) {
                session()->forget(['voting_peserta', 'voting_pemilihans']);
                return redirect()->route('bilik.voting.index')
                    ->with('error', 'Tidak ada pemilihan yang dapat diikuti atau sudah melakukan voting untuk semua pemilihan.');
            }
        }

        return $next($request);
    }
}