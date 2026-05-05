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
            if ($existing->face_embeddings !== null) {
                throw new \Exception('Wajah Anda sudah terverifikasi. Hubungi HRD untuk reset.', 400);
            }
            Log::warning("User {$userId} is_verified=APPROVED tapi embedding kosong, izinkan re-enrollment.");
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
                'face_embeddings' => null,
                'jumlah_embedding' => null,
                'embedding_generated_at' => null,
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
     * Verifikasi wajah menggunakan pendekatan embedding + cosine similarity.
     * File dikirim ke Flask untuk generate embedding, lalu dibandingkan dengan database.
     */
    public function verifyFace($userId, UploadedFile $file)
    {
        $probeEmbedding = $this->getEmbedding($file);

        $allEmbeddings = DB::table('data_wajah')
            ->where('is_verified', StatusVerifikasiWajah::APPROVED)
            ->whereNotNull('face_embeddings')
            ->get(['id_user', 'face_embeddings']);

        if ($allEmbeddings->isEmpty()) {
            throw new \Exception('Belum ada data wajah terverifikasi di sistem.');
        }

        $bestMatch = null;
        $bestScore = -1;

        foreach ($allEmbeddings as $data) {
            $storedEmbedding = json_decode($data->face_embeddings, true);
            if (!is_array($storedEmbedding)) continue;

            $similarity = $this->cosineSimilarity($probeEmbedding, $storedEmbedding);

            if ($similarity > $bestScore) {
                $bestScore = $similarity;
                $bestMatch = $data->id_user;
            }
        }

        $threshold = 0.6;
        $isMatch = $bestScore >= $threshold && $bestMatch == $userId;

        $verificationStatus = 'REJECTED';
        if ($isMatch) {
            $verificationStatus = 'MATCH';
        } elseif ($bestScore >= $threshold && $bestMatch != $userId) {
            $verificationStatus = 'MISMATCH';
        }

        Log::info("[FaceVerify] User {$userId}: best_match={$bestMatch}, score={$bestScore}, status={$verificationStatus}");

        return [
            'verified'            => $isMatch,
            'confidence'          => round($bestScore, 4),
            'svm_df'              => null,
            'verification_status' => $verificationStatus,
            'blur_score'          => $probeEmbedding['blur_score'] ?? null,
            'predicted_user'      => $bestMatch,
            'expected_user'       => $userId,
            'message'             => $isMatch ? 'Wajah cocok' : 'Wajah tidak cocok',
        ];
    }

    private function getEmbedding(UploadedFile $file): array
    {
        $isVideo = str_starts_with($file->getMimeType() ?? '', 'video/');

        $response = Http::timeout(120)
            ->withHeaders($this->getFlaskHeaders())
            ->attach(
                'file',
                fopen($file->getRealPath(), 'r'),
                $isVideo ? 'verify.mp4' : 'verify.jpg'
            )
            ->post($this->getFlaskUrl() . '/get-embedding', [
                'is_video' => $isVideo ? 'true' : 'false',
            ]);

        if (!$response->successful()) {
            $body = $response->json();
            $msg = $body['message'] ?? 'Flask ML API tidak merespons.';
            Log::error("[FaceVerify] Flask /get-embedding error: HTTP {$response->status()} - {$msg}");
            throw new \Exception("Gagal mengekstrak embedding wajah. {$msg}");
        }

        $output = $response->json();

        if (!$output || $output['status'] !== 'success' || !isset($output['embedding'])) {
            throw new \Exception('Respons embedding tidak valid.');
        }

        return $output;
    }

    private function cosineSimilarity(array $embeddingResponse, array $storedEmbedding): float
    {
        $a = $embeddingResponse['embedding'] ?? $embeddingResponse;
        $b = $storedEmbedding;

        if (count($a) !== count($b) || count($a) === 0) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dotProduct / ($normA * $normB);
    }
}
