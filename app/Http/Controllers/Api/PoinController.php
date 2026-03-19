<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Poin;
use App\Models\PenggunaanPoin;
use App\Services\PoinService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PoinController extends Controller
{
    public function getExpiringPoints()
    {
        try {
            $userId = auth()->user()->id;

            $poinService = new PoinService();
            $poinExpiring = $poinService->getExpiringPoints($userId);
            $activePoints = $poinService->getActivePoints($userId);

            $firstExpiring = $poinExpiring->first();
            $formattedExpiring = null;

            if ($firstExpiring) {
                $formattedExpiring = [
                    'poin' => (int) $firstExpiring->sisa_poin,
                    'tanggal_kadaluarsa' => $firstExpiring->expired_at->format('Y-m-d'),
                ];
            }

            return ApiResponse::success([
                'expiring_points' => $formattedExpiring,
                'total_poin' => (int) $activePoints
            ]);

        } catch (\Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function getPointHistory()
    {
        try {
            $userId = auth()->user()->id;

            $poinService = new PoinService();
            $history = $poinService->getPointHistory($userId);

            return ApiResponse::success(['history' => $history]);

        } catch (\Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function redeem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'required|string',
            'id_pengurangan' => 'required|integer',
            'jam_masuk_custom' => 'required_if:id_pengurangan,4|date_format:H:i|nullable',
            'jam_pulang_custom' => 'required_if:id_pengurangan,5|date_format:H:i|nullable',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $userId = auth()->user()->id;
            $jumlah = $request->jumlah;
            $keterangan = $request->keterangan;

            $poinService = new PoinService();
            $totalPoin = $poinService->getActivePoints($userId);

            if ($totalPoin < $jumlah) {
                return ApiResponse::error('Saldo poin tidak mencukupi', 400);
            }

            preg_match('/\[(\d{4}-\d{2}-\d{2})\]/', $keterangan, $matches);
            $tanggalPenggunaan = isset($matches[1]) ? $matches[1] : now()->format('Y-m-d');

            PenggunaanPoin::create([
                'id_user' => $userId,
                'tanggal_penggunaan' => $tanggalPenggunaan,
                'jumlah_poin' => $jumlah,
                'id_pengurangan' => $request->id_pengurangan,
                'jam_masuk_custom' => $request->jam_masuk_custom,
                'jam_pulang_custom' => $request->jam_pulang_custom,
                'id_status' => 1,
                'tanggal_diajukan' => now(),
            ]);

            return ApiResponse::success(null, 'Pengajuan penukaran poin berhasil disimpan');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}
