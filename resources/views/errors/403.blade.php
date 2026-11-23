@extends('layouts.app')

@section('title', '403 - Akses Dibatasi | BooksVy Security')

@section('content')
<div class="row justify-content-center my-5 py-3">
    <div class="col-lg-7 col-md-9">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white text-center">
            
            <!-- Icon Ilustrasi Middleware Security -->
            <div class="mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger p-4" style="width: 100px; height: 100px;">
                    <i class="bi bi-shield-lock-fill" style="font-size: 3.2rem;"></i>
                </div>
            </div>

            <!-- Kode Status & Badge -->
            <div class="mb-2">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold small">
                    <i class="bi bi-shield-x me-1"></i> PROTEKSI MIDDLEWARE • KODE 403
                </span>
            </div>

            <!-- Judul Penjelasan -->
            <h3 class="fw-bold text-dark mt-3 mb-2">Akses Halaman Dibatasi</h3>
            <p class="text-secondary small mb-4 px-md-4">
                {{ $message ?? 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. Halaman Panel Administrator hanya diperuntukkan bagi pengguna dengan peran Administrator.' }}
            </p>

            <!-- Card Rincian Status Akun -->
            @if(Auth::check())
                <!-- Kondisi: Sudah Login Sebagai Pelanggan Biasa -->
                <div class="card bg-light border-0 rounded-4 p-3 mb-4 text-start small">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-person me-1"></i> Pengguna Aktif:</span>
                        <strong class="text-dark">{{ Auth::user()->name }}</strong>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-envelope me-1"></i> Alamat Email:</span>
                        <span class="text-dark">{{ Auth::user()->email }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted"><i class="bi bi-person-badge me-1"></i> Hak Akses (Role):</span>
                        <span class="badge bg-warning text-dark rounded-pill px-2 py-1 text-uppercase fw-bold">
                            {{ Auth::user()->role ?? 'user' }} (Bukan Admin)
                        </span>
                    </div>
                </div>
            @else
                <!-- Kondisi: Belum Login (Tamu / Guest) -->
                <div class="card bg-light border-0 rounded-4 p-3 mb-4 text-start small">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-person-slash me-1"></i> Status Pengguna:</span>
                        <span class="badge bg-secondary rounded-pill px-2 py-1">Belum Masuk (Tamu / Guest)</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted"><i class="bi bi-info-circle me-1"></i> Keterangan:</span>
                        <span class="text-dark">Silakan login menggunakan akun Administrator.</span>
                    </div>
                </div>
            @endif

            <!-- Tombol Aksi Navigasi UX -->
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                    <i class="bi bi-house-door me-1"></i> Kembali ke Beranda Toko
                </a>
                
                @if(!Auth::check())
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk (Login)
                    </a>
                @endif
            </div>

            <!-- Footer Security Note -->
            <div class="mt-4 pt-3 text-muted" style="font-size: 0.75rem;">
                <i class="bi bi-lock-fill text-success me-1"></i> Dilindungi oleh BooksVy Middleware Security Guard • Laravel 12
            </div>

        </div>
    </div>
</div>
@endsection
