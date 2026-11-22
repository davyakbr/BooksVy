<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Menampilkan daftar pesanan masuk dengan filter status dan pencarian invoice/nama/telepon.
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = trim($request->get('q', ''));

        $query = Order::with('user');

        // Filter Status Pesanan
        if (!empty($status)) {
            $query->where('order_status', $status);
        }

        // Pencarian Kata Kunci
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('recipient_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('id', 'desc')->paginate(15);

        return view('admin.orders.index', compact('orders', 'status', 'search'));
    }

    /**
     * Menampilkan halaman detail spesifik pesanan.
     */
    public function show($id)
    {
        $order = Order::with(['user', 'items.book'])->findOrFail($id);
        $isCompleted = ($order->order_status === 'Selesai');

        return view('admin.orders.show', compact('order', 'isCompleted'));
    }

    /**
     * Memperbarui status pesanan & status pembayaran secara tersinkronisasi.
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Jika pesanan sudah Selesai, kunci status secara permanen
        if ($order->order_status === 'Selesai') {
            return back()->with('warning', 'Pesanan ini sudah berstatus "Selesai" dan tidak dapat diubah lagi.');
        }

        $validated = $request->validate([
            'payment_status' => 'required|in:Lunas,Dibatalkan',
            'order_status'   => 'required|in:Menunggu Konfirmasi,Sedang Diproses,Sedang Dikirim,Selesai,Dibatalkan',
        ]);

        $oldOrderStatus = $order->order_status;
        $newOrderStatus = $validated['order_status'];
        $newPaymentStatus = $validated['payment_status'];

        // Fitur Smart Auto-Sync:
        // 1. Jika pesanan dibatalkan (atau pembayaran dibatalkan), keduanya menjadi "Dibatalkan"
        if ($newOrderStatus === 'Dibatalkan' || $newPaymentStatus === 'Dibatalkan') {
            $newOrderStatus = 'Dibatalkan';
            $newPaymentStatus = 'Dibatalkan';
        }
        // 2. Jika pengiriman sudah "Selesai", status pembayaran dipastikan "Lunas"
        elseif ($newOrderStatus === 'Selesai') {
            $newPaymentStatus = 'Lunas';
        }

        try {
            // Gunakan Transaction untuk menjamin perubahan status & manajemen stok berjalan aman
            DB::transaction(function () use ($order, $newPaymentStatus, $oldOrderStatus, $newOrderStatus) {
                
                // Kasus A: Jika pesanan dibatalkan, kembalikan stok buku ke katalog
                if ($newOrderStatus === 'Dibatalkan' && $oldOrderStatus !== 'Dibatalkan') {
                    foreach ($order->items as $item) {
                        if ($item->book_id) {
                            Book::where('id', $item->book_id)->increment('stock', $item->quantity);
                        }
                    }
                }

                // Kasus B: Jika pesanan sebelumnya batal lalu diaktifkan kembali, kurangi kembali stoknya jika cukup
                if ($oldOrderStatus === 'Dibatalkan' && $newOrderStatus !== 'Dibatalkan') {
                    foreach ($order->items as $item) {
                        if ($item->book_id) {
                            $book = Book::where('id', $item->book_id)->lockForUpdate()->first();
                            if ($book) {
                                if ($book->stock < $item->quantity) {
                                    throw new \Exception("Stok buku '{$book->title}' tidak mencukupi untuk mengaktifkan kembali pesanan ini.");
                                }
                                $book->decrement('stock', $item->quantity);
                            }
                        }
                    }
                }

                // Simpan pembaruan status ke database
                $order->update([
                    'payment_status' => $newPaymentStatus,
                    'order_status'   => $newOrderStatus,
                ]);
            });

            return back()->with('success', 'Status pesanan #' . $order->order_number . ' berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('danger', $e->getMessage());
        }
    }
}
