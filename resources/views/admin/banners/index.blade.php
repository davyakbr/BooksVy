@extends('layouts.admin')

@section('title', 'Kelola Slide Banner Hero - BooksVy Admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-images text-primary me-2"></i>Kelola Slide Banner</h2>
        <p class="text-muted small mb-0">Atur konten, promo, dan gambar hero carousel di halaman beranda</p>
    </div>
    <a href="#formBanner" class="btn btn-primary rounded-pill px-4 shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Tambah Slide Baru
    </a>
</div>

<!-- Daftar Banner -->
<div class="row g-4 mb-5">
    @forelse ($banners as $b)
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 {{ !$b->is_active ? 'opacity-75' : '' }}">
                <!-- Preview mini banner -->
                <div class="{{ $b->bg_class ?? 'slide-bg-green' }} d-flex align-items-center justify-content-between px-4 py-3" style="min-height:100px;">
                    <div class="text-white" style="max-width:70%;">
                        @if ($b->badge_text)
                            <span class="badge bg-white text-dark rounded-pill px-2 py-1 small fw-bold mb-1 d-inline-block">
                                {{ $b->badge_text }}
                            </span>
                        @endif
                        <div class="fw-bold" style="font-size:1rem; line-height:1.3;">
                            {{ $b->title }}
                        </div>
                    </div>
                    @if (!empty($b->image) && file_exists(public_path('uploads/' . $b->image)))
                        <img src="{{ asset('uploads/' . $b->image) }}" 
                             alt="Banner"
                             style="width:65px; height:85px; object-fit:cover; border-radius:8px; border:2px solid rgba(255,255,255,.4);">
                    @else
                        <div style="width:65px; height:85px; background:rgba(255,255,255,.15); border-radius:8px; border:1px dashed rgba(255,255,255,.5); display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px;">
                            <i class="bi bi-card-text text-white-50" style="font-size:1.2rem;"></i>
                            <span style="font-size:.65rem; color:rgba(255,255,255,.8);">Tanpa Foto</span>
                        </div>
                    @endif
                </div>

                <!-- Info & Actions -->
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge rounded-pill {{ $b->is_active ? 'bg-success' : 'bg-secondary' }} small me-1">
                                {{ $b->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <span class="badge rounded-pill bg-light text-muted border small">
                                Urutan: {{ $b->sort_order }}
                            </span>
                        </div>
                        <div class="d-flex gap-1">
                            <!-- Toggle Aktif -->
                            <form action="{{ route('admin.banners.toggle', $b->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm rounded-pill {{ $b->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}"
                                        title="{{ $b->is_active ? 'Nonaktifkan' : 'Aktifkan' }} slide">
                                    <i class="bi bi-{{ $b->is_active ? 'eye-slash' : 'eye' }}"></i>
                                </button>
                            </form>
                            <!-- Hapus -->
                            <form action="{{ route('admin.banners.destroy', $b->id) }}" method="POST" class="d-inline form-confirm-delete" data-item-name="slide banner &quot;{{ addslashes($b->title) }}&quot;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="Hapus slide">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @if ($b->subtitle)
                        <p class="text-muted small mb-0" style="line-height:1.45; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                            {{ $b->subtitle }}
                        </p>
                    @endif
                    <div class="mt-2 d-flex gap-2 small text-muted">
                        @if ($b->btn1_label)
                            <span><i class="bi bi-cursor-fill me-1 text-primary"></i>{{ $b->btn1_label }}</span>
                        @endif
                        @if ($b->btn2_label)
                            <span class="text-secondary">/ {{ $b->btn2_label }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                <i class="bi bi-image text-muted" style="font-size:3rem;"></i>
                <p class="text-muted mt-3">Belum ada slide banner. Tambahkan slide pertama melalui formulir di bawah!</p>
            </div>
        </div>
    @endforelse
</div>

<!-- ================================================================
     FORM TAMBAH BANNER BARU
================================================================ -->
<div id="formBanner" class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white mb-5">
    <h5 class="fw-bold mb-4">
        <i class="bi bi-plus-circle text-primary me-2"></i>Tambah Slide Banner Baru
    </h5>

    <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            <!-- Judul Slide -->
            <div class="col-12">
                <label class="form-label small fw-semibold text-muted">Judul Utama Slide <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" required
                       placeholder="Contoh: Koleksi Buku Referensi Teknologi & Bisnis"
                       value="{{ old('title') }}">
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Subtitle -->
            <div class="col-12">
                <label class="form-label small fw-semibold text-muted">Teks Deskripsi / Subtitle</label>
                <textarea name="subtitle" class="form-control" rows="2"
                          placeholder="Deskripsi singkat isi slide promosi (opsional)">{{ old('subtitle') }}</textarea>
            </div>

            <!-- Badge & BG -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Teks Badge (Label Kecil)</label>
                <input type="text" name="badge_text" class="form-control"
                       placeholder="Contoh: 💻 Spesial Teknologi"
                       value="{{ old('badge_text') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Warna Tema Latar Slide</label>
                <select name="bg_class" class="form-select">
                    <option value="slide-bg-green" {{ old('bg_class') === 'slide-bg-green' ? 'selected' : '' }}>🟢 Hijau (Forest Green)</option>
                    <option value="slide-bg-wine" {{ old('bg_class') === 'slide-bg-wine' ? 'selected' : '' }}>🟤 Coklat Anggur (Wine)</option>
                    <option value="slide-bg-teal" {{ old('bg_class') === 'slide-bg-teal' ? 'selected' : '' }}>🔵 Teal (Blue-Green)</option>
                    <option value="slide-bg-forest" {{ old('bg_class') === 'slide-bg-forest' ? 'selected' : '' }}>🌲 Hutan Gelap (Dark Forest)</option>
                </select>
            </div>

            <!-- Tombol 1 -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Label Tombol Utama</label>
                <input type="text" name="btn1_label" class="form-control"
                       placeholder="Contoh: Lihat Katalog"
                       value="{{ old('btn1_label', 'Lihat Katalog') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">URL Tombol Utama</label>
                <input type="text" name="btn1_url" class="form-control"
                       placeholder="#katalog atau {{ url('/') }}"
                       value="{{ old('btn1_url', '#katalog') }}">
            </div>

            <!-- Tombol 2 (opsional) -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Label Tombol Kedua <span class="text-muted fw-normal">(opsional)</span></label>
                <input type="text" name="btn2_label" class="form-control"
                       placeholder="Contoh: Semua Koleksi"
                       value="{{ old('btn2_label') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">URL Tombol Kedua <span class="text-muted fw-normal">(opsional)</span></label>
                <input type="text" name="btn2_url" class="form-control"
                       placeholder="#katalog"
                       value="{{ old('btn2_url') }}">
            </div>

            <!-- Urutan & Aktif -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Urutan Tampil (Sort Order)</label>
                <input type="number" name="sort_order" class="form-control" min="1" max="99"
                       value="{{ old('sort_order', count($banners) + 1) }}">
            </div>

            <!-- Upload Gambar -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">
                    Gambar Banner <span class="text-muted fw-normal">(JPG, PNG, WEBP, maks 3MB)</span>
                </label>
                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mt-4 text-end">
                <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm fw-bold">
                    <i class="bi bi-save me-1"></i> Simpan Slide Banner
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
