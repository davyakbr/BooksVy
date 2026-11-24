<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MessageController as AdminMessageController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - BooksVy Toko Buku Online Modern
|--------------------------------------------------------------------------
| File ini adalah daftar peta navigasi seluruh URL di web BooksVy.
| Dibagi menjadi 5 kelompok utama:
| 1. Publik (Bisa diakses siapa saja tanpa login)
| 2. Keranjang Belanja (Bisa diakses publik / user)
| 3. Autentikasi (Hanya untuk tamu/guest yang belum login)
| 4. Pelanggan (Wajib login sebagai user)
| 5. Administrator (/admin/* - Terproteksi middleware 'admin')
|--------------------------------------------------------------------------
*/

// =========================================================================
// 1. RUTE PUBLIK (Dapat Diakses Siapa Saja)
// =========================================================================
Route::get('/', [HomeController::class, 'index'])->name('home');                          // Beranda & Katalog Buku
Route::get('/books/{id}', [BookController::class, 'show'])->name('books.show');            // Detail Spesifik Buku
Route::get('/about', [AboutController::class, 'index'])->name('about');                    // Halaman Tentang Kami
Route::get('/contact', [ContactController::class, 'index'])->name('contact');              // Formulir Kontak Bantuan
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');       // Kirim Pesan Kontak

// =========================================================================
// 2. RUTE KERANJANG BELANJA (CART)
// =========================================================================
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');                 // Halaman Keranjang Belanja
Route::post('/cart/quick-add', [CartController::class, 'quickAdd'])->name('cart.quick-add');// Tambah Cepat dari Katalog
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');                // Tambah dari Detail Buku
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');       // Ubah Kuantitas (+ / -)
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');       // Hapus 1 Item dari Keranjang
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');          // Kosongkan Seluruh Keranjang

// =========================================================================
// 3. RUTE AUTENTIKASI (Hanya untuk yang BELUM Login)
// =========================================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');             // Halaman Form Login
    Route::post('/login', [AuthController::class, 'login']);                               // Proses Verifikasi Login
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');    // Halaman Form Daftar
    Route::post('/register', [AuthController::class, 'register']);                         // Proses Pendaftaran Akun
});

// Logout: Dapat diakses via GET atau POST oleh user yang sedang login
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// =========================================================================
// 4. RUTE PELANGGAN (Wajib Login & Bukan Administrator)
// =========================================================================
Route::middleware(['auth', 'customer'])->group(function () {
    // Checkout & Pembayaran
    Route::match(['get', 'post'], '/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');

    // Riwayat Pesanan Saya
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{id}/success', [OrderController::class, 'success'])->name('orders.success');

    // Pengaturan Akun & Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// =========================================================================
// 5. RUTE PANEL ADMINISTRATOR (/admin/*)
// Terproteksi Middleware 'admin' (Hanya akun dengan role == 'admin')
// =========================================================================
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    // Ringkasan Statistik
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Manajemen Master Data Buku (CRUD Buku)
    Route::resource('books', AdminBookController::class);

    // Manajemen Kategori Buku
    Route::resource('categories', AdminCategoryController::class)->except(['create', 'show', 'edit']);

    // Manajemen Pesanan Masuk & Update Status Pengiriman
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');

    // Manajemen Pesan Masuk dari Pelanggan
    Route::resource('messages', AdminMessageController::class)->only(['index', 'show', 'destroy']);
    Route::post('/messages/{id}/reply', [AdminMessageController::class, 'reply'])->name('messages.reply');

    // Manajemen Daftar Pengguna Terdaftar
    Route::resource('users', AdminUserController::class)->only(['index', 'destroy']);
});
