<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PresensiStatusSeeder extends Seeder
{

    public function run(): void
    {
        $statuses = [
            ['id_status' => 1, 'nama_status' => 'Tepat Waktu'],
            ['id_status' => 2, 'nama_status' => 'Terlambat'],
            ['id_status' => 3, 'nama_status' => 'Izin'],
            ['id_status' => 4, 'nama_status' => 'Sakit'],
            ['id_status' => 5, 'nama_status' => 'Alpha'],
        ];

        foreach ($statuses as $status) {
            DB::table('status_presensi')->updateOrInsert(
                ['id_status' => $status['id_status']],
                ['nama_status' => $status['nama_status'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $validasiStatuses = [
            ['id_status' => 1, 'nama_status' => 'Valid'],
            ['id_status' => 2, 'nama_status' => 'Pending'],
            ['id_status' => 3, 'nama_status' => 'Ditolak'],
        ];

        foreach ($validasiStatuses as $status) {
            DB::table('status_validasi_presensi')->updateOrInsert(
                ['id_status' => $status['id_status']],
                ['nama_status' => $status['nama_status'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('Tabel status_presensi & status_validasi_presensi berhasil diisi!');
    }
}
