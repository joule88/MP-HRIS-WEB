<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Lembur;
use App\Models\PoinLembur;
use App\Models\JenisKompensasi;
use Carbon\Carbon;

class FixNegativeLemburDuration extends Seeder
{

    public function run(): void
    {
        echo "🔍 Mencari lembur dengan durasi negatif...\n";

        $lembursNegatif = Lembur::where('durasi_menit', '<', 0)->get();

        if ($lembursNegatif->isEmpty()) {
            echo "✅ Tidak ada lembur dengan durasi negatif.\n";
            return;
        }

        echo "⚠️  Ditemukan {$lembursNegatif->count()} lembur dengan durasi negatif.\n\n";

        foreach ($lembursNegatif as $lembur) {
            echo "📋 ID Lembur: {$lembur->id_lembur}\n";
            echo "   Durasi lama: {$lembur->durasi_menit} menit\n";

            $start = Carbon::parse($lembur->jam_mulai);
            $end = Carbon::parse($lembur->jam_selesai);
            $durasiBenar = abs($start->diffInMinutes($end));

            echo "   Durasi benar: {$durasiBenar} menit\n";

            $lembur->update(['durasi_menit' => $durasiBenar]);

            $statusApproved = DB::table('status_pengajuan')
                ->where('nama_status', 'Approved')
                ->first();

            if (
                $lembur->id_status == ($statusApproved->id_status ?? 2) &&
                $lembur->id_kompensasi == JenisKompensasi::POIN
            ) {

                echo "   🔄 Re-generating poin...\n";

                PoinLembur::where('id_lembur', $lembur->id_lembur)->delete();

                $poinBaru = floor($durasiBenar / 30);

                if ($poinBaru > 0) {
                    PoinLembur::create([
                        'id_user' => $lembur->id_user,
                        'jumlah_poin' => $poinBaru,
                        'sisa_poin' => $poinBaru,
                        'id_lembur' => $lembur->id_lembur,
                        'keterangan' => 'Poin dari Lembur Tanggal ' . Carbon::parse($lembur->tanggal_lembur)->format('d/m/Y') . ' (Diperbaiki)',
                        'tanggal' => now(),
                        'expired_at' => now()->addMonths(6),
                        'is_fully_used' => false
                    ]);

                    $lembur->update(['jumlah_poin' => $poinBaru]);

                    echo "   ✅ Poin berhasil di-generate: {$poinBaru} poin\n";
                } else {
                    echo "   ⚠️  Durasi terlalu singkat untuk mendapat poin\n";
                }
            }

            echo "\n";
        }

        echo "✅ Selesai! Total {$lembursNegatif->count()} lembur diperbaiki.\n";
    }
}
