<?php

namespace App\Services;

use App\Enums\StatusPengajuan;
use App\Models\PoinLembur;
use App\Models\DetailPenggunaanPoin;
use App\Models\PenggunaanPoin;
use Illuminate\Support\Facades\DB;
use Exception;

class PoinService
{
    public function deductPoin($userId, $jumlahPoinDibutuhkan, $idPenggunaan)
    {
        $availablePoins = PoinLembur::where('id_user', $userId)
            ->where('is_fully_used', false)
            ->where('expired_at', '>', now())
            ->orderBy('expired_at', 'asc')
            ->lockForUpdate()
            ->get();

        $totalAvailable = $availablePoins->sum('sisa_poin');

        if ($totalAvailable < $jumlahPoinDibutuhkan) {
            throw new Exception("Saldo poin tidak mencukupi. Anda butuh $jumlahPoinDibutuhkan, saldo tersedia $totalAvailable.");
        }

        $remainingNeeded = $jumlahPoinDibutuhkan;

        foreach ($availablePoins as $poin) {
            if ($remainingNeeded <= 0)
                break;

            $take = min($poin->sisa_poin, $remainingNeeded);

            $poin->sisa_poin -= $take;
            if ($poin->sisa_poin == 0) {
                $poin->is_fully_used = true;
            }
            $poin->save();

            DetailPenggunaanPoin::create([
                'id_penggunaan' => $idPenggunaan,
                'id_poin_sumber' => $poin->id_poin,
                'jumlah_diambil' => $take
            ]);

            $remainingNeeded -= $take;
        }
    }

    public function refundPoin($idPenggunaan)
    {
        $details = DetailPenggunaanPoin::where('id_penggunaan', $idPenggunaan)->get();

        foreach ($details as $detail) {
            $poinSumber = PoinLembur::find($detail->id_poin_sumber);
            if ($poinSumber) {
                $poinSumber->sisa_poin += $detail->jumlah_diambil;
                $poinSumber->is_fully_used = false;
                if ($poinSumber->expired_at < now()) {
                    $poinSumber->expired_at = now()->addDays(7);
                }
                $poinSumber->save();
            }
        }

        DetailPenggunaanPoin::where('id_penggunaan', $idPenggunaan)->delete();
    }

    public function getActivePoints($userId)
    {
        return PoinLembur::where('id_user', $userId)
            ->where('is_fully_used', false)
            ->where('expired_at', '>', now())
            ->sum('sisa_poin');
    }

    public function getExpiringPoints($userId)
    {
        return PoinLembur::where('id_user', $userId)
            ->where('is_fully_used', false)
            ->whereBetween('expired_at', [now(), now()->addDays(30)])
            ->orderBy('expired_at', 'asc')
            ->get();
    }

    public function getPointHistory($userId)
    {
        return PenggunaanPoin::with(['status', 'jenisPengurangan'])
            ->where('id_user', $userId)
            ->orderBy('tanggal_penggunaan', 'desc')
            ->get();
    }
}