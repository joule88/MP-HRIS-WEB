<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class FixLegacyRolesSeeder extends Seeder
{
    public function run()
    {

        $roleAdmin = Role::firstOrCreate(['nama_role' => 'admin']);
        $rolePegawai = Role::firstOrCreate(['nama_role' => 'pegawai']);

        $oldAdmins = User::where('role', 'admin')->get();
        foreach ($oldAdmins as $user) {
            if (!$user->roles()->where('nama_role', 'admin')->exists()) {
                $user->roles()->attach($roleAdmin->id_role ?? $roleAdmin->id);
                $this->command->info("Migrated legacy admin: {$user->email}");
            }
        }

        $oldStaff = User::where('role', 'pegawai')->get();
        foreach ($oldStaff as $user) {
            if (!$user->roles()->where('nama_role', 'pegawai')->exists()) {
                $user->roles()->attach($rolePegawai->id_role ?? $rolePegawai->id);
                $this->command->info("Migrated legacy staff: {$user->email}");
            }
        }
    }
}
