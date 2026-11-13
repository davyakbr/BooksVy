@extends('layouts.app')

@section('title', $book->title . ' - BooksVy')

@section('content')

<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ route('home', ['category' => $book->category_id]) }}" class="text-decoration-none">{{ $book->category->name ?? 'Kategori' }}</a></li>
        <li class="breadcrumb-item active text-truncate" style="max-width: 300px;" aria-current="page">{{ $book->title }}</li>
    </ol>
</nav>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
    <div class="card-body p-lg-5 p-4">
        <div class="row g-5">
            <!-- Kolom Cover Gambar Buku -->
            <div class="col-lg-5 col-md-6 text-center">
                <div class="position-relative d-inline-block rounded-3 overflow-hidden shadow" style="max-width: 340px; width: 100%;">
                    {!! bookCoverHtml($book->cover_image, $book->title, 'detail') !!}
                    
                    <span class="position-absolute top-0 start-0 m-3 badge bg-primary rounded-pill px-3 py-2 shadow-sm">
                        {{ $book->category->name ?? 'Kategori' }}
                    </span>
                </div>
            </div>

            <!-- Kolom Informasi & Form Order -->
            <div class="col-lg-7 col-md-6 d-flex flex-column justify-content-between">
                <div>
                    <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill mb-2">
                        ID Buku: #{{ $book->id }}
                    </span>
                    <h2 class="fw-bold text-dark mb-2">{{ $book->title }}</h2>
                    <p class="fs-5 text-muted mb-3">
                        Karya: <strong class="text-primary">{{ $book->author }}</strong>
                    </p>

                    <div class="d-flex align-items-baseline gap-3 mb-4">
                        <span class="display-6 fw-bold text-primary">{{ formatRupiah($book->price) }}</span>
                        <span class="badge {{ $book->stock > 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary text-white' }} fs-6 px-3 py-2 rounded-pill">
                            <i class="bi {{ $book->stock > 0 ? 'bi-check-circle-fill' : 'bi-dash-circle' }} me-1"></i>
                            {{ $book->stock > 0 ? 'Stok Tersedia: ' . $book->stock . ' unit' : 'Stok Habis' }}
                        </span>
                    </div>

                    <!-- Spesifikasi Buku Table/List -->
                    <div class="card bg-light border-0 rounded-3 mb-4">
                        <div class="card-body p-3">
                            <div class="row g-2 small">
                                <div class="col-6">
                                    <span class="text-muted">Penerbit:</span>
                                    <div class="fw-semibold text-dark">{{ $book->publisher ?? '-' }}</div>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Tahun Terbit:</span>
                                    <div class="fw-semibold text-dark">{{ $book->year ?? '-' }}</div>
                                </div>
                                <div class="col-6 mt-2">
                                    <span class="text-muted">Kategori:</span>
                                    <div class="fw-semibold text-dark">{{ $book->category->name ?? '-' }}</div>
                                </div>
                                <div class="col-6 mt-2">
                                    <span class="text-muted">Metode Pembayaran:</span>
                                    <div class="fw-semibold text-success"><i class="bi bi-shield-check me-1"></i> Payment at Delivery</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deskripsi / Sinopsis -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-file-text me-1 text-primary"></i> Sinopsis & Deskripsi</h6>
                        <p class="text-secondary" style="line-height: 1.7; text-align: justify;">
                            {!! nl2br(e($book->description ?? 'Belum ada deskripsi lengkap untuk buku ini.')) !!}
                        </p>
                    </div>
                </div>

                <!-- Form Tambah ke Keranjang -->
                <div class="pt-3 border-top">
                    @if ($book->stock > 0)
                        @auth
                            @if ($remainingStock <= 0)
                                <div class="alert alert-warning border-0 rounded-3 mb-3 p-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-info-circle-fill fs-5 text-warning-emphasis"></i>
                                        <strong class="text-dark">Buku ini sudah berada di keranjang belanja Anda</strong>
                                    </div>
                                    <p class="small text-muted mb-0">
                                        Anda telah memasukkan <strong>{{ $inCartQty }} unit</strong> (seluruh sisa stok yang tersedia saat ini). Anda tidak dapat menambah unit lagi untuk buku ini.
                                    </p>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <a href="{{ route('cart.index') }}" class="btn btn-warning rounded-pill px-4 py-2 fw-semibold shadow-sm">
                                        <i class="bi bi-cart3 me-1"></i> Buka Keranjang Belanja
                                    </a>
                                    <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2" title="Kembali ke Katalog">
                                        <i class="bi bi-arrow-left me-1"></i> Katalog
                                    </a>
                                </div>
                            @else
                                <form method="POST" action="{{ route('cart.add') }}" class="row g-3 align-items-center form-add-cart" id="formDetailAddToCart">
                                    @csrf
                                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                                    
                                    <div class="col-sm-4 col-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Jumlah Pembelian</label>
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary btn-qty-minus" type="button"><i class="bi bi-dash"></i></button>
                                            <input type="number" 
                                                   name="quantity" 
                                                   class="form-control text-center qty-input" 
                                                   id="detailBookQtyInput" 
                                                   value="1" 
                                                   min="1" 
                                                   max="{{ $remainingStock }}" 
                                                   data-max-stock="{{ $remainingStock }}">
                                            <button class="btn btn-outline-secondary btn-qty-plus" type="button"><i class="bi bi-plus"></i></button>
                                        </div>
                                    </div>

                                    <div class="col-sm-8 col-12 d-flex gap-2 align-items-end">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 flex-grow-1 shadow-sm" id="btnDetailAddToCart">
                                            <i class="bi bi-cart-plus-fill me-2"></i> Tambah ke Keranjang
                                        </button>
                                        <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2" title="Kembali ke Katalog">
                                            <i class="bi bi-arrow-left"></i>
                                        </a>
                                    </div>
                                </form>

                                @if ($inCartQty > 0)
                                    <div class="mt-3 small text-muted p-2 rounded-3 bg-light border border-light-subtle d-inline-block" id="inCartNotice">
                                        <i class="bi bi-bag-check-fill text-success me-1"></i>
                                        Sudah ada <strong class="text-dark">{{ $inCartQty }} unit</strong> di keranjang Anda. Sisa yang dapat ditambahkan: <strong class="text-primary">{{ $remainingStock }} unit</strong>.
                                    </div>
                                @endif
                            @endif
                        @else
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <a href="{{ route('login') }}" class="btn btn-warning rounded-pill px-4 py-2 fw-semibold shadow-sm">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Masuk (Login) untuk Tambah ke Keranjang
                                </a>
                                <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2" title="Kembali ke Katalog">
                                    <i class="bi bi-arrow-left me-1"></i> Katalog
                                </a>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i> Silakan masuk (login) atau daftar akun terlebih dahulu untuk mulai memesan buku.
                            </small>
                        @endauth
                    @else
                        <div class="alert alert-secondary d-flex align-items-center gap-2 mb-0 rounded-3">
                            <i class="bi bi-dash-circle fs-5"></i>
                            <div>Maaf, produk ini sedang tidak tersedia (stok habis) untuk sementara waktu. Silakan cek kembali nanti atau hubungi Admin.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rekomendasi Buku Terkait -->
