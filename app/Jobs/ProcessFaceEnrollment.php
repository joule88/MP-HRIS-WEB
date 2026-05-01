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
                ->post(config('services.flask.url') . '/extract-frames', [
                    'user_id' => (string) $this->userId,
                    'target_frames' => 200,
                ]);

            if (!$response->successful()) {
                throw new \Exception("Flask API error: HTTP {$response->status()} - " . $response->body());
            }

            $output = $response->json();

            if (!isset($output['status']) || $output['status'] !== 'success') {
                throw new \Exception("Extract frames error: " . ($output['message'] ?? 'Unknown'));
            }

            $jumlahFrame = $output['total_extracted'] ?? 0;

            DB::table('data_wajah')->where('id_user', $this->userId)->update([
                'jumlah_frame' => $jumlahFrame,
                'is_verified' => StatusVerifikasiWajah::PENDING,
            ]);

            Log::info("Face enrollment: extract frames berhasil untuk user {$this->userId}. Frames: {$jumlahFrame}. Menunggu HRD approve untuk training model.");

        } catch (\Exception $e) {
            Log::error("Face enrollment GAGAL untuk user {$this->userId}: " . $e->getMessage());

            DB::table('data_wajah')->where('id_user', $this->userId)->update([
                'is_verified' => StatusVerifikasiWajah::PENDING,
            ]);
        }
    }
}
