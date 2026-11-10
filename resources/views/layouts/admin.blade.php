<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - BooksVy')</title>
    
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
<body class="bg-light">

@php
    $unreadCount = \App\Models\ContactMessage::where('status', 'Menunggu Respon')->count();
@endphp

<!-- Mobile Sidebar Backdrop Overlay -->
<div class="admin-sidebar-overlay" id="sidebarOverlay" onclick="toggleAdminSidebar()"></div>

<div class="admin-layout-wrapper">
    <!-- Admin Sidebar (Samping) -->
    <aside class="admin-sidebar" id="adminSidebar">
        <!-- Brand Header: Tanpa Logo di Button-nya (Murni Teks Bersih & Elegan) -->
        <div class="admin-sidebar-brand">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand-button-text">
                <div class="brand-text-title">
                    <span>Books<span class="vy">Vy</span></span>
                    <span class="brand-text-badge">ADMIN</span>
                </div>
                <span class="brand-text-sub">Control Center</span>
            </a>
        </div>

        <!-- Tombol Menu di Samping (Pindah dari Header) -->
        <div class="admin-sidebar-menu">
            <div class="admin-menu-label">NAVIGASI UTAMA</div>

            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-btn {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.books.index') }}" class="admin-sidebar-btn {{ request()->routeIs('admin.books.*') && !request()->routeIs('admin.books.create') ? 'active' : '' }}">
                <span>Data Buku</span>
            </a>

            <a href="{{ route('admin.categories.index') }}" class="admin-sidebar-btn {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <span>Kategori</span>
            </a>

            <a href="{{ route('admin.orders.index') }}" class="admin-sidebar-btn {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <span>Pesanan</span>
            </a>

            <a href="{{ route('admin.users.index') }}" class="admin-sidebar-btn {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <span>Pengguna</span>
            </a>

            <a href="{{ route('admin.messages.index') }}" class="admin-sidebar-btn {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                <span>Pesan Masuk</span>
                @if(($unreadCount ?? 0) > 0)
                    <span class="badge bg-danger rounded-pill px-2 py-1 ms-auto" style="font-size: 0.7rem;">{{ $unreadCount }} baru</span>
                @endif
            </a>

            <a href="{{ route('admin.banners.index') }}" class="admin-sidebar-btn {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                <span>Banner Promosi</span>
            </a>

            <div class="admin-menu-label mt-4">AKSI CEPAT</div>

            <!-- Tombol Tambah Buku di Samping -->
            <a href="{{ route('admin.books.create') }}" class="admin-sidebar-action-btn {{ request()->routeIs('admin.books.create') ? 'opacity-90' : '' }}">
                + Tambah Buku
            </a>

            <!-- Tombol Buka Halaman Toko di Bawah Tambah Buku -->
            <a href="{{ route('home') }}" target="_blank" class="admin-sidebar-store-btn mt-2">
                <span>Halaman Toko</span>
            </a>
        </div>

        <!-- Sidebar Footer -->
        <div class="admin-sidebar-footer">
            <div class="admin-user-profile-badge">
                <div class="admin-user-info">
                    <span class="admin-user-name text-truncate" style="max-width: 140px;">{{ Auth::user()->name ?? 'Administrator' }}</span>
                    <span class="admin-user-role">Sistem Online</span>
                </div>
                <span class="admin-status-dot"></span>
            </div>
            <form method="POST" action="{{ route('logout') }}" id="adminLogoutForm" class="form-confirm-logout">
                @csrf
                <button type="submit" class="admin-sidebar-btn-logout">
                    Keluar (Logout)
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main-container">
        <!-- Top Minimal Header Bar -->
        <header class="admin-top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="admin-sidebar-toggle-btn" onclick="toggleAdminSidebar()" aria-label="Toggle Sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h5 class="admin-header-title">Panel Administrasi BooksVy</h5>
                    <div class="admin-header-date">{{ date('l, d F Y') }}</div>
                </div>
            </div>

            <div class="admin-header-actions">
                <a href="{{ route('home') }}" target="_blank" class="admin-btn-preview-store d-none d-sm-inline-flex align-items-center gap-1">
                    Buka Toko <i class="bi bi-arrow-up-right"></i>
                </a>
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small">
                    Admin: <strong>{{ Auth::user()->name ?? 'Admin' }}</strong>
                </span>
            </div>
        </header>

        <!-- Main Content Body -->
        <main class="flex-grow-1 p-4">
            <div class="container-fluid px-0">
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
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 (Modern Pop-up Confirmation) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/main.js') }}?v={{ time() }}"></script>
<script>
function toggleAdminSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar && overlay) {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }
}
</script>
@yield('scripts')
</body>
</html>
