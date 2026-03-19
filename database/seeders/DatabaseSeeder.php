<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        DB::table('lokasi_kantor')->insert([
            [
                'nama_kantor' => 'Notis Digital',
                'alamat' => 'Jln. Kalimantan No. 1, Jember',
                'latitude' => -8.172110,
                'longitude' => 113.700550,
                'radius_meter' => 200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kantor' => 'Curah Manis Studio',
                'alamat' => 'Jln. Mastrip, Jember',
                'latitude' => -8.172110,
                'longitude' => 113.700550,
                'radius_meter' => 200,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        User::factory(10)->create([
            'id_kantor' => 1,
        ]);

        User::factory(15)->create([
            'id_kantor' => 2,
        ]);

        User::factory()->create([
            'nama_lengkap' => 'Julian (Super Admin)',
            'email' => 'julian@admin.com',
            'password' => bcrypt('password'),
            'divisi' => 'IT',
            'jabatan' => 'Manager',
            'id_kantor' => 1,
        ]);

        $this->call([
            MasterDataSeeder::class,
            PermissionSeeder::class,
            PresensiStatusSeeder::class,
            PresensiSeeder::class,

            JenisIzinSeeder::class,
            StatusPengajuanSeeder::class,

            PengajuanIzinSeeder::class,
        ]);
    }
}
