<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiWebController extends Controller
{
    public function index()
    {
        $notifikasi = Notifikasi::forUser(Auth::id())
            ->orderByDesc('created_at')
            ->paginate(15);

        $unreadCount = Notifikasi::forUser(Auth::id())->unread()->count();

        return view('notifikasi.index', compact('notifikasi', 'unreadCount'));
    }

    public function recent()
    {
        $items = Notifikasi::forUser(Auth::id())
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'judul'      => $n->judul,
                'pesan'      => $n->pesan,
                'tipe'       => $n->tipe,
                'is_read'    => $n->is_read,
                'data'       => $n->data,
                'waktu'      => $n->created_at->diffForHumans(),
                'created_at' => $n->created_at->toIso8601String(),
            ]);

        return response()->json(['data' => $items]);
    }

    public function unreadCount()
    {
        $count = Notifikasi::forUser(Auth::id())->unread()->count();
        return response()->json(['count' => $count]);
    }

    public function markAsRead($id)
    {
        Notifikasi::where('id_user', Auth::id())->findOrFail($id)->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notifikasi::forUser(Auth::id())->unread()->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }
}
