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
        // 1. Jika belum login (tamu/guest) mau mengakses rute admin
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'Akses ditolak! Anda harus masuk (login) sebagai Administrator terlebih dahulu untuk mengakses Panel Admin.');
        }

        // 2. Jika user sudah login tetapi bukan Administrator (role !== 'admin')
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('danger', 'Akses ditolak! Akun Anda tidak memiliki hak akses ke Panel Administrator.');
        }

        return $next($request);
    }
}
