<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = trim($request->get('q', ''));

        $query = Order::with('user');

        if (!empty($status)) {
            $query->where('order_status', $status);
        }

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

    public function show($id)
    {
        $order = Order::with(['user', 'items.book'])->findOrFail($id);
        $isCompleted = ($order->order_status === 'Selesai');

        return view('admin.orders.show', compact('order', 'isCompleted'));
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Jika status pengiriman sudah selesai, kunci permanen dan tolak perubahan
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

        // Fitur Sinkronisasi Otomatis Antar-Status (Smart Auto-Sync):
        // 1. Jika status pesanan dibatalkan atau status pembayaran dibatalkan, keduanya disinkronkan ke "Dibatalkan"
        if ($newOrderStatus === 'Dibatalkan' || $newPaymentStatus === 'Dibatalkan') {
            $newOrderStatus = 'Dibatalkan';
            $newPaymentStatus = 'Dibatalkan';
        }
        // 2. Jika pengiriman sudah berstatus "Selesai", pastikan status pembayaran adalah "Lunas"
        elseif ($newOrderStatus === 'Selesai') {
            $newPaymentStatus = 'Lunas';
        }

        try {
            DB::transaction(function () use ($order, $newPaymentStatus, $oldOrderStatus, $newOrderStatus) {
                // 1. Jika pesanan diubah ke "Dibatalkan" dari status aktif, kembalikan stok buku ke katalog
                if ($newOrderStatus === 'Dibatalkan' && $oldOrderStatus !== 'Dibatalkan') {
                    foreach ($order->items as $item) {
                        if ($item->book_id) {
                            Book::where('id', $item->book_id)->increment('stock', $item->quantity);
                        }
                    }
                }

                // 2. Jika pesanan sebelumnya "Dibatalkan" lalu diaktifkan kembali, kurangi kembali stoknya jika cukup
                if ($oldOrderStatus === 'Dibatalkan' && $newOrderStatus !== 'Dibatalkan') {
                    foreach ($order->items as $item) {
                        if ($item->book_id) {
                            $book = Book::where('id', $item->book_id)->lockForUpdate()->first();
                            if ($book) {
                                if ($book->stock < $item->quantity) {
                                    throw new \Exception("Gagal mengaktifkan kembali pesanan! Stok buku '{$book->title}' tidak mencukupi (Sisa stok: {$book->stock}, Dibutuhkan: {$item->quantity}).");
                                }
                                $book->decrement('stock', $item->quantity);
                            }
                        }
                    }
                }

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
