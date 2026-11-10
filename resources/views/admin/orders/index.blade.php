@extends('layouts.admin')

@section('title', 'Kelola Pesanan - Admin BooksVy')

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-bag-check text-primary me-2"></i>Kelola Pesanan Pelanggan</h3>
        <p class="text-muted small mb-0">Pantau status transaksi Payment at Delivery dan progres pengiriman paket</p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-2 align-items-center">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Cari nomor pesanan, nama penerima, nomor HP..." value="{{ $search ?? '' }}">
                    <button type="submit" class="btn btn-primary px-3">Cari</button>
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="">-- Semua Status Pengiriman --</option>
                    <option value="Menunggu Konfirmasi" {{ ($status ?? '') === 'Menunggu Konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                    <option value="Sedang Diproses" {{ ($status ?? '') === 'Sedang Diproses' ? 'selected' : '' }}>Sedang Diproses</option>
                    <option value="Sedang Dikirim" {{ ($status ?? '') === 'Sedang Dikirim' ? 'selected' : '' }}>Sedang Dikirim</option>
                    <option value="Selesai" {{ ($status ?? '') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="Dibatalkan" {{ ($status ?? '') === 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-2 text-md-end">
                @if(!empty($search ?? '') || !empty($status ?? ''))
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill w-100">
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
                    <th style="width: 25%;">No. Pesanan & Tanggal</th>
                    <th style="width: 20%;">Penerima</th>
                    <th style="width: 15%;">Total Tagihan</th>
                    <th style="width: 15%;">Status Pembayaran</th>
                    <th style="width: 15%;">Status Pesanan</th>
                    <th style="width: 5%;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $ord)
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}</td>
                        <td>
                            <strong class="text-primary small d-block">#{{ $ord->order_number }}</strong>
                            <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $ord->created_at ? $ord->created_at->format('d M Y, H:i') : '-' }}</small>
                        </td>
                        <td>
                            <strong class="d-block small text-dark">{{ $ord->recipient_name }}</strong>
                            <small class="text-muted">{{ $ord->recipient_phone }}</small>
                        </td>
                        <td class="fw-bold text-dark">{{ formatRupiah($ord->total_amount) }}</td>
                        <td>{!! renderPaymentStatusBadge($ord->payment_status) !!}</td>
                        <td>{!! renderOrderStatusBadge($ord->order_status) !!}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.orders.show', $ord->id) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm text-nowrap">
                                Kelola <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Tidak ada data pesanan yang ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection
