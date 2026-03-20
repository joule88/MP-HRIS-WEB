<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * SuperAdminSeeder sudah tidak digunakan.
 * Fungsinya telah dipindahkan ke MasterDataSeeder (user HRD dibuat di sana).
 * File ini dipertahankan agar tidak ada error jika masih dirujuk,
 * namun tidak melakukan apa-apa.
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('SuperAdminSeeder: tidak melakukan apa-apa (lihat MasterDataSeeder).');
    }
}
