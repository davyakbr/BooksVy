@extends('layouts.app')

@section('title', 'Masuk ke Akun - BooksVy')

@section('content')

<div class="row justify-content-center my-4">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <div class="text-center mb-4">
                <span class="fs-3 fw-bold text-dark">Books<span class="text-warning">Vy</span></span>
                <h4 class="fw-bold mt-2 mb-1">Masuk ke Akun</h4>
                <p class="text-muted small">Silakan masukkan email dan kata sandi Anda</p>
            </div>

            @if(session('warning'))
                <div class="alert alert-warning rounded-3 border-0 small p-3 mb-4 shadow-sm">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-lock-fill fs-5 me-2 text-warning-emphasis"></i>
                        <div>{{ session('warning') }}</div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger rounded-3 border-0 small p-3 mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control bg-light border-start-0" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" name="password" id="loginPassword" class="form-control bg-light border-start-0 border-end-0" placeholder="Masukkan kata sandi Anda" required>
                        <button class="btn btn-light border border-start-0 text-muted" type="button" onclick="togglePasswordVisibility('loginPassword', this)" title="Tampilkan / Sembunyikan Kata Sandi">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary rounded-pill py-2 fw-semibold w-100 shadow-sm mb-3">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                </button>

                <div class="text-center small text-muted">
                    Belum memiliki akun? 
                    <a href="{{ route('register') }}" class="text-primary text-decoration-none fw-semibold">
                        Daftar Akun Baru
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
