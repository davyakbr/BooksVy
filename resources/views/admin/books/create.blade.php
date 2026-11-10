@extends('layouts.admin')

@section('title', 'Tambah Buku Baru - Admin BooksVy')

@section('content')

<div class="mb-4">
    <a href="{{ route('admin.books.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 mb-3">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Buku
    </a>
    <h3 class="fw-bold mb-1"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Buku Baru</h3>
    <p class="text-muted small mb-0">Lengkapi detail informasi buku dan unggah cover resmi buku</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger rounded-4 border-0 shadow-sm p-3 mb-4">
        <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-5">
    <form method="POST" action="{{ route('admin.books.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row g-4">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Judul Buku <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Penulis / Pengarang <span class="text-danger">*</span></label>
                        <input type="text" name="author" class="form-control" value="{{ old('author') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Penerbit</label>
                        <input type="text" name="publisher" class="form-control" value="{{ old('publisher') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Tahun Terbit</label>
                        <input type="number" name="year" class="form-control" value="{{ old('year', date('Y')) }}" min="1900" max="{{ date('Y') + 1 }}">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Harga (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control" value="{{ old('price') }}" min="0" step="500" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Stok Buku <span class="text-danger">*</span></label>
                        <input type="number" name="stock" class="form-control" value="{{ old('stock', 10) }}" min="0" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Sinopsis & Deskripsi Lengkap</label>
                    <textarea name="description" class="form-control" rows="6">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-light border-0 rounded-4 p-3 text-center">
                    <label class="form-label small fw-semibold text-muted mb-2">Unggah Cover Buku</label>
                    <div class="mb-3">
                        <img id="cover_image_preview" src="" alt="Preview Cover" class="img-fluid rounded-3 shadow-sm d-none mx-auto" style="max-height: 220px; object-fit: cover;">
                        <div id="cover_placeholder" class="py-5 text-muted">
                            <i class="bi bi-image fs-1 d-block mb-2"></i>
                            <span class="small">Belum ada gambar yang dipilih</span>
                        </div>
                    </div>
                    <input type="file" name="cover_image" id="cover_image_input" class="form-control form-control-sm" accept="image/*">
                    <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">Format: JPG, PNG, WEBP. Maks 2MB.</small>
                </div>
            </div>

            <div class="col-12 border-top pt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm">
                    <i class="bi bi-check-lg me-1"></i> Simpan Buku Baru
                </button>
                <a href="{{ route('admin.books.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>

@endsection
