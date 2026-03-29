<?php

namespace Database\Seeders;

use App\Models\JenisKompensasi;
use Illuminate\Database\Seeder;

class JenisKompensasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kompensasi = [
            ['id_kompensasi' => 1, 'nama_kompensasi' => 'Uang Lembur'],
            ['id_kompensasi' => 2, 'nama_kompensasi' => 'Tambahan Poin'],
        ];

        foreach ($kompensasi as $k) {
            JenisKompensasi::updateOrCreate(
                ['id_kompensasi' => $k['id_kompensasi']],
                ['nama_kompensasi' => $k['nama_kompensasi']]
            );
        }
    }
}
