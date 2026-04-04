<?php

namespace App\Http\Controllers\Api;

use App\Enums\JenisIzin as JenisIzinEnum;
use App\Enums\StatusPengajuan;
use App\Enums\StatusSurat;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use App\Models\JenisIzin;
use App\Models\SuratIzin;
use App\Models\TandaTangan;
use App\Services\NotifikasiService;
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
            if ($jenisIzin && $jenisIzin->id_jenis_izin == JenisIzinEnum::CUTI) {
                $tanggalMulai = Carbon::parse($request->tanggal_mulai);
                $tanggalSelesai = Carbon::parse($request->tanggal_selesai);
                $jumlahHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;

                if ($user->sisa_cuti <= 0) {
                    return ApiResponse::error('Sisa cuti Anda sudah habis (0 hari). Pengajuan cuti tidak dapat dilanjutkan.', 400);
                }

                if ($jumlahHari > 3) {
                    return ApiResponse::error('Pengajuan cuti maksimal 3 hari berturut-turut dalam satu pengajuan.', 400);
                }

                if ($jumlahHari > $user->sisa_cuti) {
                    return ApiResponse::error("Sisa cuti Anda tidak mencukupi. Sisa: {$user->sisa_cuti} hari, diajukan: {$jumlahHari} hari.", 400);
                }

                $minDate = Carbon::now()->addDays(7)->startOfDay();
                if ($tanggalMulai->lt($minDate)) {
                    return ApiResponse::error('Pengajuan Cuti minimal H-7!', 400);
                }
            }

            if ($jenisIzin && $jenisIzin->id_jenis_izin == JenisIzinEnum::SAKIT) {
                $tanggalMulai = Carbon::parse($request->tanggal_mulai);
                $tanggalSelesai = Carbon::parse($request->tanggal_selesai);
                $jumlahHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;

                $sudahSakitBulanIni = PengajuanIzin::where('id_user', $user->id)
                    ->where('id_jenis_izin', JenisIzinEnum::SAKIT)
                    ->whereIn('id_status', [StatusPengajuan::PENDING, StatusPengajuan::DISETUJUI])
                    ->whereMonth('tanggal_mulai', $tanggalMulai->month)
                    ->whereYear('tanggal_mulai', $tanggalMulai->year)
                    ->exists();

                if (($jumlahHari > 1 || $sudahSakitBulanIni) && !$request->hasFile('bukti_file')) {
                    return ApiResponse::error(
                        $jumlahHari > 1
                            ? 'Izin sakit lebih dari 1 hari wajib melampirkan Surat Keterangan Dokter (SKD).'
                            : 'Anda sudah pernah izin sakit di bulan ini. Wajib melampirkan Surat Keterangan Dokter (SKD).',
                        422
                    );
                }
            }

            $tglMulai = $request->tanggal_mulai;
            $tglSelesai = $request->tanggal_selesai;
            $userId = $user->id;

            $overlappingIzin = PengajuanIzin::where('id_user', $userId)
                ->whereIn('id_status', [StatusPengajuan::PENDING, StatusPengajuan::DISETUJUI])
                ->where(function ($q) use ($tglMulai, $tglSelesai) {
                    $q->whereBetween('tanggal_mulai', [$tglMulai, $tglSelesai])
                      ->orWhereBetween('tanggal_selesai', [$tglMulai, $tglSelesai])
                      ->orWhere(function ($q2) use ($tglMulai, $tglSelesai) {
                          $q2->where('tanggal_mulai', '<=', $tglMulai)
                             ->where('tanggal_selesai', '>=', $tglSelesai);
                      });
                })->exists();

            if ($overlappingIzin) {
                return ApiResponse::error('Anda sudah memiliki pengajuan Izin/Cuti (Pending/Disetujui) pada rentang tanggal tersebut!', 400);
            }

            $existingPresensi = \App\Models\Presensi::where('id_user', $userId)
                ->whereBetween('tanggal', [$tglMulai, $tglSelesai])
                ->whereNotNull('jam_masuk')
                ->exists();

            if ($existingPresensi) {
                return ApiResponse::error('Anda sudah tercatat absen masuk (hadir) pada rentang tanggal tersebut!', 400);
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
                'id_status' => StatusPengajuan::PENDING
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
            if ($jenisIzin && $jenisIzin->id_jenis_izin == JenisIzinEnum::CUTI) {
                $suratIzin = SuratIzin::create([
                    'id_izin' => $submission->getKey(),
                    'id_user' => $user->id,
                    'id_ttd_pengaju' => $ttdAktif?->id_tanda_tangan,
                    'isi_surat' => $isiSurat,
                    'status_surat' => StatusSurat::MENUNGGU_MANAJER,
                ]);
            }

            DB::commit();

            app(NotifikasiService::class)->kirimKeRole(
                'hrd',
                'pengajuan_baru',
                'Pengajuan Baru: ' . ($jenisIzin->nama_izin ?? 'Izin'),
                $user->nama_lengkap . ' mengajukan ' . ($jenisIzin->nama_izin ?? 'izin') . '.',
                ['id_izin' => $submission->getKey()]
            );

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
                ->where('id_status', StatusPengajuan::PENDING)
                ->first();

            if (!$submission) {
                return ApiResponse::error('Pengajuan tidak ditemukan atau sudah diproses.', 404);
            }

            $tglMulai = $request->tanggal_mulai;
            $tglSelesai = $request->tanggal_selesai;
            $userId = Auth::id();

            $overlappingIzin = PengajuanIzin::where('id_user', $userId)
                ->where('id_izin', '!=', $id)
                ->whereIn('id_status', [StatusPengajuan::PENDING, StatusPengajuan::DISETUJUI])
                ->where(function ($q) use ($tglMulai, $tglSelesai) {
                    $q->whereBetween('tanggal_mulai', [$tglMulai, $tglSelesai])
                      ->orWhereBetween('tanggal_selesai', [$tglMulai, $tglSelesai])
                      ->orWhere(function ($q2) use ($tglMulai, $tglSelesai) {
                          $q2->where('tanggal_mulai', '<=', $tglMulai)
                             ->where('tanggal_selesai', '>=', $tglSelesai);
                      });
                })->exists();

            if ($overlappingIzin) {
                return ApiResponse::error('Anda sudah memiliki pengajuan Izin/Cuti lain (Pending/Disetujui) pada rentang tanggal tersebut!', 400);
            }

            $existingPresensi = \App\Models\Presensi::where('id_user', $userId)
                ->whereBetween('tanggal', [$tglMulai, $tglSelesai])
                ->whereNotNull('jam_masuk')
                ->exists();

            if ($existingPresensi) {
                return ApiResponse::error('Anda sudah tercatat absen masuk (hadir) pada rentang tanggal tersebut!', 400);
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
            $query = PengajuanIzin::with(['jenisIzin', 'statusPengajuan', 'suratIzin'])
                ->where('id_user', Auth::id());

            if ($request->has('status')) {
                $statusParam = $request->get('status');

                $statusId = match ($statusParam) {
                    'pending' => StatusPengajuan::PENDING,
                    'approved' => StatusPengajuan::DISETUJUI,
                    'rejected' => StatusPengajuan::DITOLAK,
                    default => null
                };

                if ($statusId) {
                    $query->where('id_status', $statusId);
                }
            }

            $history = $query->orderBy('created_at', 'desc')->paginate(10);

            $history->getCollection()->transform(function ($item) {
                $item->bukti_file_url = $item->bukti_file
                    ? asset('storage/' . $item->bukti_file)
                    : null;
                $item->has_surat = $item->suratIzin !== null;
                $item->id_surat = $item->suratIzin?->id_surat;
                unset($item->suratIzin);
                return $item;
            });

            return ApiResponse::success($history);

        } catch (\Exception $e) {
            return ApiResponse::error('Gagal memuat riwayat: ' . $e->getMessage(), 500);
        }
    }
}
