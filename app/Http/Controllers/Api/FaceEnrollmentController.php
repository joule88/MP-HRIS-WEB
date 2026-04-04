<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FaceRecognitionService;
use Illuminate\Support\Facades\Log;
use App\Enums\StatusVerifikasiWajah;

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
            'video_wajah' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/3gpp,video/x-matroska|max:51200',
        ], [
            'video_wajah.required' => 'File video wajah wajib diupload.',
            'video_wajah.file' => 'Data yang dikirim bukan file yang valid.',
            'video_wajah.mimetypes' => 'Format video tidak didukung. Gunakan MP4, MOV, atau AVI.',
            'video_wajah.max' => 'Ukuran video terlalu besar (maksimal 50MB).',
        ]);

        if ($validator->fails()) {
            Log::warning('Face Enrollment Validation Failed', [
                'errors' => $validator->errors()->toArray(),
                'has_file' => $request->hasFile('video_wajah'),
                'file_size' => $request->hasFile('video_wajah')
                    ? $request->file('video_wajah')->getSize()
                    : null,
                'file_mime' => $request->hasFile('video_wajah')
                    ? $request->file('video_wajah')->getMimeType()
                    : null,
            ]);

            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $video = $request->file('video_wajah');

            $result = $this->faceService->enrollFace($user->id, $video);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
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
            if ($dataWajah && $dataWajah->is_verified == StatusVerifikasiWajah::APPROVED) {
                $status = 'verified';
            } elseif ($dataWajah && $dataWajah->is_verified == StatusVerifikasiWajah::REJECTED) {
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
            'tipe' => 'nullable|string|in:presensi,test',
        ]);

        try {
            set_time_limit(0);

            $user = $request->user();
            $tipe = $request->input('tipe', 'presensi');

            $file = $request->file('foto');
            $result = $this->faceService->verifyFace($user->id, $file);

            \DB::table('log_verifikasi_wajah')->insert([
                'id_user' => $user->id,
                'confidence' => $result['confidence'] ?? null,
                'svm_confidence' => $result['svm_confidence'] ?? null,
                'normalized_distance' => $result['normalized_distance'] ?? null,
                'verification_status' => $result['verification_status'] ?? null,
                'is_match' => $result['verified'] ?? false,
                'blur_score' => $result['blur_score'] ?? null,
                'tipe' => $tipe,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wajah terverifikasi',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            if (isset($user)) {
                \DB::table('log_verifikasi_wajah')->insert([
                    'id_user' => $user->id,
                    'confidence' => 0,
                    'verification_status' => 'ERROR',
                    'is_match' => false,
                    'tipe' => $tipe ?? 'presensi',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Verifikasi Gagal: ' . $e->getMessage()
            ], 400);
        }
    }
}
