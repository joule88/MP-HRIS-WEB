<?php

namespace App\Services;

use App\Enums\JenisKompensasi as JenisKompensasiEnum;
use App\Enums\StatusPengajuan as StatusPengajuanEnum;
use App\Models\Lembur;
use App\Models\PoinLembur;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LemburService
{
    public function createLembur($user, array $data)
    {
        if (!isset($data['durasi_menit'])) {
            $start = Carbon::parse($data['jam_mulai']);
            $end   = Carbon::parse($data['jam_selesai']);

            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }

            $diff = $start->diffInMinutes($end);
        } else {
            $diff = $data['durasi_menit'];
        }

        return Lembur::create([
            'id_user' => $user->id,
            'tanggal_lembur' => $data['tanggal_lembur'],
            'jam_mulai' => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
            'durasi_menit' => $diff,
            'keterangan' => $data['keterangan'] ?? null,
            'id_status' => StatusPengajuanEnum::PENDING,
            'id_kompensasi' => $data['id_kompensasi'] ?? null,
            'tanggal_diajukan' => Carbon::now(),
            'jumlah_poin' => 0,
        ]);
    }

    public function approve(Lembur $lembur)
    {
        if ($lembur->id_status != StatusPengajuanEnum::PENDING) {
            throw new \Exception('Lembur ini sudah diproses sebelumnya.');
        }

        return DB::transaction(function () use ($lembur) {
            $lembur->update([
                'id_status' => StatusPengajuanEnum::DISETUJUI
            ]);

            if ($lembur->id_kompensasi == JenisKompensasiEnum::TAMBAHAN_POIN) {
                $this->generatePoin($lembur);
            }

            return true;
        });
    }

    public function reject(Lembur $lembur, string $alasan)
    {
        if ($lembur->id_status != StatusPengajuanEnum::PENDING) {
            throw new \Exception('Lembur ini sudah diproses sebelumnya.');
        }

        return $lembur->update([
            'id_status' => StatusPengajuanEnum::DITOLAK,
            'alasan_penolakan' => $alasan
        ]);
    }

    private function generatePoin(Lembur $lembur)
    {
        $poinDapat = floor($lembur->durasi_menit / 30);

        if ($poinDapat > 0) {
            PoinLembur::create([
                'id_user' => $lembur->id_user,
                'jumlah_poin' => $poinDapat,
                'sisa_poin' => $poinDapat,
                'id_lembur' => $lembur->id_lembur,
                'keterangan' => 'Poin dari Lembur Tanggal ' . Carbon::parse($lembur->tanggal_lembur)->format('d/m/Y'),
                'tanggal' => now(),
                'expired_at' => now()->addMonths(6),
                'is_fully_used' => false
            ]);

            $lembur->update(['jumlah_poin' => $poinDapat]);
        }
    }
}