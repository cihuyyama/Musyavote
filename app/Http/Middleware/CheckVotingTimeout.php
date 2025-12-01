<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVotingTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika ada session voting, cek apakah sudah melebihi batas waktu (30 menit)
        if (session('voting_start_time')) {
            $votingStartTime = session('voting_start_time');
            $timeout = 30 * 60; // 30 menit dalam detik
            
            if (time() - $votingStartTime > $timeout) {
                // Hapus session voting yang expired
                session()->forget([
                    'voting_peserta', 
                    'voting_pemilihans', 
                    'voting_start_time'
                ]);
                
                return redirect()->route('bilik.voting.index')
                    ->with('error', 'Sesi voting telah expired. Silakan mulai kembali.');
            }
        } else if (session('voting_peserta')) {
            // Set waktu mulai voting jika belum ada
            session(['voting_start_time' => time()]);
        }

        return $next($request);
    }
}