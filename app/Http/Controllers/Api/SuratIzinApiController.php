<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSuratIzinRequest;
use App\Models\SuratIzin;
use App\Models\PengajuanIzin;
use App\Models\TandaTangan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuratIzinApiController extends Controller
{
    public function index()
    {
        try {
            $surat = SuratIzin::with([
                'pengajuanIzin.jenisIzin',
                'tandaTanganPengaju',
                'approvals.approver',
                'approvals.tandaTanganApprover',
            ])
                ->where('id_user', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $data = $surat->through(function ($item) {
                return $this->formatSurat($item);
            });

            return ApiResponse::success($data);

        } catch (\Exception $e) {
            return ApiResponse::error('Gagal memuat daftar surat: ' . $e->getMessage(), 500);
        }
    }

    public function store(StoreSuratIzinRequest $request)
    {
        try {
            $user = Auth::user();

            $pengajuan = PengajuanIzin::with('jenisIzin')
                ->where('id_izin', $request->id_izin)
                ->where('id_user', $user->id)
                ->first();

            if (!$pengajuan) {
                return ApiResponse::notFound('Pengajuan izin tidak ditemukan.');
            }

            $existing = SuratIzin::where('id_izin', $request->id_izin)->first();
            if ($existing) {
                return ApiResponse::error('Surat izin untuk pengajuan ini sudah ada.', 400);
            }

            $ttd = TandaTangan::where('id_user', $user->id)->active()->first();
            if (!$ttd) {
                return ApiResponse::error('Anda belum memiliki tanda tangan digital. Silakan buat terlebih dahulu.', 400);
            }

            DB::beginTransaction();

            $surat = SuratIzin::create([
                'id_izin' => $pengajuan->id_izin,
                'id_user' => $user->id,
                'isi_surat' => $request->isi_surat,
                'id_ttd_pengaju' => $ttd->id_tanda_tangan,
                'status_surat' => 'menunggu_manajer',
            ]);

            DB::commit();

            $surat->load(['tandaTanganPengaju', 'approvals']);

            return ApiResponse::success(
                $this->formatSurat($surat),
                'Surat izin berhasil dibuat.',
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Gagal membuat surat izin: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $surat = SuratIzin::with([
                'pengajuanIzin.jenisIzin',
                'user.jabatan',
                'user.divisi',
                'tandaTanganPengaju',
                'approvals.approver.jabatan',
                'approvals.tandaTanganApprover',
            ])
                ->where('id_user', Auth::id())
                ->where('id_surat', $id)
                ->first();

            if (!$surat) {
                return ApiResponse::notFound('Surat izin tidak ditemukan.');
            }

            return ApiResponse::success($this->formatSurat($surat, true));

        } catch (\Exception $e) {
            return ApiResponse::error('Gagal memuat detail surat: ' . $e->getMessage(), 500);
        }
    }

    private function formatSurat(SuratIzin $surat, bool $detail = false): array
    {
        $data = [
            'id_surat' => $surat->id_surat,
            'nomor_surat' => $surat->nomor_surat,
            'status_surat' => $surat->status_surat,
            'jenis_izin' => $surat->pengajuanIzin?->jenisIzin?->nama_izin ?? 'N/A',
            'created_at' => $surat->created_at?->toISOString(),
            'ttd_pengaju' => $surat->tandaTanganPengaju ? asset('storage/' . $surat->tandaTanganPengaju->file_ttd) : null,
            'approvals' => $surat->approvals->map(function ($approval) {
                return [
                    'tahap' => $approval->tahap,
                    'tahap_label' => $approval->tahap == 1 ? 'Manajer' : 'HRD',
                    'status' => $approval->status,
                    'approver_nama' => $approval->approver?->nama_lengkap ?? 'N/A',
                    'approver_jabatan' => $approval->approver?->jabatan?->nama_jabatan ?? 'N/A',
                    'ttd_approver' => $approval->tandaTanganApprover ? asset('storage/' . $approval->tandaTanganApprover->file_ttd) : null,
                    'catatan' => $approval->catatan,
                    'created_at' => $approval->created_at?->toISOString(),
                ];
            })->toArray(),
        ];

        if ($detail) {
            $data['isi_surat'] = $surat->isi_surat;
            $data['pengaju'] = [
                'nama' => $surat->user?->nama_lengkap,
                'nik' => $surat->user?->nik,
                'jabatan' => $surat->user?->jabatan?->nama_jabatan ?? 'N/A',
                'divisi' => $surat->user?->divisi?->nama_divisi ?? 'N/A',
            ];
            $data['pengajuan'] = [
                'id_izin' => $surat->pengajuanIzin?->id_izin,
                'tanggal_mulai' => $surat->pengajuanIzin?->tanggal_mulai,
                'tanggal_selesai' => $surat->pengajuanIzin?->tanggal_selesai,
                'alasan' => $surat->pengajuanIzin?->alasan,
            ];
        }

        return $data;
    }
}
