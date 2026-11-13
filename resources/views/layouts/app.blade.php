<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BooksVy - Toko Buku Online Modern')</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- BooksVy Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ time() }}">
    <script>
        window.BASE_URL = '{{ url('/') }}';
        window.CSRF_TOKEN = '{{ csrf_token() }}';
    </script>
</head>
<body>

@php
    $cart = session('cart', []);
    $navCartCount = is_array($cart) ? array_sum(array_column($cart, 'qty')) : 0;
@endphp

<!-- Top Announcement Bar (Better World Books Style) -->
<div class="top-announcement-bar d-none d-md-block">
    <div class="container-fluid app-fluid-container d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-truck me-1 text-warning"></i>
            <strong>Gratis Ongkir</strong> Seluruh Indonesia • 100% Buku Asli Penerbit Resmi
        </div>
        <div class="d-flex align-items-center gap-3 small">
            <span><i class="bi bi-shield-check text-warning me-1"></i> Payment at Delivery Instan</span>
            <span class="text-white-50">|</span>
            <a href="{{ route('contact') }}"><i class="bi bi-chat-dots me-1"></i> Bantuan Admin</a>
        </div>
    </div>
</div>

<!-- Navbar Area Pelanggan (Frontend) -->
<nav class="navbar navbar-expand-lg app-navbar sticky-top">
    <div class="container-fluid app-fluid-container">
        <!-- Brand BooksVy: Murni Teks Bersih Tanpa Logo / Ikon / Logo-Box -->
        <a class="navbar-brand d-inline-flex flex-column text-decoration-none me-4 py-0" href="{{ route('home') }}">
            <span class="brand-title">Books<span>Vy</span></span>
            <span class="brand-tagline">Toko Buku Modern</span>
        </a>

        <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#userNav" aria-controls="userNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="userNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link nav-link-custom {{ request()->routeIs('home') || request()->routeIs('books.*') ? 'active' : '' }}" href="{{ route('home') }}">
                        <i class="bi bi-grid"></i> Katalog Buku
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom {{ request()->routeIs('contact*') ? 'active' : '' }}" href="{{ route('contact') }}">
                        <i class="bi bi-envelope"></i> Hubungi Admin
                    </a>
                </li>
                @auth
                    @if(!Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                                <i class="bi bi-receipt"></i> Pesanan Saya
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>

            <ul class="navbar-nav ms-auto align-items-lg-center gap-2 mb-2 mb-lg-0">
                <!-- Tombol Keranjang Belanja (Disembunyikan pada halaman Login & Registrasi) -->
                @if(!request()->routeIs('login') && !request()->routeIs('register'))
                    <li class="nav-item">
                        <a class="btn nav-cart-btn rounded-pill d-inline-flex align-items-center gap-2 position-relative shadow-sm" href="{{ route('cart.index') }}">
                            <i class="bi bi-cart3 fs-5"></i>
                            <span>Keranjang</span>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle nav-cart-badge {{ $navCartCount > 0 ? '' : 'd-none' }}" style="width: 11px; height: 11px; padding: 0 !important; box-shadow: 0 0 6px rgba(220,53,69,0.85);"></span>
                        </a>
                    </li>
                @endif

                @auth
                    @if(Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="btn btn-light btn-sm rounded-pill px-3" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2 me-1"></i> Panel Admin
                            </a>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-user-pill dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5"></i>
                            <span>{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" aria-labelledby="userMenu">
                            <li class="dropdown-header">
                                <span class="d-block text-muted small">Login Sebagai:</span>
                                <strong class="text-dark">{{ Auth::user()->name }}</strong>
                                <small class="d-block text-muted">{{ Auth::user()->email }}</small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            @if(!Auth::user()->isAdmin())
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('orders.index') }}">
                                        <i class="bi bi-bag-check text-primary me-2"></i> Riwayat Pesanan
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('about') }}">
                                    <i class="bi bi-info-circle text-secondary me-2"></i> Tentang BooksVy
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="form-confirm-logout">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger border-0 bg-transparent">
                                        <i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="btn btn-nav-login rounded-pill px-3 py-1 btn-sm fw-medium d-inline-flex align-items-center" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-nav-register rounded-pill px-3 py-1 btn-sm fw-semibold shadow-sm d-inline-flex align-items-center" href="{{ route('register') }}">
                            <i class="bi bi-person-plus-fill me-1"></i> Daftar
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="main-content py-4">
    <div class="container-fluid app-fluid-container">
        <!-- Flash Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div class="flex-grow-1">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('danger'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div class="flex-grow-1">{{ session('danger') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-circle-fill fs-5 me-2"></i>
                <div class="flex-grow-1">{{ session('warning') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                <div class="flex-grow-1">{{ session('info') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </div>
</main>

<footer class="app-footer">
    <!-- Literary Mission Banner -->
    <div class="footer-mission-box mx-auto mb-4" style="max-width:820px;">
        <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0 mt-1">
                <i class="bi bi-journal-bookmark-fill fs-2 text-warning"></i>
            </div>
            <div>
                <h6 class="text-white fw-bold mb-1">Misi BooksVy: Literasi untuk Semua</h6>
                <p class="text-secondary small mb-0">
                    Kami percaya setiap buku yang kamu beli berkontribusi pada gerakan literasi Indonesia.
                    BooksVy hadir membawa buku asli berkualitas langsung ke tanganmu, dengan harga terjangkau
                    dan layanan yang hangat — karena membaca adalah hak semua orang.
                </p>
            </div>
        </div>
    </div>

    <div class="container-fluid app-fluid-container pb-4">
        <!-- Trust Badges Row -->
        <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
            <span class="badge rounded-pill px-3 py-2" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#c9ddd4; font-weight:500; font-size:.78rem;">
                <i class="bi bi-patch-check-fill text-warning me-1"></i> 100% Buku Asli
            </span>
            <span class="badge rounded-pill px-3 py-2" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#c9ddd4; font-weight:500; font-size:.78rem;">
                <i class="bi bi-truck text-warning me-1"></i> Bebas Ongkos Kirim
            </span>
            <span class="badge rounded-pill px-3 py-2" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#c9ddd4; font-weight:500; font-size:.78rem;">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Checkout Instan
            </span>
            <span class="badge rounded-pill px-3 py-2" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#c9ddd4; font-weight:500; font-size:.78rem;">
                <i class="bi bi-shield-check text-warning me-1"></i> Garansi Kualitas 100%
            </span>
            <span class="badge rounded-pill px-3 py-2" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#c9ddd4; font-weight:500; font-size:.78rem;">
                <i class="bi bi-headset text-warning me-1"></i> Admin Responsif
            </span>
        </div>

        <div class="row g-4">
            <!-- Brand & Info -->
            <div class="col-lg-4 col-md-6">
                <div class="mb-3">
                    <span class="fs-4 fw-bold text-white">Books<span style="color:#f59e0b;">Vy</span></span>
                </div>
                <p class="small mb-3" style="color:#93b5a4; line-height:1.65;">
                    Platform toko buku online modern dengan koleksi buku pilihan dari berbagai kategori —
                    pemrograman, sastra, bisnis, hingga pengembangan diri.
                </p>
                <div class="d-flex gap-2">
                    <a href="{{ route('contact') }}" class="btn btn-sm rounded-pill fw-semibold px-3"
                       style="background:#f59e0b; color:#11221b; border:none;">
                        <i class="bi bi-chat-dots-fill me-1"></i> Hubungi Kami
                    </a>
                </div>
            </div>

            <!-- Navigasi Cepat -->
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="fw-bold mb-3" style="color:#e2f0eb; letter-spacing:.4px;">Navigasi</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="{{ route('home') }}"><i class="bi bi-chevron-right me-1" style="color:#f59e0b;"></i> Beranda</a></li>
                    <li><a href="{{ route('home') }}#katalog"><i class="bi bi-chevron-right me-1" style="color:#f59e0b;"></i> Katalog Buku</a></li>
                    <li><a href="{{ route('about') }}"><i class="bi bi-chevron-right me-1" style="color:#f59e0b;"></i> Tentang Kami</a></li>
                    <li><a href="{{ route('contact') }}"><i class="bi bi-chevron-right me-1" style="color:#f59e0b;"></i> Hubungi Admin</a></li>
                    <li><a href="{{ route('cart.index') }}"><i class="bi bi-chevron-right me-1" style="color:#f59e0b;"></i> Keranjang</a></li>
                </ul>
            </div>

            <!-- Akun & Bantuan -->
            <div class="col-lg-3 col-md-6 col-6">
                <h6 class="fw-bold mb-3" style="color:#e2f0eb; letter-spacing:.4px;">Akun & Layanan</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    @auth
                        @if(!Auth::user()->isAdmin())
                            <li><a href="{{ route('orders.index') }}"><i class="bi bi-chevron-right me-1" style="color:#f59e0b;"></i> Status Pesanan</a></li>
                        @endif
                        <li><a href="{{ route('contact') }}"><i class="bi bi-chevron-right me-1" style="color:#f59e0b;"></i> Pesan ke Admin</a></li>
                        <li><a href="{{ route('logout') }}" style="color:#f87171;"><i class="bi bi-box-arrow-right me-1"></i> Keluar</a></li>
                    @else
                        <li><a href="{{ route('login') }}"><i class="bi bi-chevron-right me-1" style="color:#f59e0b;"></i> Masuk Akun</a></li>
                        <li><a href="{{ route('register') }}"><i class="bi bi-chevron-right me-1" style="color:#f59e0b;"></i> Daftar Baru</a></li>
                    @endauth
                    <li><a href="{{ route('about') }}#faq"><i class="bi bi-chevron-right me-1" style="color:#f59e0b;"></i> Panduan Pembayaran</a></li>
                </ul>
            </div>

            <!-- Kontak Toko -->
            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold mb-3" style="color:#e2f0eb; letter-spacing:.4px;">Kontak & Lokasi</h6>
                <div class="d-flex flex-column gap-2">
                    <p class="small mb-0" style="color:#93b5a4;">
                        <i class="bi bi-geo-alt-fill me-2" style="color:#f87171;"></i>
                        Jl. Thamrin No. 88, Menteng,<br>
                        <span class="ms-4">Jakarta Pusat 10350</span>
                    </p>
                    <p class="small mb-0" style="color:#93b5a4;">
                        <i class="bi bi-envelope-fill me-2" style="color:#60a5fa;"></i>
                        support@booksvy.com
                    </p>
                    <p class="small mb-0" style="color:#93b5a4;">
                        <i class="bi bi-whatsapp me-2" style="color:#4ade80;"></i>
                        +62 857-8188-0279
                    </p>
                    <p class="small mb-0 mt-1" style="color:#93b5a4;">
                        <i class="bi bi-clock me-2" style="color:#f59e0b;"></i>
                        Senin – Jumat, 08.00 – 17.00 WIB
                    </p>
                </div>
            </div>
        </div>

        <hr style="border-color:rgba(255,255,255,0.1); margin: 2rem 0 1.25rem;">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small" style="color:#6b8f7c;">
            <div>
                &copy; {{ date('Y') }} <strong style="color:#a8c5b5;">BooksVy</strong>.
                Semua hak cipta dilindungi.
            </div>
            <div class="mt-2 mt-md-0 text-center">
                <strong style="color:#a8c5b5;">BooksVy</strong> &mdash; Platform Toko Buku Online Modern & Terpercaya (Laravel Edition)
            </div>
        </div>
    </div>
</footer>

<!-- Toast Container untuk Notifikasi Tambah Keranjang -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="cartToast" class="toast align-items-center text-white border-0 shadow-lg toast-cart-smooth" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex p-1">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-warning fs-5"></i>
                <div id="cartToastMsg" class="small fw-medium">Buku berhasil ditambahkan ke keranjang!</div>
            </div>
            <a href="{{ route('cart.index') }}" class="btn btn-warning btn-sm align-self-center me-2 text-dark fw-bold rounded-pill px-3 py-1 text-nowrap shadow-sm">
                Lihat <i class="bi bi-arrow-right ms-1"></i>
            </a>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 (Modern Pop-up Confirmation) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- BooksVy Main JS -->
<script src="{{ asset('assets/js/main.js') }}?v={{ time() }}"></script>
@yield('scripts')
</body>
</html>
