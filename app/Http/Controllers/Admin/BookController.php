<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Menampilkan daftar buku di katalog admin dengan fitur pencarian & filter kategori.
     */
    public function index(Request $request)
    {
        $search = trim($request->get('q', ''));
        $categoryId = $request->get('category');

        $query = Book::with('category');

        // Filter Pencarian Judul, Penulis, atau Penerbit
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%");
            });
        }

        // Filter Kategori
        if (!empty($categoryId) && is_numeric($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        $books = $query->orderBy('id', 'desc')->paginate(10);
        $categories = Category::orderBy('name', 'asc')->get();

        return view('admin.books.index', compact('books', 'categories', 'search', 'categoryId'));
    }

    /**
     * Menampilkan form tambah buku baru.
     */
    public function create()
    {
        $categories = Category::orderBy('name', 'asc')->get();
        return view('admin.books.create', compact('categories'));
    }

    /**
     * Menyimpan data buku baru ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Form
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:150',
            'publisher'   => 'nullable|string|max:150',
            'year'        => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'price'       => 'required|numeric|min:100',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'price.min'   => 'Harga buku harus lebih besar dari Rp 0 (minimal Rp 100).',
        ]);

        $cleanTitle = trim($validated['title']);
        $cleanAuthor = trim($validated['author']);

        // 2. Cek apakah judul buku sudah pernah terdaftar
        $existingBook = Book::whereRaw('LOWER(TRIM(title)) = ?', [strtolower($cleanTitle)])->first();
        if ($existingBook) {
            return back()
                ->withInput()
                ->with('danger', 'Gagal menambahkan! Judul buku "' . $cleanTitle . '" sudah ada di katalog. Silakan periksa kembali atau perbarui stok buku yang ada.')
                ->withErrors(['title' => 'Judul buku ini sudah terdaftar di katalog.']);
        }

        // 3. Upload File Sampul Buku (jika ada)
        $coverFilename = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $coverFilename = 'book_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $coverFilename);
        }

        // 4. Simpan ke Database
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

    /**
     * Menampilkan form edit buku.
     */
    public function edit($id)
    {
        $book = Book::findOrFail($id);
        $categories = Category::orderBy('name', 'asc')->get();
        return view('admin.books.edit', compact('book', 'categories'));
    }

    /**
     * Memperbarui data buku di database.
     */
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        // 1. Validasi Input Form
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:150',
            'publisher'   => 'nullable|string|max:150',
            'year'        => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'price'       => 'required|numeric|min:100',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'price.min'   => 'Harga buku harus lebih besar dari Rp 0 (minimal Rp 100).',
        ]);

        $cleanTitle = trim($validated['title']);

        // 2. Cek apakah judul buku digunakan oleh buku lain selain buku ini
        $titleDuplicate = Book::where('id', '!=', $id)
            ->whereRaw('LOWER(TRIM(title)) = ?', [strtolower($cleanTitle)])
            ->first();

        if ($titleDuplicate) {
            return back()
                ->withInput()
                ->with('danger', 'Gagal memperbarui! Judul "' . $cleanTitle . '" sudah digunakan oleh buku lain di katalog.')
                ->withErrors(['title' => 'Judul buku ini sudah terdaftar di katalog.']);
        }

        // 3. Upload File Sampul Baru jika diunggah
        $coverFilename = $book->cover_image;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $coverFilename = 'book_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $coverFilename);

            // Hapus file gambar lama jika ada
            if ($book->cover_image && $book->cover_image !== 'default_book.png') {
                $oldPath = public_path('uploads/' . $book->cover_image);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }

        // 4. Update Data Buku
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

    /**
     * Menghapus buku dari katalog dengan proteksi integritas transaksi.
     */
    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        $title = $book->title;

        // Proteksi 1: Tidak bisa dihapus jika stok masih ada
        if ($book->stock > 0) {
            return back()->with('danger', 'Buku "' . $title . '" tidak dapat dihapus karena masih memiliki sisa stok (' . $book->stock . ' unit).');
        }

        // Proteksi 2: Tidak bisa dihapus HANYA jika masih ada transaksi pesanan yang sedang aktif/berjalan
        $hasActiveOrders = $book->orders()
            ->whereIn('order_status', ['Menunggu Konfirmasi', 'Sedang Diproses', 'Sedang Dikirim'])
            ->exists();

        if ($hasActiveOrders) {
            return back()->with('danger', 'Buku "' . $title . '" tidak dapat dihapus karena masih ada pesanan pelanggan yang sedang aktif diproses.');
        }

        // Hapus file cover jika ada
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
