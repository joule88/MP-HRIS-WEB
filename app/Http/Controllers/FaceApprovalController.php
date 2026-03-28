<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DataWajah;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceApprovalController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['jabatan', 'divisi', 'dataWajah']);

        $status = $request->get('status', '');

        if ($status === 'pending') {
            $query->whereHas('dataWajah', fn($q) => $q->where('is_verified', 0));
        } elseif ($status === 'approved') {
            $query->whereHas('dataWajah', fn($q) => $q->where('is_verified', 1));
        } elseif ($status === 'rejected') {
            $query->whereHas('dataWajah', fn($q) => $q->where('is_verified', 2));
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
            $storagePath = "face_datasets/{$user->id}";
            $poses = ['depan', 'kanan', 'kiri', 'bawah'];
            $fotoList = [];

            foreach ($poses as $pose) {
                $extensions = ['jpg', 'jpeg', 'png'];
                foreach ($extensions as $ext) {
                    $filePath = "{$storagePath}/user_{$user->id}_{$pose}.{$ext}";
                    if (Storage::disk('local')->exists($filePath)) {
                        $fotoList[$pose] = $filePath;
                        break;
                    }
                }
            }

            $user->face_photos = $fotoList;
        });

        $stats = [
            'pending'      => DataWajah::where('is_verified', 0)->count(),
            'approved'     => DataWajah::where('is_verified', 1)->count(),
            'rejected'     => DataWajah::where('is_verified', 2)->count(),
            'unregistered' => User::whereDoesntHave('dataWajah')->count(),
        ];

        return view('face-approval.index', compact('users', 'stats', 'status'));
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->dataWajah) {
            $user->dataWajah->update(['is_verified' => 1]);
        }

        app(NotifikasiService::class)->kirim(
            $user->id,
            'face_disetujui',
            'Wajah Terverifikasi',
            'Data wajah Anda telah diverifikasi. Sekarang Anda bisa melakukan presensi.'
        );

        return redirect()->back()->with('success', 'Wajah karyawan berhasil diverifikasi. Karyawan kini bisa melakukan presensi.');
    }

    public function reject($id)
    {
        $user = User::findOrFail($id);

        if ($user->dataWajah) {
            $user->dataWajah->delete();
        }

        $user->update(['is_face_registered' => 0]);

        $storagePath = "face_datasets/{$user->id}";
        if (Storage::disk('local')->exists($storagePath)) {
            Storage::disk('local')->deleteDirectory($storagePath);
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

        if ($user->dataWajah) {
            $user->dataWajah->delete();
        }

        $user->update(['is_face_registered' => 0]);

        $storagePath = "face_datasets/{$user->id}";
        if (Storage::disk('local')->exists($storagePath)) {
            Storage::disk('local')->deleteDirectory($storagePath);
        }

        app(NotifikasiService::class)->kirim(
            $user->id,
            'face_reset',
            'Data Wajah Direset',
            'Data wajah Anda telah direset oleh HRD. Silakan lakukan registrasi ulang melalui aplikasi.'
        );

        return redirect()->back()->with('success', 'Data wajah berhasil direset. Karyawan harus melakukan registrasi ulang.');
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
}