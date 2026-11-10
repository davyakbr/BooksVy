@extends('layouts.app')

@section('title', 'Checkout - Payment at Delivery - BooksVy')

@section('content')

<!-- Stepper Alur Belanja -->
<div class="order-stepper mb-4 d-none d-md-flex">
    <div class="step-item">
        <div class="step-number"><i class="bi bi-check"></i></div>
        <span>1. Keranjang Belanja</span>
    </div>
    <div class="d-flex align-items-center text-muted"><i class="bi bi-chevron-right"></i></div>
    <div class="step-item active">
        <div class="step-number">2</div>
        <span>2. Alamat & Pengiriman</span>
    </div>
    <div class="d-flex align-items-center text-muted"><i class="bi bi-chevron-right"></i></div>
    <div class="step-item">
        <div class="step-number">3</div>
        <span>3. Pembayaran (Payment at Delivery)</span>
    </div>
</div>

<div class="mb-4">
    <h3 class="fw-bold mb-1"><i class="bi bi-shield-check text-primary me-2"></i>Checkout & Pembayaran Pesanan</h3>
    <p class="text-muted small mb-0">Lengkapi alamat pengiriman dan pilih saluran pembayaran <em>Payment at Delivery</em> Anda</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger rounded-4 border-0 shadow-sm p-3 mb-4">
        <h6 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Mohon lengkapi data dengan benar:</h6>
        <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('checkout.process') }}">
    @csrf
    <div class="row g-4 mb-5">
        <!-- Kolom Formulir Pengiriman & Pembayaran -->
        <div class="col-lg-7">
            <!-- Card Alamat Pengiriman -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-geo-alt-fill text-primary"></i> Alamat & Penerima Paket
                </h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Nama Lengkap Penerima <span class="text-danger">*</span></label>
                        <input type="text" name="recipient_name" class="form-control" value="{{ old('recipient_name', $user->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Nomor WhatsApp / HP Aktif <span class="text-danger">*</span></label>
                        <input type="text" name="recipient_phone" class="form-control" value="{{ old('recipient_phone', $user->phone) }}" placeholder="Contoh: 081234567890" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Alamat Pengiriman Lengkap <span class="text-danger">*</span></label>
                        <textarea name="shipping_address" class="form-control" rows="3" placeholder="Nama Jalan, No. Rumah, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten, Kode Pos..." required>{{ old('shipping_address', $user->address) }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Catatan Khusus Pengiriman (Opsional)</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Contoh: Titipkan di pos satpam jika tidak ada orang di rumah">
                    </div>
                </div>
            </div>

            <!-- Card Metode Pembayaran (Payment at Delivery) -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-credit-card-2-back-fill text-success"></i> Metode Pembayaran: Payment at Delivery
                    </h5>
                </div>

                <p class="small text-muted mb-3">
                    Pilih saluran pembayaran Anda. Saat menekan tombol <strong>Bayar Sekarang</strong>, transaksi langsung berstatus <strong>Lunas</strong> dan pesanan segera diproses untuk dikirim:
                </p>

                <div class="d-flex flex-column gap-2 mb-3">
                    <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer bg-light">
                        <input type="radio" name="payment_channel" value="Transfer Bank BCA" {{ old('payment_channel', 'Transfer Bank BCA') === 'Transfer Bank BCA' ? 'checked' : '' }} class="form-check-input mt-0">
                        <div class="flex-grow-1">
                            <strong class="d-block text-dark">Bank BCA (Virtual Account / Transfer)</strong>
                            <span class="small text-muted">No. Rekening: <strong>8830-1234-5678</strong> a/n Toko Buku BooksVy</span>
                        </div>
                        <i class="bi bi-bank fs-4 text-primary"></i>
                    </label>

                    <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer bg-light">
                        <input type="radio" name="payment_channel" value="Transfer Bank Mandiri" {{ old('payment_channel') === 'Transfer Bank Mandiri' ? 'checked' : '' }} class="form-check-input mt-0">
                        <div class="flex-grow-1">
                            <strong class="d-block text-dark">Bank Mandiri</strong>
                            <span class="small text-muted">No. Rekening: <strong>137-00-9876543-2</strong> a/n BooksVy Indonesia</span>
                        </div>
                        <i class="bi bi-bank2 fs-4 text-primary"></i>
                    </label>

                    <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer bg-light">
                        <input type="radio" name="payment_channel" value="Transfer Bank BRI" {{ old('payment_channel') === 'Transfer Bank BRI' ? 'checked' : '' }} class="form-check-input mt-0">
                        <div class="flex-grow-1">
                            <strong class="d-block text-dark">Bank BRI</strong>
                            <span class="small text-muted">No. Rekening: <strong>0206-01-002345-50-8</strong> a/n BooksVy Indonesia</span>
                        </div>
                        <i class="bi bi-building fs-4 text-primary"></i>
                    </label>

                    <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer bg-light">
                        <input type="radio" name="payment_channel" value="QRIS Instant" {{ old('payment_channel') === 'QRIS Instant' ? 'checked' : '' }} class="form-check-input mt-0">
                        <div class="flex-grow-1">
                            <strong class="d-block text-dark">QRIS (Gopay / OVO / Dana / ShopeePay / BCA Mobile)</strong>
                            <span class="small text-muted">Pindai kode QRIS dinamis pada halaman konfirmasi setelah checkout</span>
                        </div>
                        <i class="bi bi-qr-code-scan fs-4 text-primary"></i>
                    </label>
                </div>

                <div class="alert alert-warning border-0 small rounded-3 mb-0">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    <strong>Penting:</strong> BooksVy menerapkan sistem pembayaran <em>Payment at Delivery</em> di mana paket buku tidak menggunakan sistem bayar di tempat (COD biasa), melainkan pembayaran diverifikasi sebelum barang diserahkan ke jasa kurir.
                </div>
            </div>
        </div>

        <!-- Kolom Ringkasan Pesanan & Tombol Konfirmasi -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 90px;">
                <h5 class="fw-bold mb-3 d-flex align-items-center justify-content-between">
                    <span>Ringkasan Pesanan</span>
                    <span class="badge bg-light text-secondary border">{{ $totalCount }} Buku</span>
                </h5>

                <div class="order-items-scroll mb-3" style="max-height: 260px; overflow-y: auto;">
                    @foreach ($cartItems as $bId => $item)
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                {!! bookCoverHtml($item['cover_image'] ?? null, $item['title'], 'thumb') !!}
                                <div>
                                    <h6 class="mb-0 small fw-bold text-truncate" style="max-width: 170px;">{{ $item['title'] }}</h6>
                                    <small class="text-muted">{{ $item['qty'] }} x {{ formatRupiah($item['price']) }}</small>
                                </div>
                            </div>
                            <div class="fw-semibold text-primary small">
                                {{ formatRupiah($item['price'] * $item['qty']) }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between text-muted small mb-2">
                    <span>Subtotal Produk</span>
                    <span class="text-dark fw-semibold">{{ formatRupiah($totalPrice) }}</span>
                </div>

                <div class="d-flex justify-content-between text-muted small mb-3">
                    <span>Biaya Kirim & Packing</span>
                    <span class="text-success fw-semibold"><i class="bi bi-tag-fill me-1"></i>Gratis Promo</span>
                </div>

                <hr class="my-2">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold text-dark fs-5">Total Tagihan</span>
                    <span class="fs-4 fw-bold text-primary">{{ formatRupiah($totalPrice) }}</span>
                </div>

                <button type="submit" class="btn btn-success rounded-pill py-3 fw-bold w-100 shadow-sm mb-2 fs-5">
                    <i class="bi bi-wallet2 me-2"></i> Bayar Sekarang ({{ formatRupiah($totalPrice) }})
                </button>

                <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill py-2 w-100">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Keranjang
                </a>
            </div>
        </div>
    </div>
</form>

@endsection
