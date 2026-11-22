<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login.
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
     * Memproses autentikasi pengguna (login) dan sinkronisasi keranjang belanja.
     */
    public function login(Request $request)
    {
        // 1. Validasi Input Login
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // 2. Cek kecocokan Email & Password
        if (Auth::attempt($credentials)) {
            $guestCart = $request->session()->get('cart', []);
            $request->session()->regenerate(); // Mencegah Session Fixation Attack

            // 3. Sinkronisasi Keranjang Belanja User dari Database
            $userId = Auth::id();
            $userCartRecord = UserCart::where('user_id', $userId)->first();
            $userCart = ($userCartRecord && is_array($userCartRecord->cart_data)) ? $userCartRecord->cart_data : [];

            // Gabungkan keranjang guest (tamu) ke keranjang user yang login
            if (!empty($guestCart)) {
                foreach ($guestCart as $bookId => $item) {
                    if (isset($userCart[$bookId])) {
                        $userCart[$bookId]['qty'] += (int)($item['qty'] ?? 1);
                    } else {
                        $userCart[$bookId] = $item;
                    }
                }
            }

            session()->put('cart', $userCart);
            UserCart::updateOrCreate(
                ['user_id' => $userId],
                ['cart_data' => $userCart]
            );

            // 4. Pengalihan berdasarkan Hak Akses (Role)
            if (Auth::user()->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Selamat datang di Panel Administrasi, ' . Auth::user()->name . '!');
            }

            // Jika user biasa mencoba akses halaman admin sebelumnya
            $intended = session()->get('url.intended');
            if ($intended && str_contains($intended, '/admin')) {
                session()->forget('url.intended');
                return redirect()->route('home')
                    ->with('warning', 'Anda berhasil masuk sebagai Pelanggan. Akun Anda tidak memiliki hak akses ke Panel Administrator.');
            }

            return redirect()->intended(route('home'))
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Menampilkan halaman formulir registrasi akun baru.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.register');
    }

    /**
     * Mendaftarkan akun pelanggan baru ke database.
     */
    public function register(Request $request)
    {
        // 1. Validasi Input Registrasi
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/', // Minimal 1 huruf besar
                'regex:/[0-9]/', // Minimal 1 angka
                'regex:/[!@#$%^&*()\-_=+{};:,<.>]/', // Minimal 1 simbol
                'confirmed',
            ],
            'phone'    => ['nullable', 'string', 'max:25'],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format alamat email tidak valid.',
            'email.unique'       => 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau login.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.min'       => 'Kata sandi minimal terdiri dari 8 karakter.',
            'password.regex'     => 'Kata sandi harus mengandung kombinasi huruf besar (kapital), angka, dan simbol unik (!@#$%^&*...).',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // 2. Simpan Data User (Password otomatis di-hash via Model casts 'password' => 'hashed')
        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => $validated['password'],
            'phone'      => $validated['phone'] ?? null,
            'address'    => null,
            'role'       => 'user',
            'created_at' => now(),
        ]);

        // 3. Langsung login setelah daftar
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('success', 'Pendaftaran akun berhasil! Selamat datang di BooksVy, ' . $user->name . '.');
    }

    /**
     * Logout pengguna dan hancurkan sesi.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Anda telah berhasil keluar (logout) dari akun.');
    }
}
