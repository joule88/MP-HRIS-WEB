<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{

    public function run(): void
    {

        $permissions = [

            'view_dashboard',

            'manage_divisi',
            'manage_jabatan',
            'manage_kantor',
            'manage_shift',
            'manage_hari_libur',

            'view_pegawai',
            'create_pegawai',
            'edit_pegawai',
            'delete_pegawai',
            'view_pegawai_detail',

            'view_presensi_all',
            'view_presensi_team',
            'view_presensi_self',
            'create_presensi',
            'approve_presensi',
            'correction_presensi',

            'view_izin_all',
            'view_izin_team',
            'create_izin',
            'approve_izin',

            'view_lembur_all',
            'view_lembur_team',
            'create_lembur',
            'approve_lembur',

            'manage_face_data',

            'view_laporan',

            'manage_users',
            'manage_roles',
            'manage_permissions',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(
                ['nama_permission' => $p],
                ['slug' => $p]
            );
        }

        $adminRole = Role::where('nama_role', 'admin')->first();
        if ($adminRole) {
            $allPermissions = Permission::all();
            $adminRole->permissions()->sync($allPermissions);
        }

        $hrdRole = Role::where('nama_role', 'hrd')->first();
        if ($hrdRole) {
            $hrdPermissions = Permission::whereIn('nama_permission', [
                'view_dashboard',
                'manage_divisi', 'manage_jabatan', 'manage_kantor', 'manage_shift', 'manage_hari_libur',
                'view_pegawai', 'create_pegawai', 'edit_pegawai', 'view_pegawai_detail',
                'view_presensi_all', 'approve_presensi', 'correction_presensi',
                'view_izin_all', 'approve_izin',
                'view_lembur_all', 'approve_lembur',
                'manage_face_data',
                'view_laporan'
            ])->get();
            $hrdRole->permissions()->sync($hrdPermissions);
        }

        $managerRole = Role::where('nama_role', 'manager')->first();
        if ($managerRole) {
            $managerPermissions = Permission::whereIn('nama_permission', [
                'view_dashboard',
                'view_pegawai', 'view_pegawai_detail',
                'view_presensi_team',
                'view_izin_team', 'approve_izin',
                'view_lembur_team', 'approve_lembur'
            ])->get();
            $managerRole->permissions()->sync($managerPermissions);
        }

        $pegawaiRole = Role::where('nama_role', 'pegawai')->first();
        if ($pegawaiRole) {
            $pegawaiPermissions = Permission::whereIn('nama_permission', [
                'view_dashboard',
                'view_presensi_self',
                'create_presensi',
                'create_izin',
                'create_lembur'
            ])->get();
            $pegawaiRole->permissions()->sync($pegawaiPermissions);
        }
    }
}
