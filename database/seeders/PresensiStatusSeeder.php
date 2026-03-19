<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PresensiStatusSeeder extends Seeder
{

    public function run(): void
    {

        DB::table('status_presensi')->insertOrIgnore([
            ['id_status' => 1, 'nama_status' => 'Tepat Waktu', 'created_at' => now(), 'updated_at' => now()],
            ['id_status' => 2, 'nama_status' => 'Terlambat', 'created_at' => now(), 'updated_at' => now()],
            ['id_status' => 3, 'nama_status' => 'Izin', 'created_at' => now(), 'updated_at' => now()],
            ['id_status' => 4, 'nama_status' => 'Sakit', 'created_at' => now(), 'updated_at' => now()],
            ['id_status' => 5, 'nama_status' => 'Alpha', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->command->info('Tabel status_presensi berhasil diisi!');
    }
}
