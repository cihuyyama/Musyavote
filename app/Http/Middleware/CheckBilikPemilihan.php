<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckBilikPemilihan
{
    public function handle(Request $request, Closure $next): Response
    {
        $bilik = Auth::guard('bilik')->user();
        
        // Cek apakah bilik memiliki pemilihan
        if ($bilik->pemilihan()->count() === 0) {
            return redirect()->route('bilik.dashboard')
                ->with('error', 'Bilik ini belum terhubung dengan pemilihan apapun.');
        }

        return $next($request);
    }
}