@extends('layouts.app')

@section('title', 'Pesanan Saya - BooksVy')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-receipt text-primary me-2"></i>Riwayat Pesanan Saya</h3>
        <p class="text-muted small mb-0">Daftar transaksi dan status pemesanan buku Anda di BooksVy</p>
    </div>
    <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
        <i class="bi bi-plus-lg me-1"></i> Beli Buku Baru
    </a>
</div>

@if(empty($orders) || count($orders) === 0)
    <div class="card border-0 shadow-sm text-center py-5 rounded-4 mb-5 bg-white">
        <div class="card-body">
            <div class="mb-3 text-muted">
                <i class="bi bi-bag-x" style="font-size: 5rem;"></i>
            </div>
            <h4 class="fw-bold">Belum Ada Riwayat Pesanan</h4>
            <p class="text-muted small mb-4">Anda belum pernah melakukan pemesanan buku di BooksVy.</p>
            <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm">
                <i class="bi bi-book me-1"></i> Jelajahi Katalog Buku Sekarang
            </a>
        </div>
    </div>
@else
    <div class="row g-4 mb-5">
        @foreach($orders as $ord)
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <div class="card-header bg-light py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold text-dark fs-6">#{{ $ord->order_number }}</span>
                            <span class="text-muted small">|</span>
                            <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i>{{ $ord->created_at ? $ord->created_at->format('d M Y, H:i') : '-' }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            {!! renderPaymentStatusBadge($ord->payment_status) !!}
                            {!! renderOrderStatusBadge($ord->order_status) !!}
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row align-items-center g-3">
                            <div class="col-md-7">
                                <div class="d-flex flex-column gap-2">
                                    @foreach($ord->items->take(2) as $it)
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-book text-muted"></i>
                                            <span class="fw-medium text-dark small text-truncate" style="max-width: 320px;">{{ $it->book_title }}</span>
                                            <span class="text-muted small">x{{ $it->quantity }}</span>
                                        </div>
                                    @endforeach
                                    @if($ord->items->count() > 2)
                                        <small class="text-muted fst-italic">+{{ $ord->items->count() - 2 }} buku lainnya</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <span class="d-block text-muted small">Total Tagihan:</span>
                                <span class="fw-bold text-primary fs-5">{{ formatRupiah($ord->total_amount) }}</span>
                            </div>
                            <div class="col-md-2 text-md-end">
                                <a href="{{ route('orders.show', $ord->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 w-100">
                                    Rincian <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
