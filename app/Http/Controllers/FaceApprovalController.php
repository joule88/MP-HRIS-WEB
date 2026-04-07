<?php

namespace App\Http\Controllers;

use App\Enums\StatusVerifikasiWajah;
use App\Models\User;
use App\Models\DataWajah;
use App\Services\NotifikasiService;
use App\Jobs\RetrainAllModels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceApprovalController extends Controller
{
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
            $datasetPath = "face_datasets/{$user->id}";
            $frameList = [];

            if (Storage::disk('local')->exists($datasetPath)) {
                $files = Storage::disk('local')->files($datasetPath);
                foreach ($files as $file) {
                    if (preg_match('/\/frame_\d+\.jpg$/', $file)) {
                        $frameList[] = $file;
                    }
                }
                sort($frameList);
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

        $datasetPath = storage_path("app/face_datasets/{$user->id}");
        if (!file_exists($datasetPath) || count(glob("$datasetPath/*.jpg")) < 10) {
            return redirect()->back()->with('error', 'Dataset wajah belum cukup. Pastikan proses extract frames sudah selesai.');
        }

        $user->dataWajah->update(['is_verified' => StatusVerifikasiWajah::APPROVED]);

        dispatch(new RetrainAllModels());

        app(NotifikasiService::class)->kirim(
            $user->id,
            'face_disetujui',
            'Wajah Terverifikasi',
            'Data wajah Anda telah diverifikasi. Sekarang Anda bisa melakukan presensi.'
        );

        return redirect()->back()->with('success', 'Wajah berhasil diverifikasi. Model sedang di-training ulang.');
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

        if ($wasApproved) {
            dispatch(new RetrainAllModels());
        }

        app(NotifikasiService::class)->kirim(
            $user->id,
            'face_ditolak',
            'Registrasi Wajah Ditolak',
            'Data wajah Anda ditolak. Silakan lakukan registrasi ulang melalui aplikasi.'
        );

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

        if ($wasApproved) {
            dispatch(new RetrainAllModels());
        }

        app(NotifikasiService::class)->kirim(
            $user->id,
            'face_reset',
            'Data Wajah Direset',
            'Data wajah Anda telah direset oleh HRD. Silakan lakukan registrasi ulang melalui aplikasi.'
        );

        return redirect()->back()->with('success', 'Data wajah berhasil direset. Karyawan harus melakukan registrasi ulang.');
    }

    public function showFrame($userId, $frameIndex)
    {
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
}