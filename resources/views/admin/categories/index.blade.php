@extends('layouts.admin')

@section('title', 'Kelola Kategori Buku - Admin BooksVy')

@section('content')

<div class="mb-4">
    <h3 class="fw-bold mb-1"><i class="bi bi-tags text-primary me-2"></i>Kelola Kategori Buku</h3>
    <p class="text-muted small mb-0">Atur kategori genre buku untuk memudahkan pencarian oleh pelanggan</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger rounded-4 border-0 shadow-sm p-3 mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
            <div>
                <strong>Perhatian:</strong>
                <ul class="mb-0 ps-3 small mt-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<div class="row g-4 mb-5">
    <!-- Form Tambah Kategori -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Kategori Baru</h5>
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Pemrograman" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted">Deskripsi Kategori</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Keterangan singkat kategori...">{{ old('description') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 w-100 shadow-sm">
                    <i class="bi bi-check-lg me-1"></i> Simpan Kategori
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Kategori -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="table-responsive">
                <table class="table align-middle mb-0 custom-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 35%;">Nama Kategori</th>
                            <th style="width: 35%;">Deskripsi</th>
                            <th style="width: 10%;" class="text-center">Total Buku</th>
                            <th style="width: 15%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                            <tr>
                                <td class="text-muted small">{{ $loop->iteration }}</td>
                                <td class="fw-semibold text-dark">{{ $cat->name }}</td>
                                <td class="text-muted small">{{ $cat->description ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-primary border rounded-pill px-2 py-1">{{ $cat->books_count }} buku</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-outline-primary btn-sm rounded-circle" data-bs-toggle="modal" data-bs-target="#editModal{{ $cat->id }}" title="Edit Kategori">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        @if($cat->books_count == 0)
                                            <form method="POST" action="{{ route('admin.categories.destroy', $cat->id) }}" class="d-inline form-confirm-delete" data-item-name="kategori &quot;{{ addslashes($cat->name) }}&quot;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm border-0 rounded-circle" title="Hapus Kategori">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-outline-secondary btn-sm border-0 rounded-circle" disabled title="Tidak dapat dihapus karena ada buku dalam kategori ini">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Edit Kategori -->
                            <div class="modal fade" id="editModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Edit Kategori</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="{{ route('admin.categories.update', $cat->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold text-muted">Nama Kategori</label>
                                                    <input type="text" name="name" class="form-control" value="{{ $cat->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold text-muted">Deskripsi</label>
                                                    <textarea name="description" class="form-control" rows="3">{{ $cat->description }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada kategori yang ditambahkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
