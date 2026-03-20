<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class FixLegacyRolesSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Tidak ada migrasi legacy yang perlu dilakukan (sistem sudah menggunakan pivot roles).');
    }
}
