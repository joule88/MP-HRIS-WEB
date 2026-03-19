<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Str;

class PengajuanIzinSeeder extends Seeder
{
    public function run(): void
    {

        $users = User::inRandomOrder()->limit(5)->get();

        if ($users->isEmpty()) {
            $this->command->warn('Tidak ada user untuk di-seed.');
            return;
        }

        foreach ($users as $user) {
            try {

                $idJenis = rand(1, 3);

                $idStatus = rand(1, 3);

                $tglMulai = now()->addDays(rand(-10, 10));
                $tglSelesai = (clone $tglMulai)->addDays(rand(1, 3));

                DB::table('pengajuan_izin')->insertOrIgnore([
                    'id_user' => $user->id,
                    'id_jenis_izin' => $idJenis,
                    'tanggal_mulai' => $tglMulai->format('Y-m-d'),
                    'tanggal_selesai' => $tglSelesai->format('Y-m-d'),
                    'alasan' => 'Seeding Dummy Auto: ' . Str::random(10),
                    'bukti_file' => 'https://placehold.co/600x800/png?text=Bukti+Dummy',
                    'id_status' => $idStatus,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            } catch (\Exception $e) {
                $this->command->error('Gagal seed user ' . $user->id . ': ' . $e->getMessage());
            }
        }

        $this->command->info('Data Pengajuan Izin (FIXED) berhasil dibuat!');
    }
}
