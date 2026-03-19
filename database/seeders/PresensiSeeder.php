<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Presensi;
use App\Models\JadwalKerja;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PresensiSeeder extends Seeder
{

    public function run(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        $jadwalHariIni = JadwalKerja::with('shift')
            ->where('tanggal', $today)
            ->get();

        if ($jadwalHariIni->isEmpty()) {
            $this->command->warn('Tidak ada jadwal kerja untuk tanggal ' . $today . '. Harap jalankan generator jadwal terlebih dahulu atau isi manual.');
            return;
        }

        $this->command->info('Ditemukan ' . $jadwalHariIni->count() . ' jadwal untuk hari ini via PresensiSeeder.');

        foreach ($jadwalHariIni as $jadwal) {
            try {
                if (!$jadwal->shift)
                    continue;

                $jamMasukShift = $jadwal->shift->jam_masuk ?? $jadwal->shift->jam_mulai;
                $jamPulangShift = $jadwal->shift->jam_pulang ?? $jadwal->shift->jam_selesai;

                $shiftStart = Carbon::parse($jadwal->tanggal . ' ' . $jamMasukShift);
                $shiftEnd = Carbon::parse($jadwal->tanggal . ' ' . $jamPulangShift);

                $randomMinutes = rand(-10, 30);
                $jamMasuk = $shiftStart->copy()->addMinutes($randomMinutes);

                $status = 1;
                $alasan = null;
                $toleransi = 10;

                if ($randomMinutes > $toleransi) {
                    $status = 2;
                    $alasan = 'Terlambat ' . ($randomMinutes) . ' menit (Dummy Data)';
                }

                $validasi = (rand(1, 10) > 2) ? 1 : 2;

                $lat = -8.172110 + (rand(-100, 100) / 100000);
                $lon = 113.700550 + (rand(-100, 100) / 100000);

                Presensi::firstOrCreate(
                    [
                        'id_user' => $jadwal->id_user,
                        'tanggal' => $today
                    ],
                    [
                        'jam_masuk' => $jamMasuk->format('H:i:s'),
                        'jam_pulang' => $shiftEnd->format('H:i:s'),
                        'lat_masuk' => $lat,
                        'lon_masuk' => $lon,
                        'lat_pulang' => $lat,
                        'lon_pulang' => $lon,
                        'foto_wajah_masuk' => 'https://placehold.co/400x400/png?text=Masuk',
                        'foto_wajah_pulang' => 'https://placehold.co/400x400/png?text=Pulang',
                        'id_status' => $status,
                        'alasan_telat' => $alasan,
                        'id_validasi' => $validasi,
                        'verifikasi_wajah' => 0
                    ]
                );

            } catch (\Exception $e) {
                $this->command->error('Gagal seed user ' . $jadwal->id_user . ': ' . $e->getMessage());
            }
        }

        $this->command->info('Presensi dummy berhasil dibuat!');
    }
}
