<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{

    public function update(Request $request)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'no_telp' => 'nullable|string|max:20',
                'alamat' => 'nullable|string|max:500',
            ]);

            if ($request->has('no_telp')) {
                $user->no_telp = $request->no_telp;
            }

            if ($request->has('alamat')) {
                $user->alamat = $request->alamat;
            }

            if ($request->hasFile('foto')) {
                if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                    Storage::disk('public')->delete($user->foto);
                }

                $path = $request->file('foto')->store('uploads/profil', 'public');
                $user->foto = $path;
            }

            $user->save();

            return ApiResponse::success($user->load(['divisi', 'jabatan', 'kantor']), 'Profil berhasil diperbarui.');

        } catch (\Exception $e) {
            return ApiResponse::error('Gagal update profil: ' . $e->getMessage(), 500);
        }
    }

    public function password(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            $user = Auth::user();

            if (!Hash::check($request->current_password, $user->password)) {
                return ApiResponse::error('Password lama salah.', 400);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return ApiResponse::success(null, 'Kata sandi berhasil diubah.');

        } catch (\Exception $e) {
            return ApiResponse::error('Gagal ubah kata sandi: ' . $e->getMessage(), 500);
        }
    }
}
