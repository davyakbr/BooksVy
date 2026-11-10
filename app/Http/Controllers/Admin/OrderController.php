<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

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

        $order->update([
            'payment_status' => $validated['payment_status'],
            'order_status'   => $validated['order_status'],
        ]);

        return back()->with('success', 'Status pesanan #' . $order->order_number . ' berhasil diperbarui.');
    }
}
