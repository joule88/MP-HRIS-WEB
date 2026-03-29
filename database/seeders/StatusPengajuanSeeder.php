<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusPengajuanSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['id_status' => 1, 'nama_status' => 'Pending'],
            ['id_status' => 2, 'nama_status' => 'Disetujui'],
            ['id_status' => 3, 'nama_status' => 'Ditolak'],
        ];

        foreach ($statuses as $status) {
            DB::table('status_pengajuan')->updateOrInsert(
                ['id_status' => $status['id_status']],
                ['nama_status' => $status['nama_status'], 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
