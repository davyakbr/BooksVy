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
| Web Routes - BooksVy Toko Buku Online
|--------------------------------------------------------------------------
| Seluruh alur URL aplikasi diatur di file ini.
*/

// ==========================================
// 1. RUTE PUBLIK (Dapat diakses siapa saja)
// ==========================================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/books/{id}', [BookController::class, 'show'])->name('books.show');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// ==========================================
// 2. RUTE KERANJANG BELANJA (CART)
// ==========================================
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/quick-add', [CartController::class, 'quickAdd'])->name('cart.quick-add');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// ==========================================
// 3. RUTE AUTENTIKASI (LOGIN & REGISTER)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ==========================================
// 4. RUTE PELANGGAN (Wajib Login sebagai User)
// ==========================================
Route::middleware(['auth', 'customer'])->group(function () {
    Route::match(['get', 'post'], '/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{id}/success', [OrderController::class, 'success'])->name('orders.success');

    // Edit Profil Pengguna
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// ==========================================
// 5. RUTE PANEL ADMINISTRATOR (/admin/*)
// Diproteksi oleh Middleware 'admin' (Hanya role admin)
// ==========================================
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Manajemen Buku (CRUD)
    Route::resource('books', AdminBookController::class);

    // Manajemen Kategori
    Route::resource('categories', AdminCategoryController::class)->except(['create', 'show', 'edit']);

    // Manajemen Pesanan & Pembaruan Status
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');

    // Manajemen Pesan Kontak / Tiket Bantuan
    Route::resource('messages', AdminMessageController::class)->only(['index', 'show', 'destroy']);
    Route::post('/messages/{id}/reply', [AdminMessageController::class, 'reply'])->name('messages.reply');

    // Manajemen Pengguna
    Route::resource('users', AdminUserController::class)->only(['index', 'destroy']);
});
