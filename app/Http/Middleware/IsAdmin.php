<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     * Memproteksi rute admin dari pengunjung yang belum login maupun user biasa (bukan admin).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Jika belum login (tamu/guest) mencoba mengakses rute admin
        if (!Auth::check()) {
            return response()->view('errors.403', [
                'message' => 'Anda belum masuk ke sistem. Halaman Panel Administrator hanya dapat diakses setelah Anda masuk menggunakan akun Administrator.'
            ], 403);
        }

        // 2. Jika user sudah login tetapi bukan Administrator (role !== 'admin')
        if (!Auth::user()->isAdmin()) {
            return response()->view('errors.403', [
                'message' => 'Akun Anda (' . Auth::user()->name . ') terdaftar dengan peran Pelanggan. Halaman Panel Administrator hanya dapat diakses oleh Administrator sistem.'
            ], 403);
        }

        return $next($request);
    }
}
