<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->get('role');
        $search = trim($request->get('q', ''));

        $query = User::withCount('orders');

        if (!empty($role)) {
            $query->where('role', $role);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(15);

        return view('admin.users.index', compact('users', 'role', 'search'));
    }

    public function destroy($id)
    {
        if ((int)$id === Auth::id()) {
            return back()->with('danger', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user = User::findOrFail($id);

        // Validasi: Tidak boleh menghapus akun sesama Administrator
        if ($user->role === 'admin') {
            return back()->with('danger', 'Akun Administrator tidak dapat dihapus melalui menu ini demi keamanan sistem.');
        }

        // Validasi: Pengguna tidak boleh dihapus jika masih memiliki pesanan aktif yang belum selesai
        $hasActiveOrders = $user->orders()->whereIn('order_status', ['Menunggu Konfirmasi', 'Sedang Diproses', 'Sedang Dikirim'])->exists();
        if ($hasActiveOrders) {
            return back()->with('danger', 'Akun pengguna "' . $user->name . '" tidak dapat dihapus karena masih memiliki pesanan aktif yang sedang diproses atau dikirim.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Akun pengguna berhasil dihapus.');
    }
}
