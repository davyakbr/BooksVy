@extends('layouts.admin')

@section('title', 'Pusat Pesan Pelanggan - BooksVy Admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-chat-left-text text-primary me-2"></i>Pesan Masuk dari Pelanggan</h3>
        <p class="text-muted small mb-0">Kelola tiket bantuan dan tanggapi pesan masuk pelanggan BooksVy</p>
    </div>
</div>

<!-- Form Pencarian & Filter Pesan -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.messages.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Cari No. Tiket / Nama / Email / Subjek..." value="{{ $search }}">
                </div>
            </div>

            <div class="col-md-4">
                <select name="status" class="form-select bg-light">
                    <option value="">Semua Status</option>
                    <option value="Menunggu Tanggapan" {{ $status === 'Menunggu Tanggapan' ? 'selected' : '' }}>Menunggu Tanggapan</option>
                    <option value="Sudah Dibalas" {{ $status === 'Sudah Dibalas' ? 'selected' : '' }}>Sudah Dibalas</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill flex-grow-1">Filter</button>
                @if (!empty($search) || !empty($status))
                    <a href="{{ route('admin.messages.index') }}" class="btn btn-outline-secondary rounded-pill" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Pesan Masuk -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-5">
    <div class="table-responsive">
        <table class="table align-middle custom-table mb-0">
            <thead>
                <tr>
                    <th style="width: 15%;">No. Tiket & Waktu</th>
                    <th style="width: 20%;">Pengirim</th>
                    <th style="width: 18%;">Topik & Pesanan</th>
                    <th style="width: 32%;">Subjek & Pesan</th>
                    <th class="text-center" style="width: 15%;">Status & Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $m)
                    <tr>
                        <td>
                            <strong class="text-primary d-block">#{{ $m->ticket_number ?: 'PSN-' . $m->id }}</strong>
                            <small class="text-muted">{{ date('d/m/Y H:i', strtotime($m->created_at)) }} WIB</small>
                        </td>
                        <td>
                            <strong class="text-dark d-block">{{ $m->name }}</strong>
                            <small class="text-muted d-block"><i class="bi bi-envelope me-1"></i>{{ $m->email }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1 mb-1 d-inline-block small">
                                {{ $m->topic }}
                            </span>
                            @if ($m->order_number)
                                <div class="small mt-1">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        <i class="bi bi-receipt me-1"></i>{{ $m->order_number }}
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong class="d-block text-dark small mb-1">{{ $m->subject }}</strong>
                            <p class="small text-secondary mb-1" style="max-width: 340px; line-height: 1.5;">{{ Str::limit($m->message, 120) }}</p>

                            @if (!empty($m->reply))
                                <div class="bg-light p-2 rounded-2 small text-dark border-start border-success border-3 mt-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-success" style="font-size: 0.75rem;"><i class="bi bi-check2-all me-1"></i>Balasan Admin:</strong>
                                        <span class="text-muted" style="font-size: 0.7rem;">{{ $m->replied_at ? date('d/m/Y H:i', strtotime($m->replied_at)) : '' }}</span>
                                    </div>
                                    <div class="text-secondary small">{{ Str::limit($m->reply, 100) }}</div>
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="mb-2">
                                @if ($m->status === 'Sudah Dibalas')
                                    <span class="badge bg-success rounded-pill px-2 py-1 small">Sudah Dibalas</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1 small">Menunggu Tanggapan</span>
                                @endif
                            </div>
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.messages.show', $m->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-2" title="Lihat & Balas">
                                    <i class="bi bi-reply-fill"></i>
                                </a>
                                <form action="{{ route('admin.messages.destroy', $m->id) }}" method="POST" class="d-inline form-confirm-delete" data-item-name="pesan tiket dari &quot;{{ addslashes($m->name) }}&quot;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="Hapus Pesan">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">Belum ada pesan pelanggan yang sesuai dengan kriteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($messages->hasPages())
        <div class="p-3 border-top">
            {{ $messages->links() }}
        </div>
    @endif
</div>
@endsection
