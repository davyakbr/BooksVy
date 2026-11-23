@extends('layouts.app')

@section('title', '404 - Halaman Tidak Ditemukan | BooksVy')

@section('content')
<div class="row justify-content-center my-5 py-3">
    <div class="col-lg-7 col-md-9">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white text-center">
            
            <div class="mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning-emphasis p-4" style="width: 100px; height: 100px;">
                    <i class="bi bi-book-half" style="font-size: 3.2rem;"></i>
                </div>
            </div>

            <div class="mb-2">
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fw-semibold small">
                    <i class="bi bi-search me-1"></i> ERROR 404 • NOT FOUND
                </span>
            </div>

            <h3 class="fw-bold text-dark mt-3 mb-2">Halaman Tidak Ditemukan</h3>
            <p class="text-secondary small mb-4 px-md-4">
                Halaman atau buku yang Anda cari mungkin telah dipindahkan, dihapus, atau tautan yang Anda masukkan salah.
            </p>

            <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                    <i class="bi bi-house-door me-1"></i> Kembali ke Beranda
                </a>
                <a href="{{ route('home') }}#katalog" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-grid me-1"></i> Jelajahi Buku
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
