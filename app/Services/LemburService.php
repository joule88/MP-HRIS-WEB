<?php

namespace App\Services;

use App\Models\Lembur;
use App\Models\PoinLembur;
use App\Models\JenisKompensasi;
use App\Models\StatusPengajuan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LemburService
{
    public function createLembur($user, array $data)
    {
        if (!isset($data['durasi_menit'])) {
            $start = Carbon::parse($data['jam_mulai']);
            $end = Carbon::parse($data['jam_selesai']);
            $diff = abs($start->diffInMinutes($end));
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
            'id_status' => 1,
            'id_kompensasi' => $data['id_kompensasi'] ?? null,
            'tanggal_diajukan' => Carbon::now(),
            'jumlah_poin' => 0,
        ]);
    }

    public function approve(Lembur $lembur)
    {
        return DB::transaction(function () use ($lembur) {
            $lembur->update([
                'id_status' => StatusPengajuan::where('nama_status', 'Approved')->first()->id_status ?? 2
            ]);

            if ($lembur->id_kompensasi == JenisKompensasi::POIN) {
                $this->generatePoin($lembur);
            }

            return true;
        });
    }

    public function reject(Lembur $lembur, string $alasan)
    {
        return $lembur->update([
            'id_status' => StatusPengajuan::where('nama_status', 'Rejected')->first()->id_status ?? 3,
            'alasan_penolakan' => $alasan
        ]);
    }

    private function generatePoin(Lembur $lembur)
    {
        $poinDapat = floor(($lembur->durasi_menit + 15) / 30);

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