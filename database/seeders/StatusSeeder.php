<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    public function run(): void
    {

        DB::table('status_presensi')->delete();

        DB::table('status_presensi')->insert([
            ['id_status' => 1, 'nama_status' => 'Tepat Waktu', 'created_at' => now(), 'updated_at' => now()],
            ['id_status' => 2, 'nama_status' => 'Terlambat', 'created_at' => now(), 'updated_at' => now()],
            ['id_status' => 3, 'nama_status' => 'Izin', 'created_at' => now(), 'updated_at' => now()],
            ['id_status' => 4, 'nama_status' => 'Sakit', 'created_at' => now(), 'updated_at' => now()],
            ['id_status' => 5, 'nama_status' => 'Alpha', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('status_validasi_presensi')->delete();

        DB::table('status_validasi_presensi')->insert([
            ['id_status' => 1, 'nama_status' => 'Valid', 'created_at' => now(), 'updated_at' => now()],
            ['id_status' => 2, 'nama_status' => 'Pending', 'created_at' => now(), 'updated_at' => now()],
            ['id_status' => 3, 'nama_status' => 'Ditolak', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->command->info('Data Master Status berhasil dibuat!');
    }
}
