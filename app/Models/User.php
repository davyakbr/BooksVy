<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Nama tabel di database MySQL
    protected $table = 'users';

    // Mematikan timestamps otomatis Laravel (karena hanya menggunakan kolom custom 'created_at')
    public $timestamps = false;

    /**
     * $fillable: Kolom yang diizinkan untuk diisi secara massal (Mass Assignment Protection).
     * Mencegah pengguna jahat menyisipkan kolom berbahaya lewat request form.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'NIK',
        'address',
        'role',
        'created_at',
    ];

    /**
     * $hidden: Kolom yang otomatis disembunyikan saat data User diubah jadi array / JSON
     * (Misalnya hash password tidak boleh bocor keluar).
     */
    protected $hidden = [
        'password',
    ];

    /**
     * casts(): Tipe data otomatis untuk kolom tertentu.
     * - password otomatis di-hash dengan Bcrypt/Argon2 saat disimpan
     * - created_at otomatis menjadi objek Carbon DateTime
     */
    protected function casts(): array
    {
        return [
            'password'   => 'hashed',
            'created_at' => 'datetime',
        ];
    }

    // =========================================================================
    // HELPER METHOD (PEMERIKSA PERAN AKUN)
    // =========================================================================
    
    /**
     * Cek apakah akun ini adalah Administrator.
     * Mengembalikan true jika role == 'admin', dan false jika role == 'user'.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // =========================================================================
    // RELASI ANTAR TABEL (ELOQUENT RELATIONSHIPS)
    // =========================================================================

    /**
     * Relasi One-to-Many: Satu User bisa memiliki banyak Pesanan (Orders).
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * Relasi One-to-One: Satu User memiliki satu data Keranjang Belanja (UserCart).
     */
    public function cart()
    {
        return $this->hasOne(UserCart::class, 'user_id');
    }
}
