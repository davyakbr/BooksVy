@extends('layouts.admin')

@section('title', 'Kelola Data Buku - Admin BooksVy')

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-book text-primary me-2"></i>Kelola Katalog Buku</h3>
        <p class="text-muted small mb-0">Daftar buku, pembaruan stok, harga, dan manajemen cover</p>
    </div>
    <a href="{{ route('admin.books.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Tambah Buku Baru
    </a>
</div>

<!-- Card Filter & Pencarian -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.books.index') }}" class="row g-2 align-items-center">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Cari judul buku, penulis, penerbit..." value="{{ $search ?? '' }}">
                    <button type="submit" class="btn btn-primary px-3">Cari</button>
                </div>
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat->id }}" {{ ($categoryId ?? null) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 text-md-end">
                @if(!empty($search ?? '') || !empty($categoryId ?? null))
                    <a href="{{ route('admin.books.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill w-100">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-5">
    <div class="table-responsive">
        <table class="table align-middle mb-0 custom-table">
            <thead class="table-light">
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 40%;">Buku</th>
                    <th style="width: 15%;">Kategori</th>
                    <th style="width: 15%;">Harga</th>
                    <th style="width: 10%;" class="text-center">Stok</th>
                    <th style="width: 15%;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($books as $b)
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration + ($books->currentPage() - 1) * $books->perPage() }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                {!! bookCoverHtml($b->cover_image, $b->title, 'thumb') !!}
                                <div>
                                    <h6 class="fw-bold mb-1">
                                        <a href="{{ route('books.show', $b->id) }}" target="_blank" class="text-decoration-none text-dark">
                                            {{ $b->title }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">Karya: {{ $b->author }} &bull; {{ $b->publisher ?? 'Penerbit -' }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-secondary border rounded-pill px-3 py-1">{{ $b->category->name ?? '-' }}</span></td>
                        <td class="fw-semibold text-primary">{{ formatRupiah($b->price) }}</td>
                        <td class="text-center">
                            @if($b->stock <= 0)
                                <span class="badge bg-secondary text-white rounded-pill px-2 py-1">Habis</span>
                            @elseif($b->stock <= 5)
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1">{{ $b->stock }} unit</span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">{{ $b->stock }} unit</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.books.edit', $b->id) }}" class="btn btn-outline-primary btn-sm rounded-circle" title="Edit Buku">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($b->stock <= 0)
                                    <form method="POST" action="{{ route('admin.books.destroy', $b->id) }}" class="d-inline form-confirm-delete" data-item-name="buku &quot;{{ addslashes($b->title) }}&quot;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm border-0 rounded-circle" title="Hapus Buku (Stok Habis)">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-outline-secondary btn-sm border-0 rounded-circle" disabled title="Tidak dapat dihapus karena masih ada stok ({{ $b->stock }} unit)">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Tidak ada buku yang ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($books->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $books->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection
