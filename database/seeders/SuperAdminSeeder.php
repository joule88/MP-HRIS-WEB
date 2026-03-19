<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Kantor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {

        $divisi = Divisi::first() ?? Divisi::create(['nama_divisi' => 'IT Dept']);
        $jabatan = Jabatan::first() ?? Jabatan::create(['nama_jabatan' => 'Super Admin']);
        $kantor = Kantor::first() ?? Kantor::create(['nama_kantor' => 'Headquarters', 'alamat' => 'Cloud', 'tipe' => 'Pusat']);

        $roleAdmin = Role::firstOrCreate(
            ['nama_role' => 'admin'],
            ['keterangan' => 'Administrator with Full Access']
        );

        $user = User::updateOrCreate(
            ['email' => 'superadmin@mpg.co.id'],
            [
                'nama_lengkap' => 'Super Administrator',
                'password' => Hash::make('password'),
                'nik' => '77777777',
                'no_telp' => '081234567890',
                'alamat' => 'Server Room',
                'status_aktif' => 1,
                'tgl_bergabung' => now(),
                'id_divisi' => $divisi->id_divisi ?? $divisi->id,
                'id_jabatan' => $jabatan->id_jabatan ?? $jabatan->id,
                'id_kantor' => $kantor->id_kantor ?? $kantor->id,
                'sisa_cuti' => 12,

            ]
        );

        if (!$user->roles()->where('nama_role', 'admin')->exists()) {
            $user->roles()->attach($roleAdmin->id_role ?? $roleAdmin->id);
            $this->command->info('Role admin attached to user.');
        }

        $this->command->info('Super Admin created successfully!');
        $this->command->info('Email: superadmin@mpg.co.id');
        $this->command->info('Password: password');
    }
}
