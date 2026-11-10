<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin() 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('home');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (Auth::attempt($credentials)) {
            $guestCart = $request->session()->get('cart', []);
            $request->session()->regenerate();

            // Sinkronisasi keranjang belanja per user (dari tabel user_carts)
            $userId = Auth::id();
            $userCartRecord = \App\Models\UserCart::where('user_id', $userId)->first();
            $userCart = ($userCartRecord && is_array($userCartRecord->cart_data)) ? $userCartRecord->cart_data : [];

            if (!empty($guestCart)) {
                foreach ($guestCart as $bId => $item) {
                    if (isset($userCart[$bId])) {
                        $userCart[$bId]['qty'] += (int)($item['qty'] ?? 1);
                    } else {
                        $userCart[$bId] = $item;
                    }
                }
            }

            session()->put('cart', $userCart);
            \App\Models\UserCart::updateOrCreate(
                ['user_id' => $userId],
                ['cart_data' => $userCart]
            );

            // Jika login sebagai Administrator
            if (Auth::user()->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Selamat datang di Panel Administrasi, ' . Auth::user()->name . '!');
            }

            // Jika login sebagai Pelanggan Biasa tapi sebelumnya mencoba akses halaman admin
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

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*()\-_=+{};:,<.>]/',
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

        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => $validated['password'], // otomatis di-hash via User model cast
            'phone'      => $validated['phone'] ?? null,
            'address'    => null,
            'role'       => 'user',
            'created_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('success', 'Pendaftaran akun berhasil! Selamat datang di BooksVy, ' . $user->name . '.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Anda telah berhasil keluar (logout) dari akun.');
    }
}
