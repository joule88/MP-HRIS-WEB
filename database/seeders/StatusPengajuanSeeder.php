<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusPengajuanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('status_pengajuan')->delete();

        DB::table('status_pengajuan')->insert([

            ['id_status' => 1, 'nama_status' => 'Disetujui'],
            ['id_status' => 2, 'nama_status' => 'Pending'],
            ['id_status' => 3, 'nama_status' => 'Ditolak'],
        ]);
    }
}
