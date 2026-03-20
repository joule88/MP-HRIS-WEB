<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view_dashboard',

            // Data Master
            'manage_divisi',
            'manage_jabatan',
            'manage_kantor',
            'manage_shift',
            'manage_hari_libur',
            'manage_cuti',

            // Data Pegawai
            'view_pegawai',
            'create_pegawai',
            'edit_pegawai',
            'delete_pegawai',
            'view_pegawai_detail',

            // Penjadwalan
            'view_jadwal',
            'manage_jadwal',

            // Presensi
            'view_presensi_all',
            'view_presensi_team',
            'view_presensi_self',
            'create_presensi',
            'approve_presensi',
            'correction_presensi',

            // Izin
            'view_izin_all',
            'view_izin_team',
            'create_izin',
            'approve_izin',

            // Surat Izin
            'view_surat_izin',
            'approve_surat_izin',

            // Lembur
            'view_lembur_all',
            'view_lembur_team',
            'create_lembur',
            'approve_lembur',

            // Poin
            'manage_poin',

            // Laporan
            'view_laporan',        // laporan kehadiran
            'view_laporan_izin',   // laporan izin/cuti
            'view_laporan_lembur', // laporan lembur

            // Pengumuman
            'manage_pengumuman',

            // Face Recognition
            'manage_face_data',

            // Sistem
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

        // ─── HRD ─── akses penuh ke semua permission
        $hrdRole = Role::where('nama_role', 'hrd')->first();
        if ($hrdRole) {
            $hrdRole->permissions()->sync(Permission::all());
        }

        // ─── MANAGER ─── approve presensi & izin, lihat laporan kehadiran & izin
        $managerRole = Role::where('nama_role', 'manager')->first();
        if ($managerRole) {
            $perms = Permission::whereIn('nama_permission', [
                'view_dashboard',
                'view_pegawai', 'view_pegawai_detail',
                'view_jadwal',
                'view_presensi_team', 'approve_presensi',
                'view_izin_team', 'approve_izin',
                'view_surat_izin', 'approve_surat_izin',
                'view_laporan',
                'view_laporan_izin',
            ])->get();
            $managerRole->permissions()->sync($perms);
        }

        // ─── SUPERVISOR ─── approve presensi, lihat laporan kehadiran & penjadwalan
        $supervisorRole = Role::where('nama_role', 'supervisor')->first();
        if ($supervisorRole) {
            $perms = Permission::whereIn('nama_permission', [
                'view_dashboard',
                'view_jadwal',
                'view_presensi_team', 'approve_presensi',
                'view_laporan',
            ])->get();
            $supervisorRole->permissions()->sync($perms);
        }

        // ─── STAFF ─── hanya mobile (tidak ada view_dashboard = tidak bisa akses web)
        $staffRole = Role::where('nama_role', 'staff')->first();
        if ($staffRole) {
            $perms = Permission::whereIn('nama_permission', [
                'view_presensi_self',
                'create_presensi',
                'create_izin',
                'create_lembur',
            ])->get();
            $staffRole->permissions()->sync($perms);
        }
    }
}
