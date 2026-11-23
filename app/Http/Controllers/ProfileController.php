<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman formulir edit profil pengguna.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Memperbarui informasi profil pengguna.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. Validasi Input Profil
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'email', 'max:100', 'unique:users,email,' . $user->id],
            'phone'    => ['nullable', 'string', 'max:15', 'regex:/^[0-9]{8,15}$/'],
            'address'  => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format alamat email tidak valid.',
            'email.unique'       => 'Alamat email ini sudah digunakan oleh akun lain.',
            'phone.regex'        => 'Nomor WhatsApp / telepon hanya boleh berupa angka (8-15 digit).',
            'password.min'       => 'Kata sandi baru minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        // 2. Siapkan data yang akan diperbarui
        $updateData = [
            'name'    => $validated['name'],
            'email'   => $validated['email'],
            'phone'   => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ];

        // 3. Jika pengguna mengisi password baru
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        // 4. Update data pengguna di database
        $user->update($updateData);

        return back()->with('success', 'Profil Anda berhasil diperbarui!');
    }
}
