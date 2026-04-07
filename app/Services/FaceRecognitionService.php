<?php

namespace App\Services;

use App\Enums\StatusVerifikasiWajah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use App\Jobs\ProcessFaceEnrollment;

class FaceRecognitionService
{
    private function getCleanEnv(): array
    {
        return [
            'PATH' => getenv('PATH') ?: '',
            'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\\Windows',
            'TEMP' => getenv('TEMP') ?: sys_get_temp_dir(),
            'TMP' => getenv('TMP') ?: sys_get_temp_dir(),
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
        $datasetPath = "face_datasets/{$userId}";

        if (Storage::disk('local')->exists($videoStoragePath)) {
            Storage::disk('local')->deleteDirectory($videoStoragePath);
        }
        Storage::disk('local')->makeDirectory($videoStoragePath);

        if (Storage::disk('local')->exists($datasetPath)) {
            $files = Storage::disk('local')->files($datasetPath);
            foreach ($files as $file) {
                Storage::disk('local')->delete($file);
            }
        } else {
            Storage::disk('local')->makeDirectory($datasetPath);
        }

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

        ProcessFaceEnrollment::dispatch($userId, $videoStoragePath, $datasetPath);

        return [
            'status' => true,
            'message' => 'Video wajah berhasil dikirim. Proses ekstraksi sedang berjalan. Setelah selesai, HRD akan memverifikasi data wajah Anda.',
        ];
    }

    public function verifyFace($userId, UploadedFile $fotoMasuk)
    {
        $modelDir = storage_path('app/face_models');
        $modelFile = $modelDir . '/face_model.pkl';

        if (!file_exists($modelFile)) {
            throw new \Exception('Model wajah belum tersedia. Belum ada data training.');
        }

        $tempFileName = "verify_{$userId}_" . time() . ".jpg";
        $tempDir = storage_path("app/temp_verify");
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $absPathFoto = $tempDir . "/{$tempFileName}";
        $fotoMasuk->move($tempDir, $tempFileName);

        $pythonPath = env('PYTHON_PATH', 'python');
        $scriptPath = base_path('python_scripts/verify_face_svm.py');

        $process = new Process([
            $pythonPath,
            $scriptPath,
            $modelDir,
            (string) $userId,
            $absPathFoto
        ], null, $this->getCleanEnv());

        $process->setTimeout(60);
        $process->run();

        @unlink($absPathFoto);

        if (!$process->isSuccessful()) {
            throw new \Exception("Python error: " . $process->getErrorOutput());
        }

        $output = json_decode($process->getOutput(), true);

        if (!isset($output['status'])) {
            throw new \Exception("Invalid Python output: " . $process->getOutput());
        }

        if (isset($output['status']) && $output['status'] === 'error') {
            throw new \Exception("Error Verifikasi: " . ($output['message'] ?? 'Unknown error'));
        }

        return [
            'verified'            => ($output['match'] ?? false) === true,
            'confidence'          => $output['confidence'] ?? null,
            'svm_confidence'      => $output['svm_confidence'] ?? null,
            'verification_status' => $output['verification_status'] ?? 'UNKNOWN',
            'blur_score'          => $output['blur_score'] ?? null,
            'message'             => $output['message'] ?? null,
        ];
    }
}
