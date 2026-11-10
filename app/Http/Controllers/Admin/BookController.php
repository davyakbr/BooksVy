<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('q', ''));
        $categoryId = $request->get('category');

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

        $books = $query->orderBy('id', 'desc')->paginate(5);
        $categories = Category::orderBy('name', 'asc')->get();

        return view('admin.books.index', compact('books', 'categories', 'search', 'categoryId'));
    }

    public function create()
    {
        $categories = Category::orderBy('name', 'asc')->get();
        return view('admin.books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:150',
            'publisher'   => 'nullable|string|max:150',
            'year'        => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $cleanTitle = trim($validated['title']);
        $cleanAuthor = trim($validated['author']);

        // 1. Cek jika judul dan penulis sama persis dengan buku yang sudah ada di katalog (case-insensitive & trim)
        $exactDuplicate = Book::whereRaw('LOWER(TRIM(title)) = ?', [strtolower($cleanTitle)])
            ->whereRaw('LOWER(TRIM(author)) = ?', [strtolower($cleanAuthor)])
            ->first();

        if ($exactDuplicate) {
            return back()
                ->withInput()
                ->with('danger', 'Gagal menambahkan! Buku "' . $cleanTitle . '" oleh penulis "' . $cleanAuthor . '" sudah terdaftar di katalog. Anda tidak dapat menambahkan buku yang sama persis.')
                ->withErrors(['title' => 'Buku dengan judul dan penulis ini sudah terdaftar di katalog. Silakan perbarui stok buku yang ada.']);
        }

        // 2. Cek jika judul buku sudah digunakan di katalog
        $titleDuplicate = Book::whereRaw('LOWER(TRIM(title)) = ?', [strtolower($cleanTitle)])->first();
        if ($titleDuplicate) {
            return back()
                ->withInput()
                ->with('danger', 'Gagal menambahkan! Judul buku "' . $cleanTitle . '" sudah ada di katalog (oleh penulis: ' . $titleDuplicate->author . '). Harap periksa kembali.')
                ->withErrors(['title' => 'Judul buku ini sudah terdaftar di katalog. Silakan gunakan judul yang berbeda.']);
        }

        $coverFilename = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $coverFilename = 'book_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $coverFilename);
        }

        Book::create([
            'category_id' => $validated['category_id'],
            'title'       => $validated['title'],
            'author'      => $validated['author'],
            'publisher'   => $validated['publisher'] ?? null,
            'year'        => $validated['year'] ?? null,
            'price'       => $validated['price'],
            'stock'       => $validated['stock'],
            'description' => $validated['description'] ?? null,
            'cover_image' => $coverFilename,
            'created_at'  => now(),
        ]);

        return redirect()->route('admin.books.index')->with('success', 'Buku baru berhasil ditambahkan ke katalog!');
    }

    public function edit($id)
    {
        $book = Book::findOrFail($id);
        $categories = Category::orderBy('name', 'asc')->get();
        return view('admin.books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:150',
            'publisher'   => 'nullable|string|max:150',
            'year'        => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $cleanTitle = trim($validated['title']);
        $cleanAuthor = trim($validated['author']);

        // 1. Cek jika judul dan penulis sama persis dengan buku lain saat edit
        $exactDuplicate = Book::where('id', '!=', $id)
            ->whereRaw('LOWER(TRIM(title)) = ?', [strtolower($cleanTitle)])
            ->whereRaw('LOWER(TRIM(author)) = ?', [strtolower($cleanAuthor)])
            ->first();

        if ($exactDuplicate) {
            return back()
                ->withInput()
                ->with('danger', 'Gagal memperbarui! Buku "' . $cleanTitle . '" oleh penulis "' . $cleanAuthor . '" sudah ada di katalog.')
                ->withErrors(['title' => 'Buku dengan judul dan penulis ini sudah terdaftar di katalog.']);
        }

        // 2. Cek jika judul sudah dipakai oleh buku lain
        $titleDuplicate = Book::where('id', '!=', $id)
            ->whereRaw('LOWER(TRIM(title)) = ?', [strtolower($cleanTitle)])
            ->first();

        if ($titleDuplicate) {
            return back()
                ->withInput()
                ->with('danger', 'Gagal memperbarui! Judul "' . $cleanTitle . '" sudah digunakan oleh buku lain di katalog.')
                ->withErrors(['title' => 'Judul buku ini sudah terdaftar di katalog.']);
        }

        $coverFilename = $book->cover_image;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $coverFilename = 'book_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $coverFilename);
        }

        $book->update([
            'category_id' => $validated['category_id'],
            'title'       => $validated['title'],
            'author'      => $validated['author'],
            'publisher'   => $validated['publisher'] ?? null,
            'year'        => $validated['year'] ?? null,
            'price'       => $validated['price'],
            'stock'       => $validated['stock'],
            'description' => $validated['description'] ?? null,
            'cover_image' => $coverFilename,
        ]);

        return redirect()->route('admin.books.index')->with('success', 'Data buku "' . $book->title . '" berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        $title = $book->title;

        // Validasi: Buku tidak dapat dihapus jika masih ada stok
        if ($book->stock > 0) {
            return back()->with('danger', 'Buku "' . $title . '" tidak dapat dihapus karena masih memiliki stok (' . $book->stock . ' unit). Harap kosongkan stok terlebih dahulu jika ingin menghapus buku ini.');
        }

        // Hapus file cover buku dari public/uploads jika ada dan bukan default
        if ($book->cover_image && $book->cover_image !== 'default_book.png') {
            $coverPath = public_path('uploads/' . $book->cover_image);
            if (file_exists($coverPath)) {
                @unlink($coverPath);
            }
        }

        $book->delete();

        return redirect()->route('admin.books.index')->with('success', 'Buku "' . $title . '" berhasil dihapus dari katalog.');
    }
}
