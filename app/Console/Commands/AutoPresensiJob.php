<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Presensi;
use App\Models\JadwalKerja;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AutoPresensiJob extends Command
{
    /**
     * Nama dan signature dari perintah console.
     *
     * @var string
     */
    protected $signature = 'presensi:auto-alpha';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis set Alpha untuk pegawai yang tidak absen & hapus foto lama';

    public function handle()
    {
        $hariIni = Carbon::today()->format('Y-m-d');

        $usersWithJadwal = JadwalKerja::where('tanggal', $hariIni)
            ->with('user')
            ->get()
            ->pluck('id_user');

        $usersPresensi = Presensi::where('tanggal', $hariIni)->pluck('id_user');

        $usersAlpha = $usersWithJadwal->diff($usersPresensi);

        $idAlpha = DB::table('status_presensi')->where('nama_status', 'Alpha')->value('id_status') ?? 0;

        foreach ($usersAlpha as $userId) {
            Presensi::create([
                'id_user' => $userId,
                'tanggal' => $hariIni,
                'id_status' => $idAlpha,
                'id_validasi' => 1,
                'alasan_telat' => 'Auto Alpha by System'
            ]);
        }

        $this->info('Auto Alpha processed for ' . $usersAlpha->count() . ' users.');

        $dateLimit = Carbon::now()->subMonths(3);

        $oldPresensi = Presensi::where('tanggal', '<', $dateLimit)->get();

        $countDeleted = 0;
        foreach ($oldPresensi as $p) {

            if ($p->foto_wajah_masuk && Storage::disk('public')->exists($p->foto_wajah_masuk)) {
                Storage::disk('public')->delete($p->foto_wajah_masuk);
                $p->foto_wajah_masuk = null;
            }

            if ($p->foto_wajah_pulang && Storage::disk('public')->exists($p->foto_wajah_pulang)) {
                Storage::disk('public')->delete($p->foto_wajah_pulang);
                $p->foto_wajah_pulang = null;
            }

            $p->save();
            $countDeleted++;
        }

        $this->info("Cleanup photos completed. Processed $countDeleted records.");
    }
}
