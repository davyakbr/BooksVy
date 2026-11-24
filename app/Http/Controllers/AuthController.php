<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Menampilkan Halaman Formulir Login.
     * Jika pengguna sudah dalam keadaan login:
     * - Admin diarahkan langsung ke Dashboard Admin
     * - Pelanggan diarahkan langsung ke Beranda
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin() 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('home');
        }

        return view('auth.login');
    }

    /**
     * Memproses Verifikasi Data Login Pengguna.
     */
    public function login(Request $request)
    {
        // ---------------------------------------------------------------------
        // 1. VALIDASI INPUT LOGIN
        // ---------------------------------------------------------------------
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // ---------------------------------------------------------------------
        // 2. ATTEMPT: Cek kesesuaian email dan password (Bcrypt) di database
        // ---------------------------------------------------------------------
        if (Auth::attempt($credentials)) {
            // Ambil keranjang belanja guest sebelum login (jika ada)
            $guestCart = $request->session()->get('cart', []);
            
            // Regenerate session id untuk mencegah serangan Session Fixation
            $request->session()->regenerate();

            // -----------------------------------------------------------------
            // 3. SINKRONISASI KERANJANG BELANJA (Database <-> Session)
            // -----------------------------------------------------------------
            $userId = Auth::id();
            $userCartRecord = UserCart::where('user_id', $userId)->first();
            $userCart = ($userCartRecord && is_array($userCartRecord->cart_data)) 
                ? $userCartRecord->cart_data 
                : [];

            // Gabungkan barang yang dimasukkan sebelum login ke keranjang akun
            if (!empty($guestCart)) {
                foreach ($guestCart as $bId => $item) {
                    if (isset($userCart[$bId])) {
                        $userCart[$bId]['qty'] += (int)($item['qty'] ?? 1);
                    } else {
                        $userCart[$bId] = $item;
                    }
                }
            }

            // Simpan keranjang gabungan ke Session & Database
            session()->put('cart', $userCart);
            UserCart::updateOrCreate(
                ['user_id' => $userId],
                ['cart_data' => $userCart]
            );

            // -----------------------------------------------------------------
            // 4. PENGALIHAN HALAMAN BERDASARKAN PERAN (ROLE)
            // -----------------------------------------------------------------
            // Kasus A: Jika yang login adalah Administrator -> ke Panel Admin
            if (Auth::user()->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Selamat datang di Panel Administrasi, ' . Auth::user()->name . '!');
            }

            // Kasus B: Pelanggan yang sebelumnya mencoba akses URL admin
            $intended = session()->get('url.intended');
            if ($intended && str_contains($intended, '/admin')) {
                session()->forget('url.intended');
                return redirect()->route('home')
                    ->with('warning', 'Anda berhasil masuk sebagai Pelanggan. Akun Anda tidak memiliki hak akses ke Panel Administrator.');
            }

            // Kasus C: Pelanggan biasa -> ke Beranda Belanja
            return redirect()->intended(route('home'))
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        // ---------------------------------------------------------------------
        // 5. JIKA KREDENSIAL SALAH: Kembalikan dengan Pesan Error
        // ---------------------------------------------------------------------
        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Menampilkan Halaman Formulir Registrasi Pengguna Baru.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.register');
    }

    /**
     * Memproses Pendaftaran Akun Pelanggan Baru.
     */
    public function register(Request $request)
    {
        // ---------------------------------------------------------------------
        // 1. VALIDASI DATA PENDAFTARAN
        // ---------------------------------------------------------------------
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',                     // Wajib ada huruf besar
                'regex:/[0-9]/',                     // Wajib ada angka
                'regex:/[!@#$%^&*()\-_=+{};:,<.>]/', // Wajib ada simbol unik
                'confirmed',                         // Wajib sama dengan field password_confirmation
            ],
            'phone'    => ['nullable', 'string', 'max:15', 'regex:/^[0-9]{8,15}$/'],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format alamat email tidak valid.',
            'email.unique'       => 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau login.',
            'phone.regex'        => 'Nomor WhatsApp / telepon hanya boleh berupa angka dan maksimal 15 digit.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.min'       => 'Kata sandi minimal terdiri dari 8 karakter.',
            'password.regex'     => 'Kata sandi harus mengandung kombinasi huruf besar (kapital), angka, dan simbol unik (!@#$%^&*...).',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // ---------------------------------------------------------------------
        // 2. SIMPAN PENGGUNA BARU KE DATABASE (Default Role: 'user')
        // ---------------------------------------------------------------------
        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => $validated['password'], // Otomatis di-hash oleh casts User model
            'phone'      => $validated['phone'] ?? null,
            'address'    => null,
            'role'       => 'user',
            'created_at' => now(),
        ]);

        // ---------------------------------------------------------------------
        // 3. LOGIN OTOMATIS & ARAHKAN KE BERANDA
        // ---------------------------------------------------------------------
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('success', 'Pendaftaran akun berhasil! Selamat datang di BooksVy, ' . $user->name . '.');
    }

    /**
     * Memproses Logout (Keluar dari Sistem).
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Bersihkan dan batalkan seluruh session aktif
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Anda telah berhasil keluar (logout) dari akun.');
    }
}
