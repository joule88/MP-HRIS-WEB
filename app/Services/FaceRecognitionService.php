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
            if (($existing->jumlah_frame ?? 0) > 0) {
                throw new \Exception('Wajah Anda sudah terverifikasi. Hubungi HRD untuk reset.', 400);
            }
            Log::warning("User {$userId} is_verified=APPROVED tapi data kosong, izinkan re-enrollment.");
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
     * Verifikasi wajah menggunakan SVM (Jalur 2).
     * File dikirim ke Flask /verify-face untuk prediksi SVM.
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
            Log::error("[FaceVerify] Flask /verify-face error: HTTP {$response->status()} - {$msg}");
            throw new \Exception("Gagal memverifikasi wajah. {$msg}");
        }

        $result = $response->json();

        if (!$result || $result['status'] !== 'success') {
            throw new \Exception($result['message'] ?? 'Respons verifikasi tidak valid.');
        }

        Log::info("[FaceVerify] User {$userId}: " . json_encode([
            'match' => $result['match'],
            'verification_status' => $result['verification_status'],
            'confidence' => $result['confidence'],
            'svm_df' => $result['svm_df'] ?? null,
            'predicted_user' => $result['predicted_user'] ?? null,
            'blur_score' => $result['blur_score'] ?? null,
        ]));

        return [
            'verified'            => $result['match'],
            'confidence'          => $result['confidence'],
            'svm_df'              => $result['svm_df'] ?? null,
            'verification_status' => $result['verification_status'],
            'blur_score'          => $result['blur_score'] ?? null,
            'predicted_user'      => $result['predicted_user'] ?? null,
            'expected_user'       => (string) $userId,
            'approved_ratio'      => $result['approved_ratio'] ?? null,
            'message'             => $result['message'] ?? ($result['match'] ? 'Wajah cocok' : 'Wajah tidak cocok'),
        ];
    }
}

