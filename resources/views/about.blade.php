@extends('layouts.app')

@section('title', 'Tentang Kami - BooksVy')

@section('content')

<div class="row g-5 align-items-center mb-5">
    <div class="col-lg-6">
        <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold mb-3">Tentang BooksVy</span>
        <h2 class="display-6 fw-bold mb-3">Gerakan Membaca untuk Masa Depan Lebih Cerah</h2>
        <p class="text-secondary" style="line-height: 1.8;">
            <strong>BooksVy</strong> lahir dari komitmen mendalam untuk menghadirkan buku bacaan berkualitas, asli, dan terjangkau ke seluruh pelosok tanah air. Kami percaya bahwa akses terhadap literasi bermutu adalah hak setiap insan, dan setiap halaman yang dibaca membuka pintu kesempatan baru.
        </p>
        <p class="text-secondary" style="line-height: 1.8;">
            Terinspirasi dari model toko buku berkelanjutan, BooksVy menerapkan sistem kurasi ketat yang hanya bermitra dengan penerbit resmi, sehingga setiap buku yang sampai ke tangan Anda bergaransi 100% original.
        </p>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white p-4">
            <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-shield-check me-2"></i>Komitmen Layanan Kami</h5>
            <div class="d-flex flex-column gap-3">
                <div class="d-flex gap-3">
                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    <div>
                        <strong class="d-block text-dark">100% Buku Asli Penerbit Resmi</strong>
                        <small class="text-muted">Bebas buku bajakan. Kami menjamin keaslian setiap cetakan demi menghargai royalti penulis dan penerbit.</small>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <i class="bi bi-truck text-primary fs-4"></i>
                    <div>
                        <strong class="d-block text-dark">Gratis Ongkir & Pengemasan Rapi</strong>
                        <small class="text-muted">Setiap buku dilapisi bubble wrap tebal dan kardus pelindung agar tiba dalam kondisi sempurna.</small>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <i class="bi bi-credit-card-2-front text-warning fs-4"></i>
                    <div>
                        <strong class="d-block text-dark">Sistem Payment at Delivery</strong>
                        <small class="text-muted">Kemudahan verifikasi transfer instan sebelum proses pengiriman demi keamanan transaksi bersama.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-5" id="faq">
    <h4 class="fw-bold mb-4 text-center">Pertanyaan yang Sering Diajukan (FAQ)</h4>
    <div class="accordion accordion-flush" id="faqAccordion">
        <div class="accordion-item border-bottom py-2">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                    Apa yang dimaksud dengan sistem Payment at Delivery di BooksVy?
                </button>
            </h2>
            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body text-secondary small" style="line-height: 1.7;">
                    Sistem <em>Payment at Delivery</em> di BooksVy bukan sistem COD konvensional bayar di tempat, melainkan metode pembayaran aman di mana pelanggan menyelesaikan transfer pembayaran saat pemesanan selesai, dan status langsung diverifikasi lunas sehingga paket buku dapat segera dikemas dan diserahkan ke jasa kurir.
                </div>
            </div>
        </div>
        <div class="accordion-item border-bottom py-2">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                    Berapa lama estimasi pengiriman paket buku sampai ke rumah saya?
                </button>
            </h2>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body text-secondary small" style="line-height: 1.7;">
                    Untuk wilayah Jabodetabek, estimasi pengiriman adalah 1 – 2 hari kerja. Untuk kota-kota di pulau Jawa berkisar antara 2 – 3 hari kerja, sedangkan luar pulau Jawa berkisar 3 – 5 hari kerja tergantung jangkauan kurir ekspedisi.
                </div>
            </div>
        </div>
        <div class="accordion-item py-2">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                    Bagaimana jika buku yang saya terima rusak atau cacat cetak?
                </button>
            </h2>
            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body text-secondary small" style="line-height: 1.7;">
                    Kami memberikan garansi retur 100% ganti baru tanpa biaya tambahan. Anda cukup menghubungi admin melalui halaman Hubungi Kami dengan menyertakan foto/video kendala produk.
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
