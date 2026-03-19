<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PresensiStatusSeeder extends Seeder
{

    public function run(): void
    {
        $statuses = [
            'Tepat Waktu',
            'Terlambat',
            'Izin',
            'Sakit',
            'Alpha',
        ];

        foreach ($statuses as $status) {
            DB::table('status_presensi')->updateOrInsert(
                ['nama_status' => $status],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('Tabel status_presensi berhasil diisi!');
    }
}
