<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman beranda / katalog buku online BooksVy.
     * Mendukung: Pencarian buku, filter genre/kategori, pengurutan, dan pagination 10 item.
     */
    public function index(Request $request)
    {
        // =========================================================================
        // 1. TANGKAP PARAMETER DARI FORM / URL (Query String)
        // =========================================================================
        $search     = trim($request->get('q', ''));          // Kata kunci pencarian (judul / penulis / penerbit)
        $categoryId = $request->get('category');             // ID kategori yang dipilih (jika ada)
        $sortBy     = $request->get('sort', 'latest');       // Pilihan pengurutan (default: terbaru)

        // =========================================================================
        // 2. AMBIL BANNER HERO SLIDER YANG AKTIF
        // =========================================================================
        $banners = Banner::where('is_active', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // =========================================================================
        // 3. AMBIL SEMUA KATEGORI & HITUNG JUMLAH BUKUNYA (Untuk Menu Filter Kategori)
        // =========================================================================
        $categories = Category::withCount('books')
            ->orderBy('name', 'asc')
            ->get();

        // =========================================================================
        // 4. SUSUN QUERY KATALOG BUKU
        // =========================================================================
        $query = Book::with('category');

        // A. Filter Pencarian: Cek apakah ada kata kunci di judul, penulis, atau penerbit
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%");
            });
        }

        // B. Filter Kategori: Jika user mengklik salah satu kategori
        if (!empty($categoryId) && is_numeric($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        // C. UX Rule: Buku yang stoknya masih ada (stok > 0) selalu muncul di atas buku yang habis
        $query->orderByRaw('(stock > 0) DESC');

        // D. Pengurutan Data (Sorting)
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');       // Termurah ke termahal
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');      // Termahal ke termurah
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');       // Urut abjad A-Z
                break;
            case 'latest':
            default:
                $query->orderBy('id', 'desc');          // Buku paling baru diinput
                break;
        }

        // =========================================================================
        // 5. PAGINATION (6 Buku Per Halaman)
        //    - withQueryString() : Parameter pencarian & filter tetap ada saat klik next/prev halaman
        //    - fragment('katalog'): Otomatis scroll ke bagian katalog saat halaman berpindah
        // =========================================================================
        $books = $query->paginate(6)->withQueryString()->fragment('katalog');

        // =========================================================================
        // 6. KIRIM DATA KE VIEW (resources/views/home.blade.php)
        // =========================================================================
        return view('home', compact(
            'books',
            'categories',
            'banners',
            'search',
            'categoryId',
            'sortBy'
        ));
    }
}
