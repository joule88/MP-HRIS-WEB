<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Panggil MasterDataSeeder terlebih dahulu untuk data dasar (Divisi, Jabatan, Kantor, Role, User Admin)
        $this->call([
            MasterDataSeeder::class,
        ]);

        // Tambahan data demo jika diperlukan (Opsional)
        $kantor = \App\Models\Kantor::first();
        $divisi = \App\Models\Divisi::first();
        $jabatan = \App\Models\Jabatan::where('nama_jabatan', 'Staff')->first() ?? \App\Models\Jabatan::first();

        $users = User::factory(5)->create([
            'id_kantor' => $kantor->id_kantor,
            'id_divisi' => $divisi->id_divisi,
            'id_jabatan' => $jabatan->id_jabatan,
        ]);

        $rolePegawai = \App\Models\Role::where('nama_role', 'pegawai')->first();
        if ($rolePegawai) {
            foreach ($users as $user) {
                $user->roles()->attach($rolePegawai->id_role);
            }
        }

        // Panggil seeder fitur lainnya
        $this->call([
            PermissionSeeder::class,
            PresensiStatusSeeder::class,
            PresensiSeeder::class,
            JenisIzinSeeder::class,
            StatusPengajuanSeeder::class,
            PengajuanIzinSeeder::class,
            SuperAdminSeeder::class, // Menambah super administrator dengan role admin
        ]);
    }
}
