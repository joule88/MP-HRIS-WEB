<?php

namespace App\Http\Controllers;

use App\Enums\StatusVerifikasiWajah;
use App\Models\User;
use App\Models\DataWajah;
use App\Events\FaceEnrollmentUpdated;
use App\Services\NotifikasiService;

use App\Jobs\ReextractAllFrames;
use App\Jobs\RetrainAllModels;
use App\Jobs\MigrateExistingEmbeddings;
use App\Services\FaceRecognitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FaceApprovalController extends Controller
{
    protected $faceService;

    public function __construct(FaceRecognitionService $faceService)
    {
        $this->faceService = $faceService;
    }

    public function index(Request $request)
    {
        $query = User::with(['jabatan', 'divisi', 'dataWajah']);

        $status = $request->get('status', '');

        if ($status === 'pending') {
            $query->whereHas('dataWajah', fn($q) => $q->where('is_verified', StatusVerifikasiWajah::PENDING));
        } elseif ($status === 'approved') {
            $query->whereHas('dataWajah', fn($q) => $q->where('is_verified', StatusVerifikasiWajah::APPROVED));
        } elseif ($status === 'rejected') {
            $query->whereHas('dataWajah', fn($q) => $q->where('is_verified', StatusVerifikasiWajah::REJECTED));
        } elseif ($status === 'unregistered') {
            $query->whereDoesntHave('dataWajah');
        } else {
            $query->whereHas('dataWajah');
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $users = $query->get();

        $users->each(function ($user) {
            $dw = $user->dataWajah;
            $frameList = [];

            if ($dw && $dw->jumlah_frame > 0) {
                for ($i = 0; $i < $dw->jumlah_frame; $i++) {
                    $frameList[] = sprintf("face_datasets/%d/frame_%03d.jpg", $user->id, $i);
                }
            }

            $user->face_frames = $frameList;
            $user->face_frame_count = count($frameList);
        });

        $stats = [
            'pending'      => DataWajah::where('is_verified', StatusVerifikasiWajah::PENDING)->count(),
            'approved'     => DataWajah::where('is_verified', StatusVerifikasiWajah::APPROVED)->count(),
            'rejected'     => DataWajah::where('is_verified', StatusVerifikasiWajah::REJECTED)->count(),
            'unregistered' => User::whereDoesntHave('dataWajah')->count(),
        ];

        return view('face-approval.index', compact('users', 'stats', 'status'));
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);

        if (!$user->dataWajah) {
            return redirect()->back()->with('error', 'Data wajah belum tersedia.');
        }

        $jumlahFrame = $user->dataWajah->jumlah_frame ?? 0;
        if ($jumlahFrame < 50) {
            return redirect()->back()->with('error', "Dataset wajah belum cukup ({$jumlahFrame} frame, minimal 50). Pastikan proses extract frames sudah selesai.");
        }

        $user->dataWajah->update(['is_verified' => StatusVerifikasiWajah::APPROVED]);

        RetrainAllModels::dispatch();

        app(NotifikasiService::class)->kirim(
            $user->id,
            'face_disetujui',
            'Wajah Terverifikasi',
            'Data wajah Anda telah diverifikasi. Sekarang Anda bisa melakukan presensi.'
        );

        broadcast(new FaceEnrollmentUpdated($user->id, 'approved', 'Data wajah diverifikasi.'));

        return redirect()->back()->with('success', 'Wajah berhasil diverifikasi. Model SVM sedang di-training ulang.');
    }

    public function reject($id)
    {
        $user = User::findOrFail($id);

        $wasApproved = $user->dataWajah && $user->dataWajah->is_verified == StatusVerifikasiWajah::APPROVED;

        if ($user->dataWajah) {
            $user->dataWajah->delete();
        }

        $user->update(['is_face_registered' => 0]);

        $this->cleanupUserFaceData($user->id);



        app(NotifikasiService::class)->kirim(
            $user->id,
            'face_ditolak',
            'Registrasi Wajah Ditolak',
            'Data wajah Anda ditolak. Silakan lakukan registrasi ulang melalui aplikasi.'
        );

        broadcast(new FaceEnrollmentUpdated($user->id, 'rejected', 'Data wajah ditolak.'));

        return redirect()->back()->with('success', 'Wajah ditolak. Karyawan diminta melakukan registrasi ulang.');
    }

    public function reset($id)
    {
        $user = User::findOrFail($id);

        $wasApproved = $user->dataWajah && $user->dataWajah->is_verified == StatusVerifikasiWajah::APPROVED;

        if ($user->dataWajah) {
            $user->dataWajah->delete();
        }

        $user->update(['is_face_registered' => 0]);

        $this->cleanupUserFaceData($user->id);



        app(NotifikasiService::class)->kirim(
            $user->id,
            'face_reset',
            'Data Wajah Direset',
            'Data wajah Anda telah direset oleh HRD. Silakan lakukan registrasi ulang melalui aplikasi.'
        );

        broadcast(new FaceEnrollmentUpdated($user->id, 'reset', 'Data wajah direset.'));

        return redirect()->back()->with('success', 'Data wajah berhasil direset. Karyawan harus melakukan registrasi ulang.');
    }

    public function showFrame($userId, $frameIndex)
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'X-API-Key' => config('services.flask.api_key'),
                ])
                ->get(config('services.flask.url') . "/get-frame/{$userId}/{$frameIndex}");

            if ($response->successful()) {
                return response($response->body(), 200)
                    ->header('Content-Type', 'image/jpeg')
                    ->header('Cache-Control', 'public, max-age=86400');
            }
        } catch (\Exception $e) {
            // Fallback ke storage lokal
        }

        $rawFilePath = "face_datasets/{$userId}/raw_frame_{$frameIndex}.jpg";
        $filePath = "face_datasets/{$userId}/frame_{$frameIndex}.jpg";

        if (Storage::disk('local')->exists($rawFilePath)) {
            return response()->file(Storage::disk('local')->path($rawFilePath));
        } elseif (Storage::disk('local')->exists($filePath)) {
            return response()->file(Storage::disk('local')->path($filePath));
        }

        abort(404);
    }

    public function showPhoto($userId, $pose)
    {
        $extensions = ['jpg', 'jpeg', 'png'];
        foreach ($extensions as $ext) {
            $filePath = "face_datasets/{$userId}/user_{$userId}_{$pose}.{$ext}";
            if (Storage::disk('local')->exists($filePath)) {
                return response()->file(Storage::disk('local')->path($filePath));
            }
        }

        abort(404);
    }

    private function cleanupUserFaceData($userId)
    {
        try {
            Http::timeout(15)
                ->withHeaders([
                    'X-API-Key' => config('services.flask.api_key'),
                ])
                ->delete(config('services.flask.url') . "/delete-frames/{$userId}");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal hapus frame di Flask untuk user {$userId}: " . $e->getMessage());
        }

        $paths = [
            "face_datasets/{$userId}",
            "face_videos/{$userId}",
        ];

        foreach ($paths as $path) {
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->deleteDirectory($path);
            }
        }

        $modelFiles = [
            "face_models/user_{$userId}_svm.pkl",
            "face_models/user_{$userId}_scaler.pkl",
            "face_models/user_{$userId}_centroid.pkl",
            "face_models/user_{$userId}.yml",
        ];

        foreach ($modelFiles as $file) {
            if (Storage::disk('local')->exists($file)) {
                Storage::disk('local')->delete($file);
            }
        }
    }

    public function trainingStatus()
    {
        $status = \Illuminate\Support\Facades\Cache::get('face_training_status', ['phase' => 'idle']);
        return response()->json($status);
    }

    public function reextractAll()
    {
        dispatch(new ReextractAllFrames());
        return redirect()->back()->with('success', 'Job re-extract frame dari video berhasil ditambahkan ke antrian. Mohon tunggu beberapa saat untuk hasilnya.');
    }

    public function migrateEmbeddings()
    {
        dispatch(new MigrateExistingEmbeddings());
        return redirect()->back()->with('success', 'Proses migrasi embedding sedang berjalan di background. Anda akan menerima notifikasi setelah selesai.');
    }

    public function uploadVideo(Request $request, $id)
    {
        $request->validate([
            'video_wajah' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/3gpp,video/x-matroska|max:51200',
        ], [
            'video_wajah.required' => 'File video wajah wajib diupload.',
            'video_wajah.mimetypes' => 'Format video tidak didukung. Gunakan MP4, MOV, atau AVI.',
            'video_wajah.max' => 'Ukuran video terlalu besar (maksimal 50MB).',
        ]);

        $user = User::findOrFail($id);

        try {
            $result = $this->faceService->enrollFace($user->id, $request->file('video_wajah'));

            return redirect()->back()->with('success', "Video wajah untuk {$user->nama_lengkap} berhasil diupload. Proses ekstraksi frame sedang berjalan.");
        } catch (\Exception $e) {
            Log::error("Upload video wajah gagal untuk user {$id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal upload video: ' . $e->getMessage());
        }
    }
}