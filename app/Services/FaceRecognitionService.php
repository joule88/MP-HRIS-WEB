<?php

namespace App\Services;

use App\Enums\StatusVerifikasiWajah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessFaceEnrollment;

class FaceRecognitionService
{
    private function getFlaskUrl(): string
    {
        return config('services.flask.url', 'http://127.0.0.1:5000');
    }

    private function getFlaskHeaders(): array
    {
        return [
            'X-API-Key' => config('services.flask.api_key', ''),
        ];
    }

    public function enrollFace($userId, UploadedFile $video)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            throw new \Exception('User tidak ditemukan.', 404);
        }

        $existing = DB::table('data_wajah')->where('id_user', $userId)->first();
        if ($existing && $existing->is_verified == StatusVerifikasiWajah::APPROVED) {
            $modelPath = "face_models/user_{$userId}_svm.pkl";
            if (Storage::disk('local')->exists($modelPath)) {
                throw new \Exception('Wajah Anda sudah terverifikasi. Hubungi HRD untuk reset.', 400);
            }
            Log::warning("User {$userId} is_verified=APPROVED tapi model SVM hilang, izinkan re-enrollment.");
        }

        $videoStoragePath = "face_videos/{$userId}";

        if (Storage::disk('local')->exists($videoStoragePath)) {
            Storage::disk('local')->deleteDirectory($videoStoragePath);
        }
        Storage::disk('local')->makeDirectory($videoStoragePath);

        $video->storeAs($videoStoragePath, 'enrollment.mp4', 'local');

        DB::table('data_wajah')->updateOrInsert(
            ['id_user' => $userId],
            [
                'path_model_yml' => null,
                'path_model_pkl' => null,
                'path_scaler_pkl' => null,
                'path_video' => "{$videoStoragePath}/enrollment.mp4",
                'jumlah_frame' => null,
                'is_verified' => StatusVerifikasiWajah::PENDING,
                'last_updated' => now(),
            ]
        );

        DB::table('users')
            ->where('id', $userId)
            ->update(['is_face_registered' => 1]);

        ProcessFaceEnrollment::dispatch($userId, $videoStoragePath);

        return [
            'status' => true,
            'message' => 'Video wajah berhasil dikirim. Proses ekstraksi sedang berjalan. Setelah selesai, HRD akan memverifikasi data wajah Anda.',
        ];
    }

    /**
     * Verifikasi wajah menggunakan video pendek (3 detik) atau foto.
     * File dikirim ke Flask ML API untuk diproses.
     */
    public function verifyFace($userId, UploadedFile $file)
    {
        $isVideo = str_starts_with($file->getMimeType() ?? '', 'video/');

        $response = Http::timeout(120)
            ->withHeaders($this->getFlaskHeaders())
            ->attach(
                'file',
                fopen($file->getRealPath(), 'r'),
                $isVideo ? 'verify.mp4' : 'verify.jpg'
            )
            ->post($this->getFlaskUrl() . '/verify-face', [
                'user_id' => (string) $userId,
                'is_video' => $isVideo ? 'true' : 'false',
            ]);

        if (!$response->successful()) {
            $body = $response->json();
            $msg = $body['message'] ?? 'Flask ML API tidak merespons.';
            Log::error("[FaceVerify] Flask error (user {$userId}): HTTP {$response->status()} - {$msg}");
            throw new \Exception("Proses verifikasi gagal di server. {$msg}");
        }

        $output = $response->json();

        if (!$output || !isset($output['status'])) {
            Log::error("[FaceVerify] Output Flask tidak valid (user {$userId}): " . $response->body());
            throw new \Exception("Respons verifikasi tidak valid. Coba beberapa saat lagi.");
        }

        if ($output['status'] === 'error') {
            throw new \Exception($output['message'] ?? 'Unknown error');
        }

        return [
            'verified'            => ($output['match'] ?? false) === true,
            'confidence'          => $output['confidence'] ?? null,
            'svm_df'              => $output['svm_df'] ?? null,
            'verification_status' => $output['verification_status'] ?? 'UNKNOWN',
            'blur_score'          => $output['blur_score'] ?? null,
            'frames_total'        => $output['frames_total'] ?? null,
            'frames_approved'     => $output['frames_approved'] ?? null,
            'frames_pending'      => $output['frames_pending'] ?? null,
            'frames_rejected'     => $output['frames_rejected'] ?? null,
            'approved_ratio'      => $output['approved_ratio'] ?? null,
            'predicted_user'      => $output['predicted_user'] ?? null,
            'expected_user'       => $output['expected_user'] ?? null,
            'message'             => $output['message'] ?? null,
        ];
    }
}
