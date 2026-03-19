<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSignatureRequest;
use App\Models\TandaTangan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SignatureApiController extends Controller
{
    public function show()
    {
        $ttd = TandaTangan::where('id_user', Auth::id())
            ->active()
            ->first();

        if (!$ttd) {
            return ApiResponse::notFound('Belum ada tanda tangan.');
        }

        return ApiResponse::success([
            'id_tanda_tangan' => $ttd->id_tanda_tangan,
            'file_ttd' => asset('storage/' . $ttd->file_ttd),
            'is_active' => $ttd->is_active,
            'created_at' => $ttd->created_at,
        ]);
    }

    public function store(StoreSignatureRequest $request)
    {
        try {
            $base64 = $request->signature_data;

            if (str_contains($base64, ',')) {
                $base64 = explode(',', $base64)[1];
            }

            $imageData = base64_decode($base64);
            if ($imageData === false) {
                return ApiResponse::error('Data tanda tangan tidak valid.', 400);
            }

            $fileName = 'uploads/ttd/' . Auth::id() . '_' . time() . '.png';
            Storage::disk('public')->put($fileName, $imageData);

            TandaTangan::where('id_user', Auth::id())->update(['is_active' => false]);

            $ttd = TandaTangan::create([
                'id_user' => Auth::id(),
                'file_ttd' => $fileName,
                'is_active' => true,
            ]);

            return ApiResponse::success([
                'id_tanda_tangan' => $ttd->id_tanda_tangan,
                'file_ttd' => asset('storage/' . $ttd->file_ttd),
            ], 'Tanda tangan berhasil disimpan.', 201);

        } catch (\Exception $e) {
            return ApiResponse::error('Gagal menyimpan tanda tangan: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        $ttd = TandaTangan::where('id_user', Auth::id())
            ->where('id_tanda_tangan', $id)
            ->first();

        if (!$ttd) {
            return ApiResponse::notFound('Tanda tangan tidak ditemukan.');
        }

        if ($ttd->file_ttd && Storage::disk('public')->exists($ttd->file_ttd)) {
            Storage::disk('public')->delete($ttd->file_ttd);
        }

        $ttd->delete();

        return ApiResponse::success(null, 'Tanda tangan berhasil dihapus.');
    }
}
