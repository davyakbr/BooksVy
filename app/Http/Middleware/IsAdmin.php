<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Middleware: IsAdmin
     * 
     * FUNGSI:
     * Menjaga keamanan rute panel administrator (/admin/*).
     * Mencegah pengunjung umum (guest) atau pelanggan biasa (user) masuk ke halaman admin.
     * 
     * CARA KERJA:
     * 1. Cek apakah pengguna sudah login. Jika belum -> Tampilkan Error 403.
     * 2. Cek apakah role pengguna adalah 'admin'. Jika bukan -> Tampilkan Error 403.
     * 3. Jika lolos kedua syarat di atas -> Lanjutkan permintaan ke halaman admin ($next).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // -------------------------------------------------------------
        // SYARAT 1: Pengguna HARUS sudah login (Bukan Pengunjung Tamu)
        // -------------------------------------------------------------
        if (!Auth::check()) {
            return response()->view('errors.403', [
                'message' => 'Anda belum masuk ke sistem. Halaman Panel Administrator hanya dapat diakses setelah Anda masuk menggunakan akun Administrator.'
            ], 403);
        }

        // -------------------------------------------------------------
        // SYARAT 2: Peran (Role) akun HARUS 'admin'
        // Memanfaatkan method isAdmin() yang ada di app/Models/User.php
        // -------------------------------------------------------------
        if (!Auth::user()->isAdmin()) {
            return response()->view('errors.403', [
                'message' => 'Akun Anda (' . Auth::user()->name . ') terdaftar dengan peran Pelanggan. Halaman Panel Administrator hanya dapat diakses oleh Administrator sistem.'
            ], 403);
        }

        // Jika kedua syarat terpenuhi, izinkan request masuk ke Controller Admin
        return $next($request);
    }
}
