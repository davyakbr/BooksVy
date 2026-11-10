<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = trim($request->get('q', ''));

        $query = ContactMessage::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderBy('id', 'desc')->paginate(15);

        return view('admin.messages.index', compact('messages', 'status', 'search'));
    }

    public function show($id)
    {
        $message = ContactMessage::findOrFail($id);
        return view('admin.messages.show', compact('message'));
    }

    public function reply(Request $request, $id)
    {
        $message = ContactMessage::findOrFail($id);

        $validated = $request->validate([
            'reply' => 'required|string',
        ], [
            'reply.required' => 'Isi balasan wajib diisi.',
        ]);

        $message->update([
            'reply'      => $validated['reply'],
            'replied_at' => now(),
            'status'     => 'Sudah Dibalas',
        ]);

        return back()->with('success', 'Balasan untuk tiket #' . $message->ticket_number . ' berhasil disimpan!');
    }

    public function destroy($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Pesan berhasil dihapus.');
    }
}
