<?php

namespace App\Jobs;

use App\Enums\StatusVerifikasiWajah;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatch(int $userId, string $videoStoragePath)
 */
class ProcessFaceEnrollment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 2;

    protected int $userId;
    protected string $videoStoragePath;

    public function __construct(int $userId, string $videoStoragePath)
    {
        $this->userId = $userId;
        $this->videoStoragePath = $videoStoragePath;
    }

    public function handle(): void
    {
        $absVideoPath = Storage::disk('local')->path("{$this->videoStoragePath}/enrollment.mp4");

        if (!file_exists($absVideoPath)) {
            Log::error("Face enrollment: video tidak ditemukan untuk user {$this->userId}: {$absVideoPath}");
            return;
        }

        try {
            $response = Http::timeout(120)
                ->withHeaders([
                    'X-API-Key' => config('services.flask.api_key'),
                ])
                ->attach(
                    'video',
                    fopen($absVideoPath, 'r'),
                    'enrollment.mp4'
                )
                ->post(config('services.flask.url') . '/extract-and-embed', [
                    'user_id' => (string) $this->userId,
                    'target_frames' => 200,
                ]);

            if (!$response->successful()) {
                throw new \Exception("Flask API error: HTTP {$response->status()} - " . $response->body());
            }

            $output = $response->json();

            if (!isset($output['status']) || $output['status'] !== 'success') {
                throw new \Exception("Extract & embed error: " . ($output['message'] ?? 'Unknown'));
            }

            $jumlahFrame = $output['total_extracted'] ?? 0;
            $embedding = $output['embedding'] ?? null;

            $updateData = [
                'jumlah_frame' => $jumlahFrame,
                'is_verified' => StatusVerifikasiWajah::PENDING,
            ];

            if ($embedding && is_array($embedding)) {
                $updateData['face_embeddings'] = json_encode($embedding);
                $updateData['jumlah_embedding'] = $jumlahFrame;
                $updateData['embedding_generated_at'] = now();
                Log::info("Face enrollment: embedding berhasil di-generate untuk user {$this->userId}. Dimensi: " . count($embedding));
            } else {
                Log::warning("Face enrollment: frame berhasil di-extract tapi embedding tidak tersedia untuk user {$this->userId}.");
            }

            DB::table('data_wajah')->where('id_user', $this->userId)->update($updateData);

            Log::info("Face enrollment: extract & embed berhasil untuk user {$this->userId}. Frames: {$jumlahFrame}. Menunggu HRD approve.");

        } catch (\Exception $e) {
            Log::error("Face enrollment GAGAL untuk user {$this->userId}: " . $e->getMessage());

            DB::table('data_wajah')->where('id_user', $this->userId)->update([
                'is_verified' => StatusVerifikasiWajah::PENDING,
            ]);
        }
    }
}
