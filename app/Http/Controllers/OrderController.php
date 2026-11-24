<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Menampilkan daftar seluruh riwayat pesanan milik pelanggan yang sedang login.
     */
    public function index()
    {
        // Ambil hanya pesanan milik user yang sedang aktif (Auth::id())
        // Menggunakan eager loading with('items') agar query database hemat dan cepat
        $orders = Order::where('user_id', Auth::id())
            ->with('items')
            ->orderBy('id', 'desc')
            ->get();

        return view('orders.index', compact('orders'));
    }

    /**
     * Menampilkan rincian spesifik satu pesanan pelanggan.
     */
    public function show($id)
    {
        // Pastikan hanya pemilik pesanan yang bisa membuka nota pesanan ini (where user_id = Auth::id())
        $order = Order::where('user_id', Auth::id())
            ->with(['items.book'])
            ->findOrFail($id);

        return view('orders.show', compact('order'));
    }

    /**
     * Halaman ucapan sukses / konfirmasi sesaat setelah checkout berhasil dibuat.
     */
    public function success($id)
    {
        $order = Order::where('user_id', Auth::id())
            ->with('items.book')
            ->findOrFail($id);

        return view('orders.success', compact('order'));
    }
}
