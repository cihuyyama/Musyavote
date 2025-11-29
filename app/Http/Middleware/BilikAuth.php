<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class BilikAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('bilik')->check()) {
            return redirect()->route('bilik.login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}