@extends('layouts.app')

@section('title', 'Katalog Toko Buku Online Modern - BooksVy')

@section('content')

<!-- Hero Carousel Slider Banner -->
@if(!empty($banners) && count($banners) > 0)
<div id="booksVyHeroCarousel" class="carousel slide hero-carousel mb-5" data-bs-ride="carousel" data-bs-interval="4500">
    <div class="carousel-indicators carousel-indicators-custom">
        @foreach ($banners as $bi => $bn)
            <button type="button" data-bs-target="#booksVyHeroCarousel" data-bs-slide-to="{{ $bi }}"
                    class="{{ $bi === 0 ? 'active' : '' }}"
                    aria-current="{{ $bi === 0 ? 'true' : 'false' }}"
                    aria-label="Slide {{ $bi + 1 }}"></button>
        @endforeach
    </div>

    <div class="carousel-inner">
        @foreach ($banners as $bi => $bn)
        @php 
            $bannerImg = !empty($bn->image) ? $bn->image : null;
            $hasValidBannerImage = $bannerImg 
                && file_exists(public_path('uploads/' . $bannerImg)) 
                && !str_starts_with($bannerImg, 'book_') 
                && $bannerImg !== 'default_book.png';
        @endphp
        <div class="carousel-item {{ $bi === 0 ? 'active' : '' }}">
            <div class="carousel-slide-item {{ $bn->bg_class ?? 'hero-slide-1' }} text-white">
                <div class="container-fluid px-0">
                    <div class="row align-items-center g-4">
                        <div class="{{ $hasValidBannerImage ? 'col-lg-7 col-md-12' : 'col-12 col-lg-10' }}">
                            @if($bn->badge_text)
                                <span class="badge bg-white text-dark px-3 py-2 rounded-pill fw-bold shadow-sm mb-3 d-inline-flex align-items-center">
                                    {{ $bn->badge_text }}
                                </span>
                            @endif
                            <h1 class="display-5 fw-bold mb-3 text-white hero-slide-title">
                                {{ $bn->title }}
                            </h1>
                            @if($bn->subtitle)
                                <p class="lead text-white-50 mb-4 hero-slide-subtitle">
                                    {{ $bn->subtitle }}
                                </p>
                            @endif
                            <div class="d-flex flex-wrap gap-3">
                                @if($bn->btn1_label)
                                    <a href="{{ $bn->btn1_url ?? '#katalog' }}" class="btn btn-warning text-dark fw-bold rounded-pill px-4 py-2 shadow-sm">
                                        <i class="bi bi-grid-fill me-1"></i> {{ $bn->btn1_label }}
                                    </a>
                                @endif
                                @if($bn->btn2_label)
                                    <a href="{{ $bn->btn2_url ?? '#katalog' }}" class="btn btn-outline-light rounded-pill px-4 py-2">
                                        <i class="bi bi-book me-1"></i> {{ $bn->btn2_label }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        @if ($hasValidBannerImage)
                            <div class="col-lg-5 d-none d-lg-flex justify-content-center">
                                <div class="hero-slide-img-box">
                                    <img src="{{ asset('uploads/' . $bannerImg) }}" alt="{{ $bn->title }}" class="img-fluid hero-banner-photo shadow-lg rounded-4">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if (count($banners) > 1)
        <button class="carousel-control-prev carousel-btn-nav" type="button" data-bs-target="#booksVyHeroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Sebelumnya</span>
        </button>
        <button class="carousel-control-next carousel-btn-nav" type="button" data-bs-target="#booksVyHeroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Selanjutnya</span>
        </button>
    @endif
</div>
@endif

<!-- Value Proposition Props (Better World Books Style) -->
<div class="row g-3 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="value-prop-card">
            <div class="value-prop-icon">
                <i class="bi bi-book"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1">100% Buku Asli</h6>
                <p class="text-muted small mb-0">Langsung dari penerbit resmi tanpa khawatir barang bajakan.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="value-prop-card">
            <div class="value-prop-icon">
                <i class="bi bi-truck"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1">Bebas Ongkir</h6>
                <p class="text-muted small mb-0">Nikmati promo ongkos kirim ke seluruh pelosok Indonesia.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="value-prop-card">
            <div class="value-prop-icon">
                <i class="bi bi-credit-card-2-front"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1">Payment at Delivery</h6>
                <p class="text-muted small mb-0">Buku dikemas dan langsung diproses setelah konfirmasi transfer.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="value-prop-card">
            <div class="value-prop-icon">
                <i class="bi bi-headset"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1">Layanan Responsif</h6>
                <p class="text-muted small mb-0">Admin siap membantu kendala dan rekomendasi bacaan Anda.</p>
            </div>
        </div>
    </div>
</div>

<!-- Card Filter, Pencarian & Sortir -->
<div class="card border-0 shadow-sm rounded-4 mb-4" id="katalog">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('home') }}#katalog" class="row g-3 align-items-center">
            @if (!empty($categoryId))
                <input type="hidden" name="category" value="{{ $categoryId }}">
            @endif

            <div class="col-lg-6 col-md-12">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="q" class="form-control bg-light border-start-0" 
                           placeholder="Cari judul buku, penulis, atau penerbit..." 
                           value="{{ $search }}">
                    <button class="btn btn-primary px-4" type="submit">
                        Cari Buku
                    </button>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <select name="sort" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="latest" {{ $sortBy === 'latest' ? 'selected' : '' }}>Terbaru Ditambahkan</option>
                    <option value="price_low" {{ $sortBy === 'price_low' ? 'selected' : '' }}>Harga: Terendah ke Tertinggi</option>
                    <option value="price_high" {{ $sortBy === 'price_high' ? 'selected' : '' }}>Harga: Tertinggi ke Terendah</option>
                    <option value="title_asc" {{ $sortBy === 'title_asc' ? 'selected' : '' }}>Abjad: A ke Z</option>
                </select>
            </div>

            <div class="col-lg-3 col-md-6 text-lg-end">
                @if (!empty($search) || !empty($categoryId) || $sortBy !== 'latest')
                    <a href="{{ route('home') }}#katalog" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-x-circle me-1"></i> Reset Filter
                    </a>
                @endif
            </div>
        </form>

        <!-- Kategori Filter Chips -->
        <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
            <span class="small text-muted align-self-center me-1">Kategori:</span>
            <a href="{{ route('home', array_filter(['q' => $search ?? ''])) }}#katalog" 
               class="badge rounded-pill px-3 py-2 text-decoration-none {{ empty($categoryId ?? null) ? 'bg-primary text-white' : 'bg-light text-secondary border' }}">
                Semua
            </a>
            @foreach ($categories as $cat)
                <a href="{{ route('home', array_filter(['category' => $cat->id, 'q' => $search ?? ''])) }}#katalog" 
                   class="badge rounded-pill px-3 py-2 text-decoration-none {{ ($categoryId ?? null) == $cat->id ? 'bg-primary text-white' : 'bg-light text-secondary border' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>
</div>

<!-- Header Hasil Pencarian & Jumlah Data -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            @if (!empty($search ?? ''))
                Hasil Pencarian: "{{ $search }}"
            @elseif (!empty($categoryId ?? null))
                @php 
                    $currCat = $categories->firstWhere('id', $categoryId);
                @endphp
                Kategori: {{ $currCat ? $currCat->name : 'Kategori' }}
            @else
                Semua Koleksi Buku
            @endif
        </h4>
        <p class="text-muted small mb-0">Menampilkan {{ count($books) }} judul buku di BooksVy</p>
    </div>
</div>

<!-- Grid Katalog Buku -->
@if (empty($books) || count($books) === 0)
    <div class="card border-0 shadow-sm rounded-4 text-center py-5 my-4">
        <div class="card-body">
            <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
            <h5 class="fw-bold mt-3">Tidak Ada Buku yang Ditemukan</h5>
            <p class="text-muted small mb-3">Coba gunakan kata kunci pencarian yang lain atau reset filter kategori.</p>
            <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Pencarian
            </a>
        </div>
    </div>
@else
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
        @foreach ($books as $b)
            @php $isBookOutOfStock = ($b->stock <= 0); @endphp
            <div class="col" id="book-{{ $b->id }}">
                <div class="book-card h-100 {{ $isBookOutOfStock ? 'book-card-out-of-stock' : '' }}">
                    <div class="book-cover-wrapper">
                        {!! bookCoverHtml($b->cover_image, $b->title, 'card') !!}
                        
                        <span class="book-category-badge {{ $isBookOutOfStock ? 'bg-secondary text-white' : '' }}">
                            {{ $b->category->name ?? 'Umum' }}
                        </span>

                        <span class="book-stock-badge">
                            @if ($b->stock > 5)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i> Stok: {{ $b->stock }}
                                </span>
                            @elseif ($b->stock > 0)
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1">
                                    Sisa: {{ $b->stock }}
                                </span>
                            @else
                                <span class="badge bg-secondary text-white rounded-pill px-2 py-1 shadow-sm">
                                    <i class="bi bi-dash-circle me-1"></i> Stok Habis
                                </span>
                            @endif
                        </span>
                    </div>

                    <div class="book-card-body">
                        <div class="book-format-tag">
                            <i class="bi bi-book me-1"></i> BUKU FISIK &bull; PAPERBACK
                        </div>

                        <h6 class="book-title" title="{{ $b->title }}">
                            <a href="{{ route('books.show', $b->id) }}" class="text-decoration-none {{ $isBookOutOfStock ? 'text-secondary' : 'text-dark' }}">
                                {{ $b->title }}
                            </a>
                        </h6>
                        <div class="book-author">
                            <span class="text-muted">oleh</span> <span class="fw-medium {{ $isBookOutOfStock ? 'text-secondary' : 'text-dark' }}">{{ $b->author }}</span>
                        </div>

                        <div class="d-flex align-items-baseline justify-content-between mb-3 mt-auto">
                            <div class="book-price {{ $isBookOutOfStock ? 'text-muted' : '' }}">
                                {{ formatRupiah($b->price) }}
                            </div>
                            <small class="text-muted">
                                {{ $b->year ? 'Thn ' . $b->year : '' }}
                            </small>
                        </div>

                        <div class="d-grid gap-2">
                            <div class="d-flex gap-2">
                                <a href="{{ route('books.show', $b->id) }}" class="btn {{ $isBookOutOfStock ? 'btn-outline-secondary' : 'btn-outline-primary' }} btn-sm flex-grow-1 rounded-pill fw-semibold">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>

                                @if ($b->stock > 0)
                                    @auth
                                        <button type="button" 
                                                class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm btn-submit-cart" 
                                                onclick="quickAddToCart({{ $b->id }}, this)" 
                                                title="Tambah ke Keranjang">
                                            <i class="bi bi-cart-plus-fill me-1"></i> + Keranjang
                                        </button>
                                    @else
                                        <a href="{{ route('login') }}" 
                                           class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm" 
                                           title="Silakan masuk untuk menambahkan ke keranjang">
                                            <i class="bi bi-cart-plus-fill me-1"></i> + Keranjang
                                        </a>
                                    @endauth
                                @else
                                    <button class="btn btn-secondary btn-sm rounded-pill px-3 opacity-75" disabled title="Stok Habis">
                                        <i class="bi bi-dash-circle me-1"></i> Habis
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<!-- Section Mengenal BooksVy (Tentang Kami) -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5 mt-5 bg-white">
    <div class="row g-0 align-items-center">
        <div class="col-lg-6 p-4 p-md-5">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold mb-3">
                <i class="bi bi-book-half me-1"></i> Mengenal BooksVy
            </span>
            <h3 class="fw-bold mb-3 text-dark">Toko Buku Digital Terpercaya untuk Pecinta Literasi</h3>
            <p class="text-secondary small mb-4" style="line-height: 1.8;">
                BooksVy lahir dari kecintaan mendalam pada dunia literasi. Kami menghadirkan koleksi buku fisik original berkualitas dari berbagai penerbit terpercaya dengan metode transaksi <em>Payment at Delivery</em> yang aman dan praktis.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('about') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                    <i class="bi bi-arrow-right-circle me-1"></i> Baca Cerita Tentang Kami
                </a>
            </div>
        </div>
        <div class="col-lg-6 p-4 p-md-5 bg-light d-flex flex-column justify-content-center border-start">
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="p-3 bg-white rounded-3 shadow-sm border h-100">
                        <i class="bi bi-patch-check-fill text-success fs-3 mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">100% Buku Asli</h6>
                        <small class="text-muted">Semua judul langsung didistribusikan dari penerbit resmi.</small>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 bg-white rounded-3 shadow-sm border h-100">
                        <i class="bi bi-shield-check text-primary fs-3 mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">Payment at Delivery</h6>
                        <small class="text-muted">Konfirmasi instan dan pengiriman yang terlindungi.</small>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 bg-white rounded-3 shadow-sm border h-100">
                        <i class="bi bi-truck text-warning fs-3 mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">Pengiriman Cepat</h6>
                        <small class="text-muted">Jangkauan logistik terpercaya ke seluruh Indonesia.</small>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 bg-white rounded-3 shadow-sm border h-100">
                        <i class="bi bi-headset text-info fs-3 mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">Bantuan Admin</h6>
                        <small class="text-muted">Dukungan staf toko yang ramah dan siap membantu.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
