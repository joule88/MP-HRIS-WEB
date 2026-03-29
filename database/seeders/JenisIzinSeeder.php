<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisIzinSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['id_jenis_izin' => 1, 'nama_izin' => 'Sakit'],
            ['id_jenis_izin' => 2, 'nama_izin' => 'Cuti'],
            ['id_jenis_izin' => 3, 'nama_izin' => 'Izin'],
        ];

        foreach ($types as $type) {
            DB::table('jenis_izin')->updateOrInsert(
                ['id_jenis_izin' => $type['id_jenis_izin']],
                ['nama_izin' => $type['nama_izin'], 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
