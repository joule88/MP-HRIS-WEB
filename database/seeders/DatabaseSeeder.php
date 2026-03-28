<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Data master: Role, Divisi, Jabatan, Kantor, User HRD & contoh staff
            MasterDataSeeder::class,

            // 2. Permission per role
            PermissionSeeder::class,

            // 3. Data lookup / referensi
            PresensiStatusSeeder::class,
            JenisIzinSeeder::class,
            StatusPengajuanSeeder::class,
            JenisPenguranganSeeder::class,

            // 4. Data dummy (opsional, comment jika tidak diperlukan di production)
            // PresensiSeeder::class,
            // PengajuanIzinSeeder::class,
        ]);
    }
}
