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
            ['id_pengurangan' => 2, 'nama_pengurangan' => 'Pulang Cepat'],
        ]);
    }
}
