<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DataWajah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceApprovalController extends Controller
{
    public function index()
    {
        $users = User::whereHas('dataWajah', function($q) {
                $q->where('is_verified', 0);
            })
            ->with(['jabatan', 'divisi'])
            ->get();

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

        return view('face-approval.index', compact('users'));
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->dataWajah) {
            $user->dataWajah->update(['is_verified' => 1]);
        }

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

        return redirect()->back()->with('success', 'Wajah ditolak. Karyawan diminta melakukan registrasi ulang.');
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