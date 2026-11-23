@extends('layouts.app')

@section('title', 'Edit Profil Pengguna - BooksVy')

@section('content')
<div class="row justify-content-center my-4">
    <div class="col-lg-8 col-md-10">
        
        <!-- Header Judul -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-person-gear text-primary me-2"></i>Edit Profil Saya</h3>
                <p class="text-muted small mb-0">Perbarui data informasi kontak, nomor WhatsApp, dan alamat pengiriman Anda</p>
            </div>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success rounded-4 border-0 shadow-sm p-3 mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger rounded-4 border-0 shadow-sm p-3 mb-4">
                <h6 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Periksa kembali data formulir:</h6>
                <ul class="mb-0 ps-3 small">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <!-- Informasi Dasar Akun -->
                <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-person-vcard text-primary"></i> Informasi Pribadi
                </h5>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-semibold text-muted">Nomor WhatsApp / HP Aktif</label>
                        <input type="tel" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" maxlength="15" inputmode="numeric" oninput="validateNumberInput(this, 15, 'Nomor WhatsApp')">
                        <div class="input-notice text-danger small mt-1 d-none" style="font-size: 0.78rem;"></div>
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Alamat Pengiriman Utama</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Nama Jalan, No. Rumah, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten, Kode Pos...">{{ old('address', $user->address) }}</textarea>
                        <small class="text-muted" style="font-size: 0.75rem;">Alamat ini akan otomatis terisi saat Anda melakukan checkout buku.</small>
                        @error('address')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                <!-- Ganti Kata Sandi (Opsional) -->
                <h5 class="fw-bold mb-2 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock text-primary"></i> Ganti Kata Sandi (Opsional)
                </h5>
                <p class="text-muted small mb-3">Kosongkan jika Anda tidak ingin mengubah kata sandi akun Anda.</p>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Kata Sandi Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" name="password" id="newPassword" class="form-control bg-light border-start-0 border-end-0" placeholder="Minimal 8 karakter">
                            <button class="btn btn-light border border-start-0 text-muted" type="button" onclick="togglePasswordVisibility('newPassword', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Konfirmasi Kata Sandi Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill text-muted"></i></span>
                            <input type="password" name="password_confirmation" id="confirmNewPassword" class="form-control bg-light border-start-0 border-end-0" placeholder="Ulangi kata sandi baru">
                            <button class="btn btn-light border border-start-0 text-muted" type="button" onclick="togglePasswordVisibility('confirmNewPassword', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('home') }}" class="btn btn-light rounded-pill px-4 py-2 fw-semibold">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
