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

        $divIT = Divisi::create(['nama_divisi' => 'Information Technology']);
        $divHR = Divisi::create(['nama_divisi' => 'Human Resources']);
        Divisi::create(['nama_divisi' => 'Finance & Accounting']);
        Divisi::create(['nama_divisi' => 'Marketing']);

        $jabMgr = Jabatan::create(['nama_jabatan' => 'Manager']);
        $jabSpv = Jabatan::create(['nama_jabatan' => 'Supervisor']);
        $jabStf = Jabatan::create(['nama_jabatan' => 'Staff']);
        Jabatan::create(['nama_jabatan' => 'Internship']);

        $kPusat = Kantor::create([
            'nama_kantor' => 'Kantor Pusat',
            'alamat' => 'Jl. Jendral Sudirman No. 1, Jakarta',
            'tipe' => 'Pusat'
        ]);

        $kCabang = Kantor::create([
            'nama_kantor' => 'Curah Manis Studio',
            'alamat' => 'Jl. Curah Manis, Malang',
            'tipe' => 'Cabang'
        ]);

        User::create([
            'nama_lengkap' => 'Super Administrator',
            'email' => 'admin@mpg.co.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'nik' => '999999999',
            'status_aktif' => 1,

            'id_divisi' => $divIT->id_divisi,
            'id_jabatan' => $jabMgr->id_jabatan,
            'id_kantor' => $kPusat->id_kantor,
            'tgl_bergabung' => now(),
        ]);

        User::create([
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@mpg.co.id',
            'password' => Hash::make('Mpg123!'),
            'role' => 'pegawai',
            'nik' => '350712345678',
            'status_aktif' => 1,
            'id_divisi' => $divHR->id_divisi,
            'id_jabatan' => $jabStf->id_jabatan,
            'id_kantor' => $kCabang->id_kantor,
            'tgl_bergabung' => now(),
        ]);
    }
}
