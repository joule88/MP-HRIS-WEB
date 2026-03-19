<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisIzinSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('jenis_izin')->delete();

        DB::table('jenis_izin')->insert([

            ['id_jenis_izin' => 1, 'nama_izin' => 'Sakit'],
            ['id_jenis_izin' => 2, 'nama_izin' => 'Cuti'],
            ['id_jenis_izin' => 3, 'nama_izin' => 'Izin'],
        ]);
    }
}
