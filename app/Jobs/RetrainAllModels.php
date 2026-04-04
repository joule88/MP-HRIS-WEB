<?php

namespace App\Jobs;

use App\Enums\StatusVerifikasiWajah;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as QueueableTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class RetrainAllModels implements ShouldQueue
{
    use Queueable;

    public $timeout = 3600;

    public function handle(): void
    {
        Log::info("Memulai training model SVM multi-class global...");

        $approvedUsers = DB::table('data_wajah')
            ->where('is_verified', StatusVerifikasiWajah::APPROVED)
            ->pluck('id_user')
            ->toArray();

        if (count($approvedUsers) === 0) {
            Log::info("Tidak ada user approved. Skip training.");
            $modelPath = storage_path('app/face_models/face_model.pkl');
            if (file_exists($modelPath)) {
                unlink($modelPath);
            }
            return;
        }

        $pythonPath = env('PYTHON_PATH', 'python');
        $modelStoragePath = storage_path('app/face_models');
        $baseDatasetsPath = storage_path('app/face_datasets');
        $scriptPath = base_path('python_scripts/train_face_svm.py');

        if (!file_exists($modelStoragePath)) {
            mkdir($modelStoragePath, 0777, true);
        }

        $cleanEnv = [
            'PATH' => getenv('PATH') ?: '',
            'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\\Windows',
            'TEMP' => getenv('TEMP') ?: sys_get_temp_dir(),
            'TMP' => getenv('TMP') ?: sys_get_temp_dir(),
        ];

        $approvedIdsCsv = implode(',', $approvedUsers);

        try {
            $command = [
                $pythonPath,
                $scriptPath,
                $baseDatasetsPath,
                $modelStoragePath,
                $approvedIdsCsv,
            ];

            $process = new Process($command, null, $cleanEnv);
            $process->setTimeout(600);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \Exception("Training gagal: " . $process->getErrorOutput());
            }

            $output = json_decode($process->getOutput(), true);

            if (!isset($output['status']) || $output['status'] !== 'success') {
                throw new \Exception("Training error: " . ($output['message'] ?? 'Unknown'));
            }

            $totalUsers = $output['total_users'] ?? 0;
            Log::info("Training model global selesai. Total user: {$totalUsers}");

        } catch (\Exception $e) {
            Log::error("Training model global GAGAL: " . $e->getMessage());
        }
    }
}
