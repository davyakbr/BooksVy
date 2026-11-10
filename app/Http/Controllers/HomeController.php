<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('q', ''));
        $categoryId = $request->get('category');
        $sortBy = $request->get('sort', 'latest');

        $banners = Banner::where('is_active', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $categories = Category::withCount('books')
            ->orderBy('name', 'asc')
            ->get();

        $query = Book::with('category');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%");
            });
        }

        if (!empty($categoryId) && is_numeric($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        // Buku dengan stok tersedia selalu di atas, buku habis selalu di paling bawah
        $query->orderByRaw('(stock > 0) DESC');

        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'latest':
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        $books = $query->get();

        return view('home', compact('books', 'categories', 'banners', 'search', 'categoryId', 'sortBy'));
    }
}
