<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusPengajuanSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['Disetujui', 'Pending', 'Ditolak'];
        foreach ($statuses as $status) {
            DB::table('status_pengajuan')->updateOrInsert(
                ['nama_status' => $status],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
