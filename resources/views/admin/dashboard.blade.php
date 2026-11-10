@extends('layouts.admin')

@section('title', 'Dashboard Administrator - BooksVy')

@section('content')

<!-- Header Dashboard -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Administrator</h2>
        <p class="text-muted small mb-0">Kontrol dan monitor keseluruhan data buku, transaksi pemesanan, dan pengguna BooksVy</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.books.create') }}" class="btn btn-primary rounded-pill px-3 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Buku Baru
        </a>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-primary rounded-pill px-3">
            <i class="bi bi-tag me-1"></i> Kelola Kategori
        </a>
    </div>
</div>

<!-- Statistik KPI Cards -->
<div class="row g-3 mb-5">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">TOTAL PENDAPATAN (LUNAS)</span>
                <div class="rounded-3 bg-success-subtle p-2 text-success"><i class="bi bi-cash-stack fs-5"></i></div>
            </div>
            <h3 class="fw-bold text-success mb-0">{{ formatRupiah($totalRevenue) }}</h3>
            <small class="text-muted mt-2 d-block">Dari seluruh transaksi terverifikasi</small>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">TOTAL PESANAN</span>
                <div class="rounded-3 bg-primary-subtle p-2 text-primary"><i class="bi bi-bag-check fs-5"></i></div>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $totalOrders }}</h3>
            <small class="text-muted mt-2 d-block"><a href="{{ route('admin.orders.index') }}" class="text-decoration-none">Kelola Pesanan &rarr;</a></small>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">TOTAL KOLEKSI BUKU</span>
                <div class="rounded-3 bg-warning-subtle p-2 text-warning-emphasis"><i class="bi bi-book fs-5"></i></div>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $totalBooks }}</h3>
            <small class="text-muted mt-2 d-block"><a href="{{ route('admin.books.index') }}" class="text-decoration-none">Lihat Semua Buku &rarr;</a></small>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">TOTAL PENGGUNA TERDAFTAR</span>
                <div class="rounded-3 bg-info-subtle p-2 text-info"><i class="bi bi-people fs-5"></i></div>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $totalUsers }}</h3>
            <small class="text-muted mt-2 d-block"><a href="{{ route('admin.users.index') }}" class="text-decoration-none">Kelola Pelanggan &rarr;</a></small>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Tabel Pesanan Terbaru -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Pesanan Masuk Terbaru</h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status Bayar</th>
                            <th>Pengiriman</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $ro)
                            <tr>
                                <td>
                                    <strong class="d-block text-primary small">#{{ $ro->order_number }}</strong>
                                    <small class="text-muted">{{ $ro->created_at ? $ro->created_at->format('d/m/Y H:i') : '-' }}</small>
                                </td>
                                <td>
                                    <span class="d-block fw-semibold small text-dark">{{ $ro->recipient_name }}</span>
                                    <small class="text-muted">{{ $ro->recipient_phone }}</small>
                                </td>
                                <td class="fw-bold text-dark small">{{ formatRupiah($ro->total_amount) }}</td>
                                <td>{!! renderPaymentStatusBadge($ro->payment_status) !!}</td>
                                <td>{!! renderOrderStatusBadge($ro->order_status) !!}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.orders.show', $ro->id) }}" class="btn btn-light btn-sm rounded-circle" title="Detail Pesanan">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted small">Belum ada pesanan terbaru.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Buku dengan Stok Menipis -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-exclamation-octagon me-2"></i>Stok Kritis / Habis</h6>
            </div>
            <div class="p-3">
                <div class="d-flex flex-column gap-3">
                    @forelse($criticalStockBooks as $csb)
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light">
                            <div class="d-flex align-items-center gap-2">
                                {!! bookCoverHtml($csb->cover_image, $csb->title, 'thumb') !!}
                                <div>
                                    <h6 class="mb-0 small fw-bold text-truncate" style="max-width: 150px;">{{ $csb->title }}</h6>
                                    <small class="text-muted">{{ formatRupiah($csb->price) }}</small>
                                </div>
                            </div>
                            <div>
                                @if($csb->stock <= 0)
                                    <span class="badge bg-secondary text-white rounded-pill px-2 py-1">Habis</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1">Sisa {{ $csb->stock }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">Semua stok buku dalam batas aman.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
