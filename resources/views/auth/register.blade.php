@extends('layouts.app')

@section('title', 'Daftar Akun Baru - BooksVy')

@section('content')

<div class="row justify-content-center my-4">
    <div class="col-md-7 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <div class="text-center mb-4">
                <span class="fs-3 fw-bold text-dark">Books<span class="text-warning">Vy</span></span>
                <h4 class="fw-bold mt-2 mb-1">Daftar Akun Baru</h4>
                <p class="text-muted small">Bergabunglah bersama ribuan pembaca setia di BooksVy</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger rounded-3 border-0 small p-3 mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Alamat Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Nomor WhatsApp / HP Aktif</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="Contoh: 081234567890">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Kata Sandi <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" name="password" id="regPassword" class="form-control bg-light border-start-0 border-end-0" placeholder="Buat kata sandi yang kuat" required>
                        <button class="btn btn-light border border-start-0 text-muted" type="button" onclick="togglePasswordVisibility('regPassword', this)" title="Tampilkan / Sembunyikan Kata Sandi">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    
                    <!-- Pemberitahuan Persyaratan Password -->
                    <div class="card bg-light border-0 rounded-3 p-2 mt-2">
                        <small class="text-muted d-block" style="font-size: 0.78rem;">
                            <i class="bi bi-shield-lock-fill text-primary me-1"></i> <strong>Ketentuan Kata Sandi yang Aman:</strong>
                        </small>
                        <ul class="mb-0 ps-3 text-muted" style="font-size: 0.76rem; line-height: 1.5;">
                            <li>Minimal <strong>8 karakter</strong></li>
                            <li>Wajib mengandung setidaknya 1 <strong>huruf besar (kapital)</strong></li>
                            <li>Wajib mengandung setidaknya 1 <strong>angka (0-9)</strong></li>
                            <li>Wajib mengandung setidaknya 1 <strong>simbol unik</strong> (!@#$%^&*...)</li>
                        </ul>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-check text-muted"></i></span>
                        <input type="password" name="password_confirmation" id="regPasswordConfirm" class="form-control bg-light border-start-0 border-end-0" placeholder="Ulangi kata sandi Anda" required>
                        <button class="btn btn-light border border-start-0 text-muted" type="button" onclick="togglePasswordVisibility('regPasswordConfirm', this)" title="Tampilkan / Sembunyikan Kata Sandi">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary rounded-pill py-2 fw-semibold w-100 shadow-sm mb-3">
                    <i class="bi bi-person-plus-fill me-1"></i> Daftar Sekarang
                </button>

                <div class="text-center small text-muted">
                    Sudah memiliki akun? 
                    <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-semibold">
                        Masuk ke Akun
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
