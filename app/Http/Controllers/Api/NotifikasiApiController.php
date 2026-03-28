<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\Notifikasi;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiApiController extends Controller
{
    public function index(Request $request)
    {
        $notifikasi = Notifikasi::forUser(Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return ApiResponse::success($notifikasi);
    }

    public function unreadCount()
    {
        $count = Notifikasi::forUser(Auth::id())->unread()->count();
        return ApiResponse::success(['count' => $count]);
    }

    public function markAsRead($id)
    {
        $notif = Notifikasi::where('id_user', Auth::id())->findOrFail($id);
        $notif->update(['is_read' => true]);
        return ApiResponse::success(null, 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllAsRead()
    {
        Notifikasi::forUser(Auth::id())->unread()->update(['is_read' => true]);
        return ApiResponse::success(null, 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function saveDeviceToken(Request $request)
    {
        $request->validate([
            'fcm_token'   => 'required|string',
            'device_type' => 'nullable|in:android,ios',
        ]);

        DeviceToken::updateOrCreate(
            ['fcm_token' => $request->fcm_token],
            [
                'id_user'     => Auth::id(),
                'device_type' => $request->device_type ?? 'android',
            ]
        );

        return ApiResponse::success(null, 'Device token berhasil disimpan.');
    }
}
