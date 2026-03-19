<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="User login",
     *     description="Login user and return token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="pegawai@mpg.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="user", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Email atau Password salah")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validate->fails()) {
                return ApiResponse::validationError($validate->errors());
            }

            if (!Auth::attempt($request->only('email', 'password'))) {
                return ApiResponse::unauthorized('Email atau Password salah');
            }

            $user = User::where('email', $request->email)
                ->with(['divisi', 'jabatan', 'kantor', 'roles'])
                ->first();

            if ($user->status_aktif != 1) {
                return ApiResponse::error('Akun Anda dinonaktifkan. Hubungi admin.', 403);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return ApiResponse::success([
                'token' => $token,
                'user' => $user
            ], 'Login berhasil');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Login Error: ' . $e->getMessage());
            return ApiResponse::error('Terjadi kesalahan server: ' . $e->getMessage(), 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            /** @var \Laravel\Sanctum\PersonalAccessToken $token */
            $token = $request->user()->currentAccessToken();
            $token->delete();
            return ApiResponse::success(null, 'Logout berhasil');
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal logout: ' . $e->getMessage(), 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            $validate = Validator::make($request->all(), [
                'nama_lengkap' => 'required|string|max:255',
                'no_telp' => 'nullable|string|max:15',
                'password' => 'nullable|min:6',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validate->fails()) {
                return ApiResponse::validationError($validate->errors());
            }

            $data = [
                'nama_lengkap' => $request->nama_lengkap,
                'no_telp' => $request->no_telp,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('foto')) {

                if ($user->foto) {
                    Storage::disk('public')->delete($user->foto);
                }
                $data['foto'] = $request->file('foto')->store('foto-profil', 'public');
            }

            $user->update($data);

            return ApiResponse::success($user, 'Profil berhasil diperbarui');

        } catch (\Exception $e) {
            return ApiResponse::error('Gagal update profil: ' . $e->getMessage(), 500);
        }
    }

    public function user(Request $request)
    {
        $user = $request->user()->load(['roles', 'divisi', 'jabatan', 'kantor']);
        return ApiResponse::success($user);
    }
}
