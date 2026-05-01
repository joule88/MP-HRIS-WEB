<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReextractAllFrames implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public function handle(): void
    {
        Log::info("Memulai re-extract frames untuk semua user...");

        $users = DB::table('data_wajah')
            ->whereNotNull('path_video')
            ->get();

        if ($users->isEmpty()) {
            Log::info("Tidak ada user dengan video. Skip re-extract.");
            return;
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($users as $data) {
            $userId = $data->id_user;
            $videoPath = Storage::disk('local')->path($data->path_video);

            if (!file_exists($videoPath)) {
                Log::warning("Video tidak ditemukan untuk user {$userId}: {$videoPath}");
                $failCount++;
                continue;
            }

            try {
                $response = Http::timeout(120)
                    ->withHeaders([
                        'X-API-Key' => config('services.flask.api_key'),
                    ])
                    ->attach(
                        'video',
                        fopen($videoPath, 'r'),
                        'enrollment.mp4'
                    )
                    ->post(config('services.flask.url') . '/extract-frames', [
                        'user_id' => (string) $userId,
                        'target_frames' => 200,
                    ]);

                if (!$response->successful()) {
                    throw new \Exception("Flask API error: HTTP {$response->status()} - " . $response->body());
                }

                $output = $response->json();

                if (!isset($output['status']) || $output['status'] !== 'success') {
                    throw new \Exception("Extract error: " . ($output['message'] ?? 'Unknown'));
                }

                $jumlahFrame = $output['total_extracted'] ?? 0;

                DB::table('data_wajah')->where('id_user', $userId)->update([
                    'jumlah_frame' => $jumlahFrame,
                ]);

                Log::info("Re-extract berhasil untuk user {$userId}. Frames: {$jumlahFrame}");
                $successCount++;

            } catch (\Exception $e) {
                Log::error("Re-extract GAGAL untuk user {$userId}: " . $e->getMessage());
                $failCount++;
            }
        }

        Log::info("Re-extract selesai. Berhasil: {$successCount}, Gagal: {$failCount}");

        app(\App\Services\NotifikasiService::class)->kirimKeRole(
            'hrd',
            'pengumuman',
            'Ekstraksi Wajah Selesai',
            "Proses re-extract video wajah selesai. Berhasil: {$successCount}, Gagal: {$failCount}."
        );

        if ($successCount > 0) {
            dispatch(new RetrainAllModels());
            Log::info("Training model global di-trigger setelah re-extract.");
        }
    }
}
