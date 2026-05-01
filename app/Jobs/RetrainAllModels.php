<?php

namespace App\Jobs;

use App\Enums\StatusVerifikasiWajah;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RetrainAllModels implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $uniqueFor = 300;

    public function handle(): void
    {
        Log::info("Memulai training model SVM multi-class global...");

        $approvedUsers = DB::table('data_wajah')
            ->where('is_verified', StatusVerifikasiWajah::APPROVED)
            ->pluck('id_user')
            ->toArray();

        if (count($approvedUsers) === 0) {
            Log::info("Tidak ada user approved. Skip training.");
            return;
        }

        $approvedStr = array_map('strval', $approvedUsers);

        try {
            $response = Http::timeout(600)
                ->withHeaders([
                    'X-API-Key' => config('services.flask.api_key'),
                ])
                ->post(config('services.flask.url') . '/train-model', [
                    'approved_user_ids' => $approvedStr,
                ]);

            if (!$response->successful()) {
                throw new \Exception("Flask API error: HTTP {$response->status()} - " . $response->body());
            }

            $output = $response->json();

            if (!isset($output['status']) || $output['status'] !== 'success') {
                throw new \Exception("Training error: " . ($output['message'] ?? 'Unknown'));
            }

            $totalUsers = $output['total_users'] ?? 0;
            Log::info("Training model global selesai. Total user: {$totalUsers}");

            app(\App\Services\NotifikasiService::class)->kirimKeRole(
                'hrd',
                'pengumuman',
                'Training Model Selesai',
                "Sistem telah berhasil memperbarui model AI Face Recognition untuk {$totalUsers} karyawan."
            );

        } catch (\Exception $e) {
            Log::error("Training model global GAGAL: " . $e->getMessage());
            
            app(\App\Services\NotifikasiService::class)->kirimKeRole(
                'hrd',
                'pengumuman',
                'Training Model Gagal',
                'Terjadi kesalahan saat melatih ulang model AI. Silakan cek log server.'
            );
        }
    }
}
