<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('books')->orderBy('name', 'asc')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $cleanName = trim($request->input('name'));

        // Cek duplikasi kategori yang sama (case-insensitive & trim spasi)
        $duplicate = Category::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cleanName)])->first();

        if ($duplicate) {
            return back()
                ->withInput()
                ->with('danger', 'Gagal menambahkan! Kategori "' . $cleanName . '" sudah terdaftar. Anda tidak dapat menambahkan kategori yang sama.')
                ->withErrors(['name' => 'Kategori dengan nama "' . $cleanName . '" sudah ada di daftar kategori.']);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
        ]);

        Category::create([
            'name'        => $cleanName,
            'description' => $validated['description'] ?? null,
            'created_at'  => now(),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $cleanName = trim($request->input('name'));

        // Cek jika nama kategori sudah digunakan oleh kategori lain (case-insensitive & trim spasi)
        $duplicate = Category::where('id', '!=', $id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cleanName)])
            ->first();

        if ($duplicate) {
            return back()
                ->withInput()
                ->with('danger', 'Gagal memperbarui! Kategori "' . $cleanName . '" sudah ada.')
                ->withErrors(['name' => 'Kategori dengan nama "' . $cleanName . '" sudah digunakan.']);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
        ]);

        $category->update([
            'name'        => $cleanName,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $category = Category::withCount('books')->findOrFail($id);

        if ($category->books_count > 0) {
            return back()->with('danger', 'Kategori tidak dapat dihapus karena masih digunakan oleh ' . $category->books_count . ' buku.');
        }

        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
