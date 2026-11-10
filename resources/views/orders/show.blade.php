@extends('layouts.app')

@section('title', 'Detail Pesanan #' . $order->order_number . ' - BooksVy')

@section('content')

<div class="mb-4">
    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 mb-3">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat Pesanan
    </a>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="fw-bold mb-1">Rincian Pesanan #{{ $order->order_number }}</h3>
            <p class="text-muted small mb-0">Dipesan pada {{ $order->created_at ? $order->created_at->format('d F Y, H:i') : '-' }} WIB</p>
        </div>
        <div class="d-flex gap-2">
            {!! renderPaymentStatusBadge($order->payment_status) !!}
            {!! renderOrderStatusBadge($order->order_status) !!}
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="fw-bold mb-0">Daftar Buku Pesanan</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Buku</th>
                            <th class="text-center">Harga</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        {!! bookCoverHtml($item->book->cover_image ?? null, $item->book_title, 'thumb') !!}
                                        <div>
                                            <h6 class="fw-bold mb-1">{{ $item->book_title }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">{{ formatRupiah($item->price) }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end fw-bold text-primary">{{ formatRupiah($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt-fill text-primary me-2"></i>Informasi Alamat & Pengiriman</h6>
            <div class="row g-3 small">
                <div class="col-sm-6">
                    <span class="text-muted">Nama Penerima:</span>
                    <div class="fw-semibold text-dark fs-6">{{ $order->recipient_name }}</div>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted">Nomor WhatsApp / HP:</span>
                    <div class="fw-semibold text-dark fs-6">{{ $order->recipient_phone }}</div>
                </div>
                <div class="col-12">
                    <span class="text-muted">Alamat Lengkap Pengiriman:</span>
                    <div class="fw-semibold text-dark mt-1">{{ $order->shipping_address }}</div>
                </div>
                @if($order->notes)
                    <div class="col-12">
                        <span class="text-muted">Catatan Khusus:</span>
                        <div class="fst-italic text-secondary mt-1">"{{ $order->notes }}"</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 90px;">
            <h5 class="fw-bold mb-3">Ringkasan Pembayaran</h5>

            <div class="d-flex justify-content-between text-muted small mb-2">
                <span>Metode Pembayaran</span>
                <span class="text-dark fw-semibold text-end" style="max-width: 170px;">{{ $order->payment_method }}</span>
            </div>

            <div class="d-flex justify-content-between text-muted small mb-2">
                <span>Status Pembayaran</span>
                <span>{!! renderPaymentStatusBadge($order->payment_status) !!}</span>
            </div>

            <div class="d-flex justify-content-between text-muted small mb-3">
                <span>Ongkos Kirim</span>
                <span class="text-success fw-semibold"><i class="bi bi-tag-fill me-1"></i>Gratis Promo</span>
            </div>

            <hr class="my-2">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="fw-bold text-dark fs-5">Total Pembayaran</span>
                <span class="fs-4 fw-bold text-primary">{{ formatRupiah($order->total_amount) }}</span>
            </div>

            <div class="alert alert-info border-0 rounded-3 small mb-3">
                <i class="bi bi-shield-check me-1"></i> Sistem <strong>Payment at Delivery</strong>: Pesanan Anda telah tercatat dan status pembayaran telah diverifikasi.
            </div>

            <a href="{{ route('contact') }}" class="btn btn-outline-secondary btn-sm rounded-pill py-2 w-100">
                <i class="bi bi-headset me-1"></i> Butuh Bantuan Terkait Pesanan?
            </a>
        </div>
    </div>
</div>

@endsection
