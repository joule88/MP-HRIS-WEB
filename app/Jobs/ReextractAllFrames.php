<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
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
        Log::info("Memulai sync & retrain untuk semua user...");

        $users = DB::table('data_wajah')
            ->whereNotNull('path_video')
            ->get();

        if ($users->isEmpty()) {
            Log::info("Tidak ada user dengan video. Skip sync.");
            Cache::put('face_training_status', ['phase' => 'idle'], 60);
            return;
        }

        $extractCount = 0;
        $skipCount = 0;
        $failCount = 0;
        $total = $users->count();
        $current = 0;

        Cache::put('face_training_status', [
            'phase' => 'extracting',
            'current' => 0,
            'total' => $total,
            'message' => "Mengecek frames 0/{$total} user...",
        ], 3600);

        foreach ($users as $data) {
            $userId = $data->id_user;
            $current++;

            $frameDir = Storage::disk('local')->path("face_datasets/{$userId}");
            $existingFrames = 0;

            if (is_dir($frameDir)) {
                $existingFrames = count(glob($frameDir . '/frame_*.jpg'));
            }

            if ($existingFrames >= 50) {
                Log::info("User {$userId}: sudah punya {$existingFrames} frame. Skip extract.");
                $skipCount++;

                Cache::put('face_training_status', [
                    'phase' => 'extracting',
                    'current' => $current,
                    'total' => $total,
                    'message' => "User {$current}/{$total}: skip (sudah ada {$existingFrames} frame)",
                ], 3600);

                continue;
            }

            $videoPath = Storage::disk('local')->path($data->path_video);

            if (!file_exists($videoPath)) {
                Log::warning("Video tidak ditemukan untuk user {$userId}: {$videoPath}");
                $failCount++;
                continue;
            }

            try {
                Cache::put('face_training_status', [
                    'phase' => 'extracting',
                    'current' => $current,
                    'total' => $total,
                    'message' => "Extract frames user {$current}/{$total}...",
                ], 3600);

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

                Log::info("Extract frames berhasil untuk user {$userId}. Frames: {$jumlahFrame}");
                $extractCount++;

            } catch (\Exception $e) {
                Log::error("Extract GAGAL untuk user {$userId}: " . $e->getMessage());
                $failCount++;
            }
        }

        Log::info("Sync selesai. Extracted: {$extractCount}, Skipped: {$skipCount}, Gagal: {$failCount}. Memulai retrain SVM...");

        Cache::put('face_training_status', [
            'phase' => 'training',
            'current' => $total,
            'total' => $total,
            'message' => "Sync selesai (Extract: {$extractCount}, Skip: {$skipCount}). Memulai training model SVM...",
        ], 3600);

        RetrainAllModels::dispatch();

        app(\App\Services\NotifikasiService::class)->kirimKeRole(
            'hrd',
            'pengumuman',
            'Sync & Retrain Selesai',
            "Sync frame selesai (Extract: {$extractCount}, Skip: {$skipCount}, Gagal: {$failCount}). Model SVM sedang di-training ulang."
        );
    }
}
