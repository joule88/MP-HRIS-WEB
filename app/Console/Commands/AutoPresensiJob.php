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
        // Target H-1 (Kemarin) untuk mencegah the Midnight Bug
        $hariIni = Carbon::yesterday('Asia/Jakarta')->format('Y-m-d');
        // Target H-2 untuk sweeping pekerja shift malam
        $hariLusa = Carbon::now('Asia/Jakarta')->subDays(2)->format('Y-m-d');

        // Dapatkan jadwal H-1
        $jadwalH1 = JadwalKerja::where('tanggal', $hariIni)
            ->with(['shift', 'user'])
            ->get();
            
        // Dapatkan jadwal H-2
        $jadwalH2 = JadwalKerja::where('tanggal', $hariLusa)
            ->with(['shift', 'user'])
            ->get();

        $usersPresensiH1 = Presensi::where('tanggal', $hariIni)->pluck('id_user');
        $usersH1 = $jadwalH1->pluck('id_user');
        $usersAlphaH1 = $usersH1->diff($usersPresensiH1);

        $idAlpha = DB::table('status_presensi')->where('nama_status', 'Alpha')->value('id_status') ?? 0;

        // 1. Pegawai H-1 yang sama sekali tidak absen masuk
        foreach ($usersAlphaH1 as $userId) {
            Presensi::create([
                'id_user' => $userId,
                'tanggal' => $hariIni,
                'id_status' => $idAlpha,
                'id_validasi' => 1,
                'alasan_telat' => 'Auto Alpha by System'
            ]);
        }

        $this->info('Auto Alpha processed for ' . $usersAlphaH1->count() . ' users (no clock in H-1).');

        // 2. Sweeping pegawai yang lupa absen pulang pada H-1
        $incompleteH1 = Presensi::where('tanggal', $hariIni)
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->get();

        $countIncomplete = 0;

        foreach ($incompleteH1 as $presensi) {
            $userJadwal = $jadwalH1->where('id_user', $presensi->id_user)->first();
            $isNightShift = false;
            
            if ($userJadwal && $userJadwal->shift) {
                if ($userJadwal->shift->jam_selesai < $userJadwal->shift->jam_mulai || stripos($userJadwal->shift->nama_shift, 'Malam') !== false) {
                    $isNightShift = true;
                }
            }

            // Eksekusi jika bukan shift malam
            if (!$isNightShift) {
                $presensi->update([
                    'id_status' => $idAlpha,
                    'id_validasi' => 1,
                    'alasan_telat' => 'Auto Alpha: Lupa Absen Pulang (Shift Normal)'
                ]);
                $countIncomplete++;
            }
        }

        // 3. Sweeping pekerja Shift Malam yang lupa absen pulang pada H-2
        $incompleteH2 = Presensi::where('tanggal', $hariLusa)
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->get();

        foreach ($incompleteH2 as $presensi) {
            $userJadwal = $jadwalH2->where('id_user', $presensi->id_user)->first();
            $isNightShift = false;
            
            if ($userJadwal && $userJadwal->shift) {
                if ($userJadwal->shift->jam_selesai < $userJadwal->shift->jam_mulai || stripos($userJadwal->shift->nama_shift, 'Malam') !== false) {
                    $isNightShift = true;
                }
            }

            // Eksekusi HANYA jika ia benar shift malam
            if ($isNightShift) {
                $presensi->update([
                    'id_status' => $idAlpha,
                    'id_validasi' => 1,
                    'alasan_telat' => 'Auto Alpha: Lupa Absen Pulang (Shift Malam)'
                ]);
                $countIncomplete++;
            }
        }

        $this->info("Auto Alpha updated for $countIncomplete users (missing clock out with Night Shift Protection).");

        // 4. Cleanup old photos
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
