@extends('layouts.admin')

@section('title', 'Kelola Pesanan #' . $order->order_number . ' - Admin BooksVy')

@section('content')

<div class="mb-4">
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 mb-3">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pesanan
    </a>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="fw-bold mb-1">Pesanan #{{ $order->order_number }}</h3>
            <p class="text-muted small mb-0">Dibuat pada {{ $order->created_at ? $order->created_at->format('d F Y, H:i') : '-' }} WIB</p>
        </div>
        <div class="d-flex gap-2">
            {!! renderPaymentStatusBadge($order->payment_status) !!}
            {!! renderOrderStatusBadge($order->order_status) !!}
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-lg-8">
        <!-- Rincian Buku -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="fw-bold mb-0">Daftar Buku dalam Pesanan</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Buku</th>
                            <th class="text-center">Harga Satuan</th>
                            <th class="text-center">Kuantitas</th>
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
                                            <h6 class="fw-bold mb-0 small">{{ $item->book_title }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center small">{{ formatRupiah($item->price) }}</td>
                                <td class="text-center small">{{ $item->quantity }}</td>
                                <td class="text-end fw-bold text-primary small">{{ formatRupiah($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Alamat Pengiriman -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt-fill text-primary me-2"></i>Informasi Alamat & Penerima</h6>
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
                    <span class="text-muted">Alamat Pengiriman Lengkap:</span>
                    <div class="fw-semibold text-dark mt-1">{{ $order->shipping_address }}</div>
                </div>
                @if($order->notes)
                    <div class="col-12">
                        <span class="text-muted">Catatan dari Pelanggan:</span>
                        <div class="fst-italic text-secondary mt-1">"{{ $order->notes }}"</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @php
            $isCompleted = $isCompleted ?? ($order->order_status === 'Selesai');
        @endphp
        <!-- Card Update Status Pesanan -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 90px;">
            <h5 class="fw-bold mb-3">Pembaruan Status Pesanan</h5>

            @if($isCompleted)
                <!-- Notifikasi Pesanan Terkunci Selesai -->
                <div class="alert alert-success border-0 rounded-4 p-3 mb-4">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-lock-fill fs-4 text-success flex-shrink-0 mt-1"></i>
                        <div>
                            <strong class="d-block text-success">Pesanan Telah Selesai</strong>
                            <p class="small text-success-emphasis mb-0">
                                Status pengiriman pesanan ini sudah <strong>Selesai</strong> dan seluruh proses telah tuntas. Status tidak dapat diubah lagi.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.orders.updateStatus', $order->id) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Status Pembayaran</label>
                    <select name="payment_status" class="form-select {{ $isCompleted ? 'bg-light' : '' }}" {{ $isCompleted ? 'disabled' : '' }}>
                        <!-- Sesuai permintaan user: hanya ada 'Lunas' dan 'Batal' saja -->
                        <option value="Lunas" {{ $order->payment_status === 'Lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="Dibatalkan" {{ in_array($order->payment_status, ['Dibatalkan', 'Batal']) ? 'selected' : '' }}>Batal</option>
                    </select>
                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Pilihan status: Lunas dan Batal</small>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted">Status Pengiriman / Pesanan</label>
                    <select name="order_status" class="form-select {{ $isCompleted ? 'bg-light' : '' }}" {{ $isCompleted ? 'disabled' : '' }}>
                        <option value="Menunggu Konfirmasi" {{ $order->order_status === 'Menunggu Konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                        <option value="Sedang Diproses" {{ $order->order_status === 'Sedang Diproses' ? 'selected' : '' }}>Sedang Diproses</option>
                        <option value="Sedang Dikirim" {{ $order->order_status === 'Sedang Dikirim' ? 'selected' : '' }}>Sedang Dikirim</option>
                        <option value="Selesai" {{ $order->order_status === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Dibatalkan" {{ $order->order_status === 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                @if(!$isCompleted)
                    <button type="submit" class="btn btn-primary rounded-pill py-2 fw-semibold w-100 shadow-sm mb-3">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Pembaruan Status
                    </button>
                @else
                    <button type="button" class="btn btn-secondary rounded-pill py-2 fw-semibold w-100 shadow-sm mb-3" disabled>
                        <i class="bi bi-lock-fill me-1"></i> Terkunci (Selesai)
                    </button>
                @endif
            </form>

            <hr class="my-3">

            <div class="d-flex justify-content-between text-muted small mb-2">
                <span>Metode Pembayaran</span>
                <span class="text-dark fw-semibold text-end" style="max-width: 160px;">{{ $order->payment_method }}</span>
            </div>

            <div class="d-flex justify-content-between text-muted small mb-3">
                <span>Total Tagihan</span>
                <span class="fw-bold text-primary fs-5">{{ formatRupiah($order->total_amount) }}</span>
            </div>
        </div>
    </div>
</div>

@endsection
