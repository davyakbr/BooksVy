<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    /**
     * Menampilkan halaman formulir checkout.
     */
    public function index(Request $request)
    {
        $rawCart = session()->get('cart', []);

        if (empty($rawCart)) {
            return redirect()->route('home')->with('warning', 'Keranjang belanja Anda kosong. Silakan pilih buku terlebih dahulu.');
        }

        // Simpan pilihan item jika dikirim melalui form POST dari keranjang
        if ($request->isMethod('post') && $request->has('selected_items') && is_array($request->selected_items)) {
            session(['checkout_selected' => array_map('intval', $request->selected_items)]);
        }

        // Ambil item buku yang dipilih untuk di-checkout
        $cartItems = $this->getSelectedCartItems();

        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('danger', 'Tidak ada buku yang dipilih atau produk yang dipilih stoknya telah habis. Silakan pilih buku yang tersedia.');
        }

        // Validasi stok terkini untuk buku yang dipilih
        foreach ($cartItems as $bookId => $item) {
            $book = Book::find($bookId);
            $currentStock = $book ? $book->stock : 0;

            if ($currentStock <= 0) {
                return redirect()->route('cart.index')->with('danger', 'Maaf, buku "' . $item['title'] . '" saat ini stoknya telah habis. Silakan pilih buku lain atau hapus dari keranjang.');
            }

            if ($item['qty'] > $currentStock) {
                return redirect()->route('cart.index')->with('warning', 'Jumlah pesanan untuk buku "' . $item['title'] . '" melebihi stok yang tersedia (' . $currentStock . ' unit).');
            }
        }

        // Hitung total harga dan jumlah buku
        $totalPrice = 0;
        $totalCount = 0;
        foreach ($cartItems as $item) {
            $totalPrice += ($item['price'] * $item['qty']);
            $totalCount += $item['qty'];
        }

        $user = Auth::user();

        return view('checkout.index', compact('cartItems', 'totalPrice', 'totalCount', 'user'));
    }

    /**
     * Memproses transaksi pembelian (checkout) dan membuat pesanan baru.
     */
    public function process(Request $request)
    {
        // 1. Validasi Input Pengiriman
        $validated = $request->validate([
            'recipient_name'   => 'required|string|max:100',
            'recipient_phone'  => ['required', 'string', 'max:25', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'shipping_address' => 'required|string',
            'payment_channel'  => 'required|string',
            'notes'            => 'nullable|string',
        ], [
            'recipient_name.required'   => 'Nama penerima wajib diisi.',
            'recipient_phone.required'  => 'Nomor telepon/WhatsApp penerima wajib diisi.',
            'recipient_phone.regex'     => 'Nomor WhatsApp / telepon harus berupa nomor yang valid (minimal 8 digit angka).',
            'shipping_address.required' => 'Alamat pengiriman lengkap wajib diisi.',
        ]);

        // 2. Ambil item yang akan di-checkout
        $cartItems = $this->getSelectedCartItems();

        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('danger', 'Tidak ada item yang dapat diproses untuk checkout.');
        }

        try {
            // 3. Jalankan Database Transaction agar konsistensi data dan stok terjamin
            $order = DB::transaction(function () use ($validated, $cartItems) {
                $actualBooks = [];
                $realTotalPrice = 0;

                // Verifikasi stok & harga riil langsung dari database dengan Lock (mencegah race condition)
                foreach ($cartItems as $bookId => $item) {
                    $book = Book::where('id', $bookId)->lockForUpdate()->first();
                    if (!$book || $book->stock < $item['qty']) {
                        $available = $book ? $book->stock : 0;
                        throw new \Exception("Stok untuk buku '{$item['title']}' tidak mencukupi (Tersedia: {$available}, Diminta: {$item['qty']}).");
                    }

                    $actualBooks[$bookId] = $book;
                    $realTotalPrice += ((float)$book->price * $item['qty']);
                }

                // Buat nomor pesanan unik
                $orderNumber = 'BVY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
                $paymentDesc = 'Payment at Delivery (' . $validated['payment_channel'] . ')';

                // Simpan data induk pesanan ke tabel `orders`
                $order = Order::create([
                    'order_number'     => $orderNumber,
                    'user_id'          => Auth::id(),
                    'recipient_name'   => $validated['recipient_name'],
                    'recipient_phone'  => $validated['recipient_phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'payment_method'   => $paymentDesc,
                    'payment_status'   => 'Lunas',
                    'order_status'     => 'Sedang Diproses',
                    'total_amount'     => $realTotalPrice,
                    'notes'            => $validated['notes'] ?? null,
                    'created_at'       => now(),
                ]);

                // Simpan rincian item ke `order_items` dan kurangi stok buku
                foreach ($cartItems as $bookId => $item) {
                    $book = $actualBooks[$bookId];
                    $currentRealPrice = (float)$book->price;
                    $subtotal = $currentRealPrice * $item['qty'];

                    OrderItem::create([
                        'order_id'   => $order->id,
                        'book_id'    => $bookId,
                        'book_title' => $book->title,
                        'price'      => $currentRealPrice,
                        'quantity'   => $item['qty'],
                        'subtotal'   => $subtotal,
                    ]);

                    // Kurangi stok buku di database
                    $book->decrement('stock', $item['qty']);
                }

                // 4. Bersihkan item yang dibeli dari keranjang session & database
                $currentCart = session()->get('cart', []);
                foreach ($cartItems as $bookId => $item) {
                    unset($currentCart[$bookId]);
                }
                session()->put('cart', $currentCart);
                session()->forget('checkout_selected');

                if (Auth::check()) {
                    UserCart::updateOrCreate(
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

    /**
     * Helper: Mengambil daftar buku yang dipilih dari keranjang.
     * Jika tidak ada yang dipilih spesifik, mengambil semua buku di keranjang yang stoknya > 0.
     */
    private function getSelectedCartItems(): array
    {
        $rawCart = session()->get('cart', []);
        $selectedIds = session('checkout_selected', []);
        $cartItems = [];

        // 1. Ambil berdasarkan ID yang dicentang user
        if (!empty($selectedIds)) {
            foreach ($selectedIds as $id) {
                if (isset($rawCart[$id])) {
                    $cartItems[$id] = $rawCart[$id];
                }
            }
        }

        // 2. Fallback: jika tidak ada yang dicentang, ambil semua buku yang stoknya tersedia
        if (empty($cartItems)) {
            foreach ($rawCart as $bookId => $item) {
                $book = Book::find($bookId);
                if ($book && $book->stock > 0) {
                    $cartItems[$bookId] = $item;
                }
            }
        }

        return $cartItems;
    }
}