@if(!empty($relatedBooks) && count($relatedBooks) > 0)
    <div class="mb-5">
        <h4 class="fw-bold mb-4"><i class="bi bi-collection me-2 text-primary"></i> Buku Lainnya dalam Kategori Ini</h4>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 row-cols-xxl-6 g-4">
            @foreach ($relatedBooks as $rb)
                @php $isRbOut = ($rb->stock <= 0); @endphp
                <div class="col">
                    <div class="book-card h-100 {{ $isRbOut ? 'book-card-out-of-stock' : '' }}">
                        <div class="book-cover-wrapper">
                            {!! bookCoverHtml($rb->cover_image, $rb->title, 'card') !!}
                        </div>
                        <div class="book-card-body">
                            <h6 class="book-title"><a href="{{ route('books.show', $rb->id) }}" class="text-decoration-none {{ $isRbOut ? 'text-secondary' : 'text-dark' }}">{{ $rb->title }}</a></h6>
                            <div class="book-price {{ $isRbOut ? 'text-muted' : '' }} mb-3">{{ formatRupiah($rb->price) }}</div>
                            <a href="{{ route('books.show', $rb->id) }}" class="btn {{ $isRbOut ? 'btn-outline-secondary' : 'btn-outline-primary' }} btn-sm rounded-pill w-100">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@endsection
