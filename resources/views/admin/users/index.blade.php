@extends('layouts.admin')

@section('title', 'Daftar Pengguna - BooksVy Admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-people text-primary me-2"></i>Daftar Pengguna Terdaftar</h3>
        <p class="text-muted small mb-0">Kelola akun pelanggan dan hak akses administrator BooksVy</p>
    </div>
</div>

<!-- Form Filter & Pencarian -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-center">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Cari nama, email, nomor HP..." value="{{ $search }}">
                </div>
            </div>

            <div class="col-md-3">
                <select name="role" class="form-select bg-light">
                    <option value="">Semua Peran (Role)</option>
                    <option value="user" {{ $role === 'user' ? 'selected' : '' }}>Pelanggan (User)</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill flex-grow-1">Filter</button>
                @if (!empty($search) || !empty($role))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-pill" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Pengguna -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-5">
    <div class="table-responsive">
        <table class="table align-middle custom-table mb-0">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 35%;">Nama & Kontak</th>
                    <th style="width: 20%;">Peran</th>
                    <th class="text-center" style="width: 20%;">Jumlah Pesanan</th>
                    <th class="text-center" style="width: 20%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $index => $u)
                    <tr>
                        <td>{{ $users->firstItem() + $index }}</td>
                        <td>
                            <strong class="text-dark d-block">{{ $u->name }}</strong>
                            <small class="text-muted d-block"><i class="bi bi-envelope me-1"></i>{{ $u->email }}</small>
                            @if ($u->phone)
                                <small class="text-muted d-block"><i class="bi bi-telephone me-1"></i>{{ $u->phone }}</small>
                            @endif
                        </td>
                        <td>
                            @if ($u->role === 'admin')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-shield-lock me-1"></i>Administrator
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-person me-1"></i>Pelanggan
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1">
                                {{ $u->orders_count }} Pesanan
                            </span>
                        </td>
                        <td class="text-center">
                            @if ($u->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="d-inline form-confirm-delete" data-item-name="akun pengguna &quot;{{ addslashes($u->name) }}&quot;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="Hapus User">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-light text-muted border">Akun Anda</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">Belum ada akun pengguna yang sesuai dengan pencarian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="p-3 border-top">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
