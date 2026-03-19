<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FaceRecognitionService;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Log;

class FaceEnrollmentController extends Controller
{
    protected $faceService;

    public function __construct(FaceRecognitionService $faceService)
    {
        $this->faceService = $faceService;
    }

    public function enrollFace(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'foto_depan' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'foto_kanan' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'foto_kiri' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'foto_bawah' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            $photos = [
                'depan' => $request->file('foto_depan'),
                'kanan' => $request->file('foto_kanan'),
                'kiri' => $request->file('foto_kiri'),
                'bawah' => $request->file('foto_bawah'),
            ];

            $result = $this->faceService->enrollFace($user->id, $photos);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            Log::error('Face Enrollment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getFaceStatus(Request $request)
    {
        $user = $request->user();

        $dataWajah = \DB::table('data_wajah')->where('id_user', $user->id)->first();

        $status = 'not_registered';
        if ($user->is_face_registered) {
            $status = 'pending';
            if ($dataWajah && $dataWajah->is_verified == 1) {
                $status = 'verified';
            } elseif ($dataWajah && $dataWajah->is_verified == 2) {
                $status = 'rejected';
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $status,
                'is_registered' => (bool) $user->is_face_registered
            ]
        ]);
    }

    public function verifyFace(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|max:5120',
        ]);

        try {
            $user = $request->user();
            $file = $request->file('foto');

            $result = $this->faceService->verifyFace($user->id, $file);

            return response()->json([
                'success' => true,
                'message' => 'Wajah terverifikasi',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verifikasi Gagal: ' . $e->getMessage()
            ], 400);
        }
    }
}
