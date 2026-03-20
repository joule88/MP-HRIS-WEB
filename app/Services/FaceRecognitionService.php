<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class FaceRecognitionService
{
    public function enrollFace($userId, array $photos)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            throw new \Exception('User tidak ditemukan.', 404);
        }

        $existing = DB::table('data_wajah')->where('id_user', $userId)->first();
        if ($existing && $existing->is_verified == 1) {
            throw new \Exception('Wajah Anda sudah terverifikasi. Hubungi HRD untuk reset.', 400);
        }

        try {
            DB::beginTransaction();

            $storagePath = "face_datasets/{$userId}";

            if (Storage::disk('local')->exists($storagePath)) {
                Storage::disk('local')->deleteDirectory($storagePath);
            }
            Storage::disk('local')->makeDirectory($storagePath);

            $savedPaths = [];
            foreach ($photos as $pose => $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = "user_{$userId}_{$pose}." . $file->getClientOriginalExtension();
                    $path = $file->storeAs($storagePath, $fileName, 'local');
                    $savedPaths[$pose] = $path;
                }
            }

            DB::table('data_wajah')->updateOrInsert(
                ['id_user' => $userId],
                [
                    'path_model_yml' => null,
                    'is_verified' => 0,
                    'last_updated' => now(),
                ]
            );

            DB::table('users')
                ->where('id', $userId)
                ->update(['is_face_registered' => 1]);

            $trainingSuccess = false;

            try {
                $pythonPath = env('PYTHON_PATH', 'python');
                $scriptPath = base_path('python_scripts/train_face.py');

                $absDatasetPath = Storage::disk('local')->path($storagePath);
                $modelStoragePath = storage_path("app/face_models");

                $process = new Process([
                    $pythonPath,
                    $scriptPath,
                    (string) $userId,
                    $absDatasetPath,
                    $modelStoragePath
                ]);

                $process->setTimeout(120);
                $process->run();

                if (!$process->isSuccessful()) {
                    \Illuminate\Support\Facades\Log::warning("Face Training Failed: " . $process->getErrorOutput());
                } else {
                    $output = json_decode($process->getOutput(), true);
                    if (isset($output['status']) && $output['status'] === 'success') {
                        $trainingSuccess = true;
                    } else {
                        \Illuminate\Support\Facades\Log::warning("Face Training Failed: " . ($output['message'] ?? 'Unknown Error'));
                    }
                }
            } catch (\Exception $trainEx) {
                \Illuminate\Support\Facades\Log::warning("Face Training Error: " . $trainEx->getMessage());
            }

            $relativeModelPath = "face_models/user_{$userId}.yml";

            // Jika training Python berhasil → otomatis verified
            // Jika training gagal → tetap pending (is_verified = 0), butuh approval manual HRD
            DB::table('data_wajah')->where('id_user', $userId)->update([
                'path_model_yml' => $relativeModelPath,
                'is_verified'    => $trainingSuccess ? 1 : 0,
            ]);

            DB::commit();

            return [
                'status' => true,
                'message' => 'Foto wajah berhasil didaftarkan dan model telah dilatih.',
                'dataset_path' => $storagePath
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($storagePath)) {
                Storage::disk('local')->deleteDirectory($storagePath);
            }
            throw $e;
        }
    }

    public function verifyFace($userId, UploadedFile $fotoMasuk)
    {
        $dataWajah = DB::table('data_wajah')
            ->where('id_user', $userId)
            ->where('is_verified', 1)
            ->first();

        if (!$dataWajah || empty($dataWajah->path_model_yml)) {
            throw new \Exception('Model wajah belum tersedia atau belum diverifikasi.');
        }

        $tempFileName = "verify_{$userId}_" . time() . ".jpg";
        $tempDir = storage_path("app/temp_verify");
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $absPathFoto = $tempDir . "/{$tempFileName}";
        $fotoMasuk->move($tempDir, $tempFileName);

        $absPathModel = storage_path("app/{$dataWajah->path_model_yml}");

        if (!file_exists($absPathModel)) {
            @unlink($absPathFoto);
            throw new \Exception("File model .yml tidak ditemukan: {$dataWajah->path_model_yml}");
        }

        $pythonPath = env('PYTHON_PATH', 'python');
        $scriptPath = base_path('python_scripts/verify_face.py');

        $process = new Process([
            $pythonPath,
            $scriptPath,
            $absPathModel,
            $absPathFoto
        ]);

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

        if ($output['status'] === 'error') {
            throw new \Exception("Error Verifikasi: " . $output['message']);
        }

        return [
            'verified' => $output['match'] === true,
            'confidence' => $output['confidence'] ?? null,
            'verification_status' => $output['verification_status'] ?? 'UNKNOWN',
            'message' => $output['message'] ?? null,
        ];
    }
}
