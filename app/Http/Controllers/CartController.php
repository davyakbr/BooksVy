<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\UserCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Menampilkan halaman keranjang belanja beserta validasi stok real-time.
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        $totalPrice = 0;
        $totalItemsCount = 0;
        $stockData = [];
        $hasOutOfStock = false;
        $outOfStockNames = [];
        $exceededStockNames = [];

        // Periksa status stok setiap item di keranjang
        foreach ($cart as $bookId => $item) {
            $book = Book::find($bookId);
            $curStock = $book ? $book->stock : 0;
            $isOut = ($curStock <= 0);
            $isExceeded = (!$isOut && $item['qty'] > $curStock);

            if ($isOut) {
                $hasOutOfStock = true;
                $outOfStockNames[] = $item['title'];
            } elseif ($isExceeded) {
                $hasOutOfStock = true;
                $exceededStockNames[] = $item['title'] . " (Sisa: {$curStock})";
            }

            $stockData[$bookId] = [
                'stock'       => $curStock,
                'is_out'      => $isOut,
                'is_exceeded' => $isExceeded
            ];

            $totalPrice += ($item['price'] * $item['qty']);
            $totalItemsCount += $item['qty'];
        }

        // Pisahkan item yang masih tersedia dan item yang stoknya habis (diletakkan di bawah)
        $availableItems = [];
        $outOfStockItems = [];

        foreach ($cart as $bookId => $item) {
            if (!empty($stockData[$bookId]['is_out'])) {
                $outOfStockItems[$bookId] = $item;
            } else {
                $availableItems[$bookId] = $item;
            }
        }

        $sortedCartItems = $availableItems + $outOfStockItems;

        return view('cart.index', compact(
            'cart',
            'sortedCartItems',
            'availableItems',
            'outOfStockItems',
            'stockData',
            'hasOutOfStock',
            'outOfStockNames',
            'exceededStockNames',
            'totalPrice',
            'totalItemsCount'
        ));
    }

    /**
     * Tambah cepat 1 buku dari katalog beranda (AJAX).
     */
    public function quickAdd(Request $request)
    {
        $bookId = (int)$request->input('book_id', 0);
        return $this->executeAddToCart($request, $bookId, 1);
    }

    /**
     * Tambah buku dari halaman detail dengan jumlah kuantitas tertentu.
     */
    public function add(Request $request)
    {
        $bookId = (int)$request->input('book_id', 0);
        $qty = max(1, (int)$request->input('quantity', 1));
        return $this->executeAddToCart($request, $bookId, $qty);
    }

    /**
     * Logika utama penambahan buku ke keranjang session dan database.
     */
    private function executeAddToCart(Request $request, int $bookId, int $qty)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->input('format') === 'json';

        // 1. Wajib Login
        if (!Auth::check()) {
            if ($isAjax) {
                return response()->json([
                    'success'      => false,
                    'requireLogin' => true,
                    'redirect'     => route('login'),
                    'message'      => 'Silakan masuk (login) terlebih dahulu untuk menambahkan buku ke keranjang.'
                ]);
            }
            return redirect()->route('login')->with('warning', 'Silakan masuk (login) terlebih dahulu.');
        }

        // 2. Cek Buku di Database
        $book = Book::find($bookId);
        if (!$book) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Buku tidak ditemukan.']);
            }
            return back()->with('danger', 'Buku tidak ditemukan.');
        }

        // 3. Cek Ketersediaan Stok
        if ($book->stock <= 0) {
            $msg = 'Maaf, stok buku "' . $book->title . '" sedang habis.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg]);
            }
            return back()->with('danger', $msg);
        }

        $cart = session()->get('cart', []);
        $currentQty = isset($cart[$bookId]) ? $cart[$bookId]['qty'] : 0;
        $remainingStock = max(0, $book->stock - $currentQty);

        // 4. Validasi Batas Maksimal Stok
        if ($currentQty + $qty > $book->stock) {
            $msg = $remainingStock <= 0
                ? 'Buku "' . $book->title . '" sudah berada di keranjang Anda sebanyak ' . $currentQty . ' unit (mencapai batas maksimal stok).'
                : 'Anda sudah memiliki ' . $currentQty . ' unit di keranjang. Anda hanya bisa menambah maksimal ' . $remainingStock . ' unit lagi.';

            if ($isAjax) {
                return response()->json([
                    'success'       => false,
                    'alreadyMax'    => ($remainingStock <= 0),
                    'exceeded'      => ($remainingStock > 0),
                    'currentInCart' => $currentQty,
                    'remaining'     => $remainingStock,
                    'maxStock'      => $book->stock,
                    'message'       => $msg
                ]);
            }
            return back()->with('warning', $msg);
        }

        // 5. Simpan ke Keranjang Session
        if (isset($cart[$bookId])) {
            $cart[$bookId]['qty'] += $qty;
        } else {
            $cart[$bookId] = [
                'id'          => $book->id,
                'title'       => $book->title,
                'price'       => (float)$book->price,
                'cover_image' => $book->cover_image,
                'qty'         => $qty,
            ];
        }

        session()->put('cart', $cart);
        $this->syncUserCart($cart);

        $cartCount = array_sum(array_column($cart, 'qty'));
        $newInCart = $cart[$bookId]['qty'];
        $newRemaining = max(0, $book->stock - $newInCart);

        if ($isAjax) {
            return response()->json([
                'success'       => true,
                'message'       => 'Buku "' . $book->title . '" berhasil dimasukkan ke keranjang belanja!',
                'cartCount'     => $cartCount,
                'bookTitle'     => $book->title,
                'currentInCart' => $newInCart,
                'remaining'     => $newRemaining,
                'maxStock'      => $book->stock
            ]);
        }

        return back()->with('success', 'Buku "' . $book->title . '" berhasil dimasukkan ke keranjang belanja!');
    }

    /**
     * Memperbarui kuantitas (qty) buku di keranjang.
     */
    public function update(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->input('format') === 'json';
        $bookId = (int)$request->input('book_id', 0);
        $qty = (int)$request->input('qty', 1);
        $cart = session()->get('cart', []);

        if (!isset($cart[$bookId])) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Item tidak ditemukan di keranjang.']);
            }
            return redirect()->route('cart.index');
        }

        // Jika qty 0 atau negatif, hapus item dari keranjang
        if ($qty <= 0) {
            unset($cart[$bookId]);
            session()->put('cart', $cart);
            $this->syncUserCart($cart);
            $cartCount = array_sum(array_column($cart, 'qty'));

            if ($isAjax) {
                return response()->json([
                    'success'   => true,
                    'deleted'   => true,
                    'bookId'    => $bookId,
                    'cartCount' => $cartCount,
                    'message'   => 'Item telah dihapus dari keranjang belanja.'
                ]);
            }
            return redirect()->route('cart.index')->with('info', 'Item telah dihapus dari keranjang belanja.');
        }

        // Periksa batasan stok buku
        $book = Book::find($bookId);
        if ($book && $book->stock <= 0) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => "Buku '{$book->title}' telah habis stoknya (0 unit)."]);
            }
            return redirect()->route('cart.index')->with('danger', "Buku '{$book->title}' telah habis stoknya. Silakan hapus item tersebut dari keranjang.");
        }

        $warning = false;
        $message = 'Jumlah buku berhasil diperbarui.';

        // Jika qty melebihi stok yang ada, sesuaikan dengan sisa stok
        if ($book && $qty > $book->stock) {
            $cart[$bookId]['qty'] = $book->stock;
            $warning = true;
            $message = "Jumlah pesanan untuk '{$book->title}' disesuaikan dengan sisa stok ({$book->stock} buku).";
        } else {
            $cart[$bookId]['qty'] = $qty;
        }

        session()->put('cart', $cart);
        $this->syncUserCart($cart);

        $cartCount = array_sum(array_column($cart, 'qty'));
        $itemSubtotal = $cart[$bookId]['price'] * $cart[$bookId]['qty'];

        if ($isAjax) {
            return response()->json([
                'success'               => true,
                'warning'               => $warning,
                'bookId'                => $bookId,
                'qty'                   => $cart[$bookId]['qty'],
                'itemSubtotal'          => $itemSubtotal,
                'itemSubtotalFormatted' => formatRupiah($itemSubtotal),
                'cartCount'             => $cartCount,
                'maxStock'              => $book ? $book->stock : 999,
                'message'               => $message
            ]);
        }

        return redirect()->route('cart.index')->with($warning ? 'warning' : 'success', $message);
    }

    /**
     * Menghapus satu buku spesifik dari keranjang.
     */
    public function remove(Request $request)
    {
        $bookId = (int)$request->input('book_id', 0);
        $cart = session()->get('cart', []);

        if (isset($cart[$bookId])) {
            $title = $cart[$bookId]['title'] ?? 'Item';
            unset($cart[$bookId]);
            session()->put('cart', $cart);
            $this->syncUserCart($cart);

            return redirect()->route('cart.index')->with('info', 'Buku "' . $title . '" telah dikeluarkan dari keranjang.');
        }

        return redirect()->route('cart.index');
    }

    /**
     * Mengosongkan seluruh isi keranjang belanja.
     */
    public function clear()
    {
        session()->forget('cart');
        session()->forget('checkout_selected');
        $this->syncUserCart([]);

        return redirect()->route('cart.index')->with('info', 'Seluruh isi keranjang belanja berhasil dikosongkan.');
    }

    /**
     * Helper: Sinkronisasi keranjang belanja ke tabel `user_carts` untuk user yang sedang login.
     */
    private function syncUserCart(array $cart): void
    {
        if (Auth::check()) {
            UserCart::updateOrCreate(
                ['user_id' => Auth::id()],
                ['cart_data' => $cart]
            );
        }
    }
}
