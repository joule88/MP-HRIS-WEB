<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Services\LemburService;
use App\Services\NotifikasiService;
use App\Http\Requests\StoreLemburRequest;
use App\Models\Lembur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LemburController extends Controller
{
    protected $lemburService;

    public function __construct(LemburService $lemburService)
    {
        $this->lemburService = $lemburService;
    }

    public function store(StoreLemburRequest $request)
    {
        try {
            $user = Auth::user();

            $this->lemburService->createLembur($user, $request->validated());

            app(NotifikasiService::class)->kirimKeRole(
                'hrd',
                'pengajuan_baru',
                'Pengajuan Lembur Baru',
                $user->nama_lengkap . ' mengajukan lembur.'
            );

            return ApiResponse::success(null, 'Pengajuan lembur berhasil dikirim.', 201);

        } catch (\Exception $e) {
            return ApiResponse::error('Gagal mengajukan lembur: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/lembur/{id}",
     *     tags={"Lembur"},
     *     summary="Update Overtime Request",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tanggal_lembur", "jam_mulai", "jam_selesai"},
     *             @OA\Property(property="tanggal_lembur", type="string", format="date", example="2023-10-25"),
     *             @OA\Property(property="jam_mulai", type="string", format="time", example="17:00"),
     *             @OA\Property(property="jam_selesai", type="string", format="time", example="20:00"),
     *             @OA\Property(property="keterangan", type="string", example="Updated reason"),
     *             @OA\Property(property="id_kompensasi", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Overtime request updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pengajuan lembur berhasil diperbarui.")
     *         )
     *     )
     * )
     */
    public function update(\App\Http\Requests\UpdateLemburRequest $request, $id)
    {
        try {

            $lembur = Lembur::where('id_user', Auth::id())
                ->where('id_lembur', $id)
                ->where('id_status', 1)
                ->first();

            if (!$lembur) {
                return ApiResponse::error('Data lembur tidak ditemukan atau sudah diproses.', 404);
            }

            $lembur->update($request->validated());

            return ApiResponse::success(null, 'Pengajuan lembur berhasil diperbarui.');

        } catch (\Exception $e) {
            return ApiResponse::error('Gagal memperbarui lembur: ' . $e->getMessage(), 500);
        }
    }

    public function history(Request $request)
    {
        try {
            $query = Lembur::with(['status', 'kompensasi'])
                ->where('id_user', Auth::id());

            if ($request->has('status')) {
                $statusParam = $request->get('status');

                $statusId = match ($statusParam) {
                    'pending' => 1,
                    'approved' => 2,
                    'rejected' => 3,
                    default => null
                };

                if ($statusId) {
                    $query->where('id_status', $statusId);
                }
            }

            $history = $query->orderBy('created_at', 'desc')->get();

            return ApiResponse::success(['data' => $history], 'Riwayat lembur berhasil dimuat');

        } catch (\Exception $e) {
            return ApiResponse::error('Gagal memuat riwayat lembur: ' . $e->getMessage(), 500);
        }
    }
}
