<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $rawCart = session()->get('cart', []);

        if (empty($rawCart)) {
            return redirect()->route('home')->with('warning', 'Keranjang belanja Anda kosong. Silakan pilih buku terlebih dahulu.');
        }

        // Tangkap item yang dipilih dari checkbox di cart
        if ($request->isMethod('post') && $request->has('selected_items') && is_array($request->selected_items)) {
            session(['checkout_selected' => array_map('intval', $request->selected_items)]);
        }

        $selectedIds = session('checkout_selected', []);
        $cartItems = [];

        if (!empty($selectedIds)) {
            foreach ($selectedIds as $sId) {
                if (isset($rawCart[$sId])) {
                    $cartItems[$sId] = $rawCart[$sId];
                }
            }
        }

        // Fallback jika tidak ada pilihan spesifik: ambil item yang stoknya > 0
        if (empty($cartItems)) {
            foreach ($rawCart as $bId => $item) {
                $book = Book::find($bId);
                if ($book && $book->stock > 0) {
                    $cartItems[$bId] = $item;
                }
            }
        }

        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('danger', 'Tidak ada buku yang dipilih atau produk yang dipilih stoknya telah habis. Silakan pilih buku yang tersedia.');
        }

        // Validasi stok terkini HANYA untuk buku yang dipilih
        foreach ($cartItems as $bId => $item) {
            $book = Book::find($bId);
            $curStock = $book ? $book->stock : 0;

            if ($curStock <= 0) {
                return redirect()->route('cart.index')->with('danger', 'Maaf, buku "' . $item['title'] . '" saat ini stoknya telah habis. Silakan pilih buku lain atau hapus dari keranjang.');
            } elseif ($item['qty'] > $curStock) {
                return redirect()->route('cart.index')->with('warning', 'Jumlah pesanan untuk buku "' . $item['title'] . '" melebihi stok yang tersedia (' . $curStock . ' unit).');
            }
        }

        $totalPrice = 0;
        $totalCount = 0;
        foreach ($cartItems as $item) {
            $totalPrice += ($item['price'] * $item['qty']);
            $totalCount += $item['qty'];
        }

        $user = Auth::user();

        return view('checkout.index', compact('cartItems', 'totalPrice', 'totalCount', 'user'));
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'recipient_name'   => 'required|string|max:100',
            'recipient_phone'  => 'required|string|max:25',
            'shipping_address' => 'required|string',
            'payment_channel'  => 'required|string',
            'notes'            => 'nullable|string',
        ], [
            'recipient_name.required'   => 'Nama penerima wajib diisi.',
            'recipient_phone.required'  => 'Nomor telepon/WhatsApp penerima wajib diisi.',
            'shipping_address.required' => 'Alamat pengiriman lengkap wajib diisi.',
        ]);

        $rawCart = session()->get('cart', []);
        $selectedIds = session('checkout_selected', []);
        $cartItems = [];

        if (!empty($selectedIds)) {
            foreach ($selectedIds as $sId) {
                if (isset($rawCart[$sId])) {
                    $cartItems[$sId] = $rawCart[$sId];
                }
            }
        }

        if (empty($cartItems)) {
            foreach ($rawCart as $bId => $item) {
                $book = Book::find($bId);
                if ($book && $book->stock > 0) {
                    $cartItems[$bId] = $item;
                }
            }
        }

        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('danger', 'Tidak ada item yang dapat diproses untuk checkout.');
        }

        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += ($item['price'] * $item['qty']);
        }

        try {
            $order = DB::transaction(function () use ($validated, $cartItems, $totalPrice) {
                // Periksa stok dengan lock FOR UPDATE
                foreach ($cartItems as $bookId => $item) {
                    $book = Book::where('id', $bookId)->lockForUpdate()->first();
                    if (!$book || $book->stock < $item['qty']) {
                        $avail = $book ? $book->stock : 0;
                        throw new \Exception("Stok untuk buku '{$item['title']}' tidak mencukupi (Tersedia: {$avail}, Diminta: {$item['qty']}).");
                    }
                }

                $orderNumber = 'BVY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
                $paymentDesc = 'Payment at Delivery (' . $validated['payment_channel'] . ')';

                $order = Order::create([
                    'order_number'     => $orderNumber,
                    'user_id'          => Auth::id(),
                    'recipient_name'   => $validated['recipient_name'],
                    'recipient_phone'  => $validated['recipient_phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'payment_method'   => $paymentDesc,
                    'payment_status'   => 'Lunas',
                    'order_status'     => 'Sedang Diproses',
                    'total_amount'     => $totalPrice,
                    'notes'            => $validated['notes'] ?? null,
                    'created_at'       => now(),
                ]);

                foreach ($cartItems as $bookId => $item) {
                    $subtotal = $item['price'] * $item['qty'];

                    OrderItem::create([
                        'order_id'   => $order->id,
                        'book_id'    => $bookId,
                        'book_title' => $item['title'],
                        'price'      => $item['price'],
                        'quantity'   => $item['qty'],
                        'subtotal'   => $subtotal,
                    ]);

                    Book::where('id', $bookId)->decrement('stock', $item['qty']);
                }

                // Hapus HANYA item yang di-checkout dari session cart
                $currentCart = session()->get('cart', []);
                foreach ($cartItems as $bId => $item) {
                    unset($currentCart[$bId]);
                }
                session()->put('cart', $currentCart);
                session()->forget('checkout_selected');

                if (Auth::check()) {
                    \App\Models\UserCart::updateOrCreate(
                        ['user_id' => Auth::id()],
                        ['cart_data' => $currentCart]
                    );
                }

                return $order;
            });

            return redirect()->route('orders.success', $order->id)
                ->with('success', 'Pembayaran berhasil! Pesanan #' . $order->order_number . ' telah berstatus Lunas dan segera kami proses.');

        } catch (\Exception $e) {
            return back()->withInput()->with('danger', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }
}
