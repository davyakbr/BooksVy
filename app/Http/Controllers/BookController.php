<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function show($id)
    {
        $book = Book::with('category')->findOrFail($id);

        $relatedBooks = Book::where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->orderByRaw('(stock > 0) DESC')
            ->orderBy('id', 'desc')
            ->limit(4)
            ->get();

        $cart = session()->get('cart', []);
        $inCartQty = isset($cart[$book->id]) ? (int)$cart[$book->id]['qty'] : 0;
        $remainingStock = max(0, (int)$book->stock - $inCartQty);

        return view('books.show', compact('book', 'relatedBooks', 'inCartQty', 'remainingStock'));
    }
}
