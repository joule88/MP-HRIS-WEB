<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MigrateExistingEmbeddings implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $uniqueFor = 300;

    public function handle(): void
    {
        Log::info("Memulai migrasi embedding untuk data wajah yang sudah ada...");

        $users = DB::table('data_wajah')
            ->whereNotNull('path_video')
            ->whereNull('face_embeddings')
            ->get();

        if ($users->isEmpty()) {
            Log::info("Tidak ada data wajah yang perlu dimigrasi.");
            app(\App\Services\NotifikasiService::class)->kirimKeRole(
                'hrd',
                'pengumuman',
                'Migrasi Embedding Selesai',
                'Semua data wajah sudah memiliki embedding. Tidak ada yang perlu dimigrasi.'
            );
            return;
        }

        Log::info("Ditemukan {$users->count()} user yang perlu dimigrasi.");

        $successCount = 0;
        $failCount = 0;

        foreach ($users as $data) {
            $userId = $data->id_user;
            $videoPath = Storage::disk('local')->path($data->path_video);

            if (!file_exists($videoPath)) {
                Log::warning("Migrasi: Video tidak ditemukan untuk user {$userId}: {$videoPath}");
                $failCount++;
                continue;
            }

            try {
                $response = Http::timeout(180)
                    ->withHeaders([
                        'X-API-Key' => config('services.flask.api_key'),
                    ])
                    ->attach(
                        'video',
                        fopen($videoPath, 'r'),
                        'enrollment.mp4'
                    )
                    ->post(config('services.flask.url') . '/extract-and-embed', [
                        'user_id' => (string) $userId,
                        'target_frames' => 200,
                    ]);

                if (!$response->successful()) {
                    throw new \Exception("Flask API error: HTTP {$response->status()} - " . $response->body());
                }

                $output = $response->json();

                if (!isset($output['status']) || $output['status'] !== 'success') {
                    throw new \Exception("Extract & embed error: " . ($output['message'] ?? 'Unknown'));
                }

                $embedding = $output['embedding'] ?? null;
                $jumlahFrame = $output['total_extracted'] ?? 0;

                if ($embedding && is_array($embedding)) {
                    DB::table('data_wajah')->where('id_user', $userId)->update([
                        'face_embeddings' => json_encode($embedding),
                        'jumlah_embedding' => $jumlahFrame,
                        'jumlah_frame' => $jumlahFrame,
                        'embedding_generated_at' => now(),
                    ]);

                    Log::info("Migrasi berhasil untuk user {$userId}. Embedding dimensi: " . count($embedding));
                    $successCount++;
                } else {
                    throw new \Exception("Embedding kosong dari Flask API.");
                }

            } catch (\Exception $e) {
                Log::error("Migrasi GAGAL untuk user {$userId}: " . $e->getMessage());
                $failCount++;
            }
        }

        Log::info("Migrasi embedding selesai. Berhasil: {$successCount}, Gagal: {$failCount}");

        app(\App\Services\NotifikasiService::class)->kirimKeRole(
            'hrd',
            'pengumuman',
            'Migrasi Embedding Selesai',
            "Proses migrasi data wajah ke sistem baru selesai. Berhasil: {$successCount}, Gagal: {$failCount}."
        );
    }
}
