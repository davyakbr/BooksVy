<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:200',
            'subtitle'   => 'nullable|string',
            'badge_text' => 'nullable|string|max:100',
            'btn1_label' => 'nullable|string|max:80',
            'btn1_url'   => 'nullable|string|max:255',
            'btn2_label' => 'nullable|string|max:80',
            'btn2_url'   => 'nullable|string|max:255',
            'bg_class'   => 'nullable|string|max:50',
            'sort_order' => 'required|integer',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $imageFilename = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageFilename = 'banner_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $imageFilename);
        }

        Banner::create([
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'badge_text' => $validated['badge_text'] ?? null,
            'btn1_label' => $validated['btn1_label'] ?? null,
            'btn1_url'   => $validated['btn1_url'] ?? null,
            'btn2_label' => $validated['btn2_label'] ?? null,
            'btn2_url'   => $validated['btn2_url'] ?? null,
            'bg_class'   => $validated['bg_class'] ?? 'hero-slide-1',
            'sort_order' => $validated['sort_order'],
            'image'      => $imageFilename,
            'is_active'  => 1,
            'created_at' => now(),
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner promosi baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $validated = $request->validate([
            'title'      => 'required|string|max:200',
            'subtitle'   => 'nullable|string',
            'badge_text' => 'nullable|string|max:100',
            'btn1_label' => 'nullable|string|max:80',
            'btn1_url'   => 'nullable|string|max:255',
            'btn2_label' => 'nullable|string|max:80',
            'btn2_url'   => 'nullable|string|max:255',
            'bg_class'   => 'nullable|string|max:50',
            'sort_order' => 'required|integer',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $imageFilename = $banner->image;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageFilename = 'banner_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $imageFilename);
        }

        $banner->update([
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'badge_text' => $validated['badge_text'] ?? null,
            'btn1_label' => $validated['btn1_label'] ?? null,
            'btn1_url'   => $validated['btn1_url'] ?? null,
            'btn2_label' => $validated['btn2_label'] ?? null,
            'btn2_url'   => $validated['btn2_url'] ?? null,
            'bg_class'   => $validated['bg_class'] ?? $banner->bg_class,
            'sort_order' => $validated['sort_order'],
            'image'      => $imageFilename,
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil diperbarui!');
    }

    public function toggle($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->is_active = !$banner->is_active;
        $banner->save();

        $status = $banner->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.banners.index')->with('info', "Banner berhasil {$status}.");
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil dihapus.');
    }
}
