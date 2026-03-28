<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisPenguranganSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('jenis_pengurangan')->insertOrIgnore([
            ['id_pengurangan' => 1, 'nama_pengurangan' => 'Datang Terlambat'],
            ['id_pengurangan' => 2, 'nama_pengurangan' => 'Pulang Cepat (Biasa)'],
            ['id_pengurangan' => 3, 'nama_pengurangan' => 'Tidak Hadir (Alpha)'],
            ['id_pengurangan' => 4, 'nama_pengurangan' => 'Masuk Siang (Poin)'],
            ['id_pengurangan' => 5, 'nama_pengurangan' => 'Pulang Cepat (Poin)'],
        ]);
    }
}
