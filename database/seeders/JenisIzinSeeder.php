<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisIzinSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Sakit', 'Cuti', 'Izin'];
        foreach ($types as $type) {
            DB::table('jenis_izin')->updateOrInsert(
                ['nama_izin' => $type],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
