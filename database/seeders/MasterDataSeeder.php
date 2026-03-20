<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Kantor;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
        $divIT = Divisi::firstOrCreate(['nama_divisi' => 'Information Technology']);
        $divHR = Divisi::firstOrCreate(['nama_divisi' => 'Human Resources']);
        Divisi::firstOrCreate(['nama_divisi' => 'Finance & Accounting']);
        Divisi::firstOrCreate(['nama_divisi' => 'Marketing']);

        $jabMgr = Jabatan::firstOrCreate(['nama_jabatan' => 'Manager']);
        $jabSpv = Jabatan::firstOrCreate(['nama_jabatan' => 'Supervisor']);
        $jabStf = Jabatan::firstOrCreate(['nama_jabatan' => 'Staff']);
        Jabatan::firstOrCreate(['nama_jabatan' => 'Internship']);

        $kPusat = Kantor::firstOrCreate(
            ['nama_kantor' => 'Kantor Pusat'],
            [
                'alamat'    => 'Jl. Jendral Sudirman No. 1, Jakarta',
                'latitude'  => -6.2088,
                'longitude' => 106.8456,
                'radius'    => 200
            ]
        );

        $kCabang = Kantor::firstOrCreate(
            ['nama_kantor' => 'Curah Manis Studio'],
            [
                'alamat'    => 'Jl. Curah Manis, Malang',
                'latitude'  => -7.9839,
                'longitude' => 112.6214,
                'radius'    => 200
            ]
        );

        $roleHrd        = \App\Models\Role::firstOrCreate(['nama_role' => 'hrd']);
        $roleManager    = \App\Models\Role::firstOrCreate(['nama_role' => 'manager']);
        $roleSupervisor = \App\Models\Role::firstOrCreate(['nama_role' => 'supervisor']);
        $roleStaff      = \App\Models\Role::firstOrCreate(['nama_role' => 'staff']);

        $hrd = User::updateOrCreate(
            ['nik' => '999999999'],
            [
                'nama_lengkap'  => 'HRD Manager',
                'email'         => 'hrd@mpg.co.id',
                'password'      => Hash::make('Mpg123!'),
                'status_aktif'  => 1,
                'id_divisi'     => $divHR->id_divisi,
                'id_jabatan'    => $jabMgr->id_jabatan,
                'id_kantor'     => $kPusat->id_kantor,
                'tgl_bergabung' => now(),
                'sisa_cuti'     => 12,
            ]
        );
        if (!$hrd->roles()->where('nama_role', 'hrd')->exists()) {
            $hrd->roles()->attach($roleHrd->id_role);
        }

        $pegawai = User::updateOrCreate(
            ['email' => 'budi@mpg.co.id'],
            [
                'nama_lengkap'  => 'Budi Santoso',
                'password'      => Hash::make('Mpg123!'),
                'nik'           => '350712345678',
                'status_aktif'  => 1,
                'id_divisi'     => $divHR->id_divisi,
                'id_jabatan'    => $jabStf->id_jabatan,
                'id_kantor'     => $kCabang->id_kantor,
                'tgl_bergabung' => now(),
                'sisa_cuti'     => 12,
            ]
        );
        if (!$pegawai->roles()->where('nama_role', 'staff')->exists()) {
            $pegawai->roles()->attach($roleStaff->id_role);
        }
    }
}
