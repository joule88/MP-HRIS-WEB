<?php

namespace App\Jobs;

use App\Enums\StatusVerifikasiWajah;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatch(int $userId, string $videoStoragePath, string $datasetPath)
 */
class ProcessFaceEnrollment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 2;

    protected int $userId;
    protected string $videoStoragePath;
    protected string $datasetPath;

    public function __construct(int $userId, string $videoStoragePath, string $datasetPath)
    {
        $this->userId = $userId;
        $this->videoStoragePath = $videoStoragePath;
        $this->datasetPath = $datasetPath;
    }

    public function handle(): void
    {
        $pythonPath = env('PYTHON_PATH', 'python');
        $absVideoPath = Storage::disk('local')->path("{$this->videoStoragePath}/enrollment.mp4");
        $absDatasetPath = Storage::disk('local')->path($this->datasetPath);

        $cleanEnv = [
            'PATH' => getenv('PATH') ?: '',
            'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\\Windows',
            'TEMP' => getenv('TEMP') ?: sys_get_temp_dir(),
            'TMP' => getenv('TMP') ?: sys_get_temp_dir(),
        ];

        try {
            $extractResult = $this->runExtractFrames(
                $pythonPath, $absVideoPath, $absDatasetPath, $cleanEnv
            );

            $jumlahFrame = $extractResult['total_extracted'] ?? 0;

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

    private function runExtractFrames($pythonPath, $videoPath, $outputDir, $env)
    {
        $scriptPath = base_path('python_scripts/extract_frames.py');

        $process = new Process([
            $pythonPath, $scriptPath, $videoPath, $outputDir,
            '--max_frames', '100'
        ], null, $env);

        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception("Extract frames gagal: " . $process->getErrorOutput());
        }

        $output = json_decode($process->getOutput(), true);

        if (!isset($output['status']) || $output['status'] !== 'success') {
            throw new \Exception("Extract frames error: " . ($output['message'] ?? 'Unknown'));
        }

        return $output;
    }
}
