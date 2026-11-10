<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'Silakan masuk (login) atau daftar akun terlebih dahulu untuk menghubungi Admin BooksVy.');
        }

        $userId = Auth::id();
        $user = Auth::user();
        $myMessages = ContactMessage::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
        $userOrders = Order::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

        $activeTab = $request->get('tab', 'create');

        return view('contact', compact('user', 'myMessages', 'userOrders', 'activeTab'));
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'Silakan masuk (login) atau daftar akun terlebih dahulu untuk menghubungi Admin BooksVy.');
        }

        $user = Auth::user();

        $validated = $request->validate([
            'topic'        => 'required|string|max:100',
            'order_number' => 'nullable|string|max:50',
            'subject'      => 'required|string|max:200',
            'message'      => 'required|string',
        ], [
            'topic.required'   => 'Kategori topik pertanyaan wajib dipilih.',
            'subject.required' => 'Subjek pesan tidak boleh kosong.',
            'message.required' => 'Isi rincian pesan wajib diisi.',
        ]);

        $ticketNumber = 'PSN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        ContactMessage::create([
            'ticket_number' => $ticketNumber,
            'user_id'       => $user->id,
            'topic'         => $validated['topic'],
            'order_number'  => $validated['order_number'] ?? null,
            'name'          => $user->name,
            'email'         => $user->email,
            'subject'       => $validated['subject'],
            'message'       => $validated['message'],
            'status'        => 'Menunggu Tanggapan',
            'created_at'    => now(),
        ]);

        return redirect()->route('contact', ['tab' => 'my_messages'])
            ->with('success', 'Pesan Anda dengan ID <strong>#' . $ticketNumber . '</strong> berhasil dikirimkan ke Admin BooksVy! Tanggapan resmi akan muncul pada tab Riwayat Pesan.');
    }
}
