<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $totalPrice = 0;
        $totalItemsCount = 0;
        $stockData = [];
        $hasOutOfStock = false;
        $outOfStockNames = [];
        $exceededStockNames = [];

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

        // Pisahkan: produk tersedia di atas, produk yang stoknya habis di paling bawah
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

    public function quickAdd(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->input('format') === 'json';

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

        $bookId = (int)$request->input('book_id', 0);
        $book = Book::find($bookId);

        if (!$book) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Buku tidak ditemukan.']);
            }
            return back()->with('danger', 'Buku tidak ditemukan.');
        }

        if ($book->stock <= 0) {
            $msg = 'Maaf, stok buku "' . $book->title . '" sedang habis.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg]);
            }
            return back()->with('danger', $msg);
        }

        $cart = session()->get('cart', []);
        $currentQty = isset($cart[$bookId]) ? $cart[$bookId]['qty'] : 0;

        if ($currentQty + 1 > $book->stock) {
            $msg = 'Buku "' . $book->title . '" sudah berada di keranjang Anda sebanyak ' . $currentQty . ' unit (mencapai batas maksimal stok ' . $book->stock . ' buku).';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg]);
            }
            return back()->with('warning', $msg);
        }

        if (isset($cart[$bookId])) {
            $cart[$bookId]['qty'] += 1;
        } else {
            $cart[$bookId] = [
                'id'          => $book->id,
                'title'       => $book->title,
                'price'       => (float)$book->price,
                'cover_image' => $book->cover_image,
                'qty'         => 1,
            ];
        }

        session()->put('cart', $cart);
        $this->syncUserCart($cart);

        $cartCount = array_sum(array_column($cart, 'qty'));

        if ($isAjax) {
            return response()->json([
                'success'   => true,
                'message'   => 'Buku "' . $book->title . '" berhasil ditambahkan ke keranjang!',
                'cartCount' => $cartCount,
                'bookTitle' => $book->title
            ]);
        }

        return back()->with('success', 'Buku berhasil ditambahkan ke keranjang belanja.');
    }

    public function add(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->input('format') === 'json';

        if (!Auth::check()) {
            if ($isAjax) {
                return response()->json([
                    'success'      => false,
                    'requireLogin' => true,
                    'redirect'     => route('login'),
                    'message'      => 'Silakan masuk (login) terlebih dahulu untuk menambahkan buku ke keranjang.'
                ]);
            }
            return redirect()->route('login')->with('warning', 'Silakan masuk (login) terlebih dahulu untuk mulai memesan buku.');
        }

        $bookId = (int)$request->input('book_id', 0);
        $qty = max(1, (int)$request->input('quantity', 1));
        $book = Book::find($bookId);

        if (!$book) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Buku tidak ditemukan.']);
            }
            return back()->with('danger', 'Buku tidak ditemukan.');
        }

        if ($book->stock <= 0) {
            $msg = 'Maaf, stok buku ini sedang habis.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg]);
            }
            return back()->with('danger', $msg);
        }

        $cart = session()->get('cart', []);
        $currentQty = isset($cart[$bookId]) ? $cart[$bookId]['qty'] : 0;
        $remainingStock = max(0, $book->stock - $currentQty);

        if ($currentQty + $qty > $book->stock) {
            if ($remainingStock <= 0) {
                $msg = 'Buku "' . $book->title . '" sudah berada di keranjang Anda sebanyak ' . $currentQty . ' unit (telah mencapai seluruh batas stok maksimal yang tersedia).';
                if ($isAjax) {
                    return response()->json([
                        'success'       => false,
                        'alreadyMax'    => true,
                        'currentInCart' => $currentQty,
                        'remaining'     => 0,
                        'maxStock'      => $book->stock,
                        'message'       => $msg
                    ]);
                }
                return back()->with('warning', $msg);
            } else {
                $msg = 'Gagal menambahkan! Anda sudah memiliki ' . $currentQty . ' unit buku ini di keranjang (total stok: ' . $book->stock . ' unit). Anda hanya bisa menambahkan maksimal ' . $remainingStock . ' unit lagi.';
                if ($isAjax) {
                    return response()->json([
                        'success'       => false,
                        'exceeded'      => true,
                        'currentInCart' => $currentQty,
                        'remaining'     => $remainingStock,
                        'maxStock'      => $book->stock,
                        'message'       => $msg
                    ]);
                }
                return back()->with('warning', $msg);
            }
        }

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

        if ($isAjax) {
            $newInCart = $cart[$bookId]['qty'];
            $newRemaining = max(0, $book->stock - $newInCart);
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

    public function update(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->input('format') === 'json';
        $bookId = (int)$request->input('book_id', 0);
        $qty = (int)$request->input('qty', 1);
        $cart = session()->get('cart', []);

        if (isset($cart[$bookId])) {
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

            $book = Book::find($bookId);
            if ($book && $book->stock <= 0) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => "Buku '{$book->title}' telah habis stoknya (0 unit)."
                    ]);
                }
                return redirect()->route('cart.index')->with('danger', "Buku '{$book->title}' telah habis stoknya (0 unit). Silakan hapus item tersebut dari keranjang.");
            } elseif ($book && $qty > $book->stock) {
                $cart[$bookId]['qty'] = $book->stock;
                session()->put('cart', $cart);
                $this->syncUserCart($cart);
                $cartCount = array_sum(array_column($cart, 'qty'));
                $itemSubtotal = $cart[$bookId]['price'] * $cart[$bookId]['qty'];

                if ($isAjax) {
                    return response()->json([
                        'success'               => true,
                        'warning'               => true,
                        'bookId'                => $bookId,
                        'qty'                   => $cart[$bookId]['qty'],
                        'itemSubtotal'          => $itemSubtotal,
                        'itemSubtotalFormatted' => formatRupiah($itemSubtotal),
                        'cartCount'             => $cartCount,
                        'maxStock'              => $book->stock,
                        'message'               => "Jumlah pesanan untuk '{$book->title}' disesuaikan dengan sisa stok ({$book->stock} buku)."
                    ]);
                }
                return redirect()->route('cart.index')->with('warning', "Jumlah pesanan untuk '{$book->title}' disesuaikan dengan sisa stok ({$book->stock} buku).");
            } else {
                $cart[$bookId]['qty'] = $qty;
                session()->put('cart', $cart);
                $this->syncUserCart($cart);
                $cartCount = array_sum(array_column($cart, 'qty'));
                $itemSubtotal = $cart[$bookId]['price'] * $cart[$bookId]['qty'];

                if ($isAjax) {
                    return response()->json([
                        'success'               => true,
                        'bookId'                => $bookId,
                        'qty'                   => $cart[$bookId]['qty'],
                        'itemSubtotal'          => $itemSubtotal,
                        'itemSubtotalFormatted' => formatRupiah($itemSubtotal),
                        'cartCount'             => $cartCount,
                        'maxStock'              => $book ? $book->stock : 999,
                        'message'               => 'Jumlah buku berhasil diperbarui.'
                    ]);
                }
                return redirect()->route('cart.index')->with('success', 'Jumlah buku berhasil diperbarui.');
            }
        }

        if ($isAjax) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan di keranjang.']);
        }
        return redirect()->route('cart.index');
    }

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

    public function clear()
    {
        session()->forget('cart');
        session()->forget('checkout_selected');
        $this->syncUserCart([]);
        return redirect()->route('cart.index')->with('info', 'Seluruh isi keranjang belanja berhasil dikosongkan.');
    }

    private function syncUserCart(array $cart): void
    {
        if (Auth::check()) {
            \App\Models\UserCart::updateOrCreate(
                ['user_id' => Auth::id()],
                ['cart_data' => $cart]
            );
        }
    }
}
