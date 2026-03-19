<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use App\Models\JenisIzin;
use App\Models\SuratIzin;
use App\Models\TandaTangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubmissionController extends Controller
{

    public function types()
    {
        $types = JenisIzin::all();
        return ApiResponse::success($types);
    }

    public function store(\App\Http\Requests\StorePengajuanIzinRequest $request)
    {
        try {
            $user = Auth::user();

            $jenisIzin = JenisIzin::find($request->id_jenis_izin);
            if ($jenisIzin && $jenisIzin->nama_izin == 'Cuti') {
                $tanggalMulai = Carbon::parse($request->tanggal_mulai);
                $minDate = Carbon::now()->addDays(7)->startOfDay();

                if ($tanggalMulai->lt($minDate)) {
                    return ApiResponse::error('Pengajuan Cuti minimal H-7!', 400);
                }
            }

            DB::beginTransaction();

            $path = null;
            if ($request->hasFile('bukti_file')) {
                $path = $request->file('bukti_file')->store('uploads/izin', 'public');
            }

            $submission = PengajuanIzin::create([
                'id_user' => $user->id,
                'id_jenis_izin' => $request->id_jenis_izin,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'alasan' => $request->alasan,
                'bukti_file' => $path,
                'id_status' => 1
            ]);

            $ttdAktif = TandaTangan::where('id_user', $user->id)->active()->first();

            $namaJenisIzin = $jenisIzin->nama_izin ?? 'Izin';
            $tglMulai = Carbon::parse($request->tanggal_mulai)->translatedFormat('d F Y');
            $tglSelesai = Carbon::parse($request->tanggal_selesai)->translatedFormat('d F Y');

            $isiSurat = "Dengan hormat,\n\n"
                . "Saya yang bertanda tangan di bawah ini:\n"
                . "Nama: {$user->nama_lengkap}\n"
                . "NIK: {$user->nik}\n"
                . "Jabatan: " . ($user->jabatan->nama_jabatan ?? '-') . "\n"
                . "Divisi: " . ($user->divisi->nama_divisi ?? '-') . "\n\n"
                . "Dengan ini mengajukan {$namaJenisIzin} mulai tanggal {$tglMulai} sampai dengan {$tglSelesai}.\n\n"
                . "Alasan: {$request->alasan}\n\n"
                . "Demikian surat ini saya buat dengan sebenar-benarnya. "
                . "Atas perhatian dan persetujuannya, saya ucapkan terima kasih.";

            $suratIzin = null;
            if ($jenisIzin && $jenisIzin->nama_izin == 'Cuti') {
                $suratIzin = SuratIzin::create([
                    'id_izin' => $submission->id_izin,
                    'id_user' => $user->id,
                    'id_ttd_pengaju' => $ttdAktif?->id_tanda_tangan,
                    'isi_surat' => $isiSurat,
                    'status_surat' => 'menunggu_manajer',
                ]);
            }

            DB::commit();

            $responseData = $submission->toArray();
            if ($suratIzin) {
                $responseData['surat_izin'] = [
                    'id_surat' => $suratIzin->id_surat,
                    'nomor_surat' => $suratIzin->nomor_surat,
                    'status_surat' => $suratIzin->status_surat,
                    'has_ttd' => $ttdAktif !== null,
                ];
            } else {
                $responseData['surat_izin'] = null;
            }

            $message = 'Pengajuan & surat izin berhasil dibuat.';
            if (!$ttdAktif) {
                $message .= ' (TTD belum tersedia, silakan buat di menu Profil)';
            }

            return ApiResponse::success($responseData, $message, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Gagal mengirim pengajuan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/submission/{id}",
     *     tags={"Submission"},
     *     summary="Update Submission",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"id_jenis_izin", "tanggal_mulai", "tanggal_selesai", "alasan"},
     *                 @OA\Property(property="id_jenis_izin", type="integer", example=1),
     *                 @OA\Property(property="tanggal_mulai", type="string", format="date"),
     *                 @OA\Property(property="tanggal_selesai", type="string", format="date"),
     *                 @OA\Property(property="alasan", type="string"),
     *                 @OA\Property(property="bukti_file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Submission updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(\App\Http\Requests\UpdatePengajuanIzinRequest $request, $id)
    {
        try {
            $submission = PengajuanIzin::where('id_user', Auth::id())
                ->where('id_izin', $id)
                ->where('id_status', 1)
                ->first();

            if (!$submission) {
                return ApiResponse::error('Pengajuan tidak ditemukan atau sudah diproses.', 404);
            }

            DB::beginTransaction();

            $data = $request->validated();

            if ($request->hasFile('bukti_file')) {

                if ($submission->bukti_file && \Illuminate\Support\Facades\Storage::disk('public')->exists($submission->bukti_file)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($submission->bukti_file);
                }
                $data['bukti_file'] = $request->file('bukti_file')->store('uploads/izin', 'public');
            }

            $submission->update($data);

            DB::commit();

            return ApiResponse::success(null, 'Pengajuan berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Gagal memperbarui pengajuan: ' . $e->getMessage(), 500);
        }
    }

    public function history(Request $request)
    {
        try {
            $query = PengajuanIzin::with(['jenisIzin', 'statusPengajuan'])
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

            $history = $query->orderBy('created_at', 'desc')->paginate(10);

            return ApiResponse::success($history);

        } catch (\Exception $e) {
            return ApiResponse::error('Gagal memuat riwayat: ' . $e->getMessage(), 500);
        }
    }
}
