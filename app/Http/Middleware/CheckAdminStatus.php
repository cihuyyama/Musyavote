<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdminStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $admin = Auth::guard('admin_kehadiran')->user();
        
        if ($admin && $admin->status !== 'active') {
            Auth::guard('admin_kehadiran')->logout();
            return redirect()->route('admin-kehadiran.login')
                ->withErrors(['message' => 'Akun Anda telah dinonaktifkan.']);
        }

        return $next($request);
    }
}