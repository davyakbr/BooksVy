<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRevenue = Order::where('payment_status', 'Lunas')->sum('total_amount');
        $totalOrders = Order::count();
        $totalBooks = Book::count();
        $totalUsers = User::where('role', 'user')->count();

        $recentOrders = Order::with('user')->orderBy('id', 'desc')->limit(6)->get();
        $criticalStockBooks = Book::where('stock', '<=', 3)->orderBy('stock', 'asc')->limit(6)->get();
        $unreadMessagesCount = ContactMessage::where('status', 'Menunggu Tanggapan')->count();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'totalBooks',
            'totalUsers',
            'recentOrders',
            'criticalStockBooks',
            'unreadMessagesCount'
        ));
    }
}
