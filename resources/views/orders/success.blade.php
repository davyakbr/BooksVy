@extends('layouts.app')

@section('title', 'Pesanan Berhasil #' . $order->order_number . ' - BooksVy')

@section('content')

<!-- Stepper Selesai -->
<div class="order-stepper mb-4 d-none d-md-flex no-print">
    <div class="step-item">
        <div class="step-number"><i class="bi bi-check"></i></div>
        <span>1. Keranjang Belanja</span>
    </div>
    <div class="d-flex align-items-center text-muted"><i class="bi bi-chevron-right"></i></div>
    <div class="step-item">
        <div class="step-number"><i class="bi bi-check"></i></div>
        <span>2. Alamat & Pengiriman</span>
    </div>
    <div class="d-flex align-items-center text-muted"><i class="bi bi-chevron-right"></i></div>
    <div class="step-item active">
        <div class="step-number"><i class="bi bi-check"></i></div>
        <span>3. Pembayaran (Payment at Delivery)</span>
    </div>
</div>

<!-- Kartu Sukses Pemesanan & Pembayaran -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5 bg-white">
    <div class="bg-success text-white text-center p-4 p-md-5">
        <div class="display-3 mb-2"><i class="bi bi-patch-check-fill text-warning"></i></div>
        <h2 class="fw-bold mb-1">Pembayaran Berhasil & Pesanan Diterima!</h2>
        <p class="lead text-white-50 mb-0">Transaksi Anda telah lunas dan pesanan segera dikirimkan:</p>
        <span class="badge bg-white text-success fs-5 px-4 py-2 rounded-pill mt-3 shadow-sm fw-bold">
            #{{ $order->order_number }}
        </span>
    </div>

    <div class="card-body p-4 p-md-5">
        <!-- Notifikasi Pembayaran Lunas -->
        <div class="alert alert-success border-0 rounded-4 p-4 mb-4 shadow-sm bg-success-subtle">
            <div class="d-flex align-items-start gap-3">
                <i class="bi bi-check-circle-fill text-success fs-2"></i>
                <div class="flex-grow-1">
                    <h5 class="fw-bold text-dark mb-1">Status Pembayaran: LUNAS</h5>
                    <p class="text-secondary small mb-3">
                        Terima kasih! Pembayaran sebesar <strong>{{ formatRupiah($order->total_amount) }}</strong> via <strong>{{ $order->payment_method }}</strong> telah berhasil diselesaikan. Admin tidak perlu memverifikasi manual lagi, paket buku Anda saat ini <strong>Sedang Diproses</strong> untuk pengemasan dan segera diserahkan ke jasa kurir logistik.
                    </p>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge bg-success px-3 py-2 rounded-pill fs-6">
                            <i class="bi bi-wallet2 me-1"></i> Pembayaran: LUNAS
                        </span>
                        <span class="badge bg-primary px-3 py-2 rounded-pill fs-6">
                            <i class="bi bi-box-seam me-1"></i> Status Pengiriman: {{ $order->order_status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Penerima & Status Transaksi -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card bg-light border-0 rounded-4 p-3 h-100">
                    <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Alamat & Penerima</h6>
                    <p class="mb-1 small"><strong>Nama:</strong> {{ $order->recipient_name }}</p>
                    <p class="mb-1 small"><strong>Telepon:</strong> {{ $order->recipient_phone }}</p>
                    <p class="mb-1 small"><strong>Alamat:</strong> {!! nl2br(e($order->shipping_address)) !!}</p>
                    @if (!empty($order->notes))
                        <p class="mb-0 small text-muted"><strong>Catatan:</strong> {{ $order->notes }}</p>
                    @endif
                </div>
            </div>

            <div class="col-md-6">
                <div class="card bg-light border-0 rounded-4 p-3 h-100">
                    <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-info-circle-fill text-primary me-1"></i> Status Pemesanan</h6>
                    <p class="mb-2 small d-flex justify-content-between">
                        <span>Status Pembayaran:</span>
                        <span>{!! renderPaymentStatusBadge($order->payment_status) !!}</span>
                    </p>
                    <p class="mb-2 small d-flex justify-content-between">
                        <span>Status Pengiriman:</span>
                        <span>{!! renderOrderStatusBadge($order->order_status) !!}</span>
                    </p>
                    <p class="mb-2 small d-flex justify-content-between">
                        <span>Metode Pembayaran:</span>
                        <span class="fw-semibold text-dark">{{ $order->payment_method }}</span>
                    </p>
                    <p class="mb-0 small d-flex justify-content-between">
                        <span>Waktu Pemesanan:</span>
                        <span class="text-muted">{{ date('d M Y, H:i', strtotime($order->created_at)) }} WIB</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Tabel Item Buku yang Dipesan -->
        <h5 class="fw-bold mb-3"><i class="bi bi-box-seam text-primary me-1"></i> Rincian Buku yang Dipesan</h5>
        <div class="table-responsive mb-4">
            <table class="table align-middle custom-table">
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th class="text-center">Harga</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $it)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    {!! bookCoverHtml($it->book ? $it->book->cover_image : null, $it->book_title, 'thumb') !!}
                                    <div>
                                        <h6 class="mb-0 fw-bold small">{{ $it->book_title }}</h6>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">{{ formatRupiah($it->price) }}</td>
                            <td class="text-center">{{ $it->quantity }}</td>
                            <td class="text-end fw-bold text-primary">{{ formatRupiah($it->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total Pembayaran:</td>
                        <td class="text-end fw-bold text-primary fs-5">{{ formatRupiah($order->total_amount) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tombol Aksi Navigasi -->
        <div class="d-flex flex-wrap gap-2 justify-content-between pt-3 border-top no-print">
            <a href="{{ route('orders.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                <i class="bi bi-receipt me-1"></i> Lihat Daftar Pesanan Saya
            </a>
            
            <div class="d-flex gap-2">
                <button type="button" onclick="window.print()" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-printer me-1"></i> Cetak Bukti Pesanan
                </button>
                <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-shop me-1"></i> Belanja Lagi
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
