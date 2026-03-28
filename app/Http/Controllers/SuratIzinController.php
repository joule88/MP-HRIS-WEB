<?php

namespace App\Http\Controllers;

use App\Models\SuratIzin;
use App\Models\ApprovalSurat;
use App\Models\TandaTangan;
use App\Models\Presensi;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuratIzinController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGlobalAdmin = $user->isGlobalAdmin();

        $query = SuratIzin::with(['user', 'pengajuanIzin.jenisIzin', 'approvals.approver']);

        if (!$isGlobalAdmin) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('id_kantor', $user->id_kantor);
            });
        }

        if ($request->filled('status')) {
            $query->where('status_surat', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%');
            });
        }

        $suratList = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('surat-izin.index', compact('suratList'));
    }

    public function show($id)
    {
        $surat = SuratIzin::with([
            'user.jabatan',
            'user.divisi',
            'pengajuanIzin.jenisIzin',
            'tandaTanganPengaju',
            'approvals.approver.jabatan',
            'approvals.tandaTanganApprover',
        ])->findOrFail($id);

        $user = Auth::user();
        $isGlobalAdmin = $user->isGlobalAdmin();

        // Security Check: Office Isolation
        if (!$isGlobalAdmin && $surat->user->id_kantor != $user->id_kantor) {
            abort(403, 'Anda tidak diizinkan mengakses data dari kantor lain.');
        }

        $canApprove = false;
        $tahapApproval = null;

        if ($surat->status_surat === 'menunggu_manajer' && ($user->roles->contains('nama_role', 'manajer') || $isGlobalAdmin)) {
            $canApprove = true;
            $tahapApproval = 1;
        } elseif ($surat->status_surat === 'menunggu_hrd' && ($user->roles->contains('nama_role', 'hrd') || $isGlobalAdmin)) {
            $canApprove = true;
            $tahapApproval = 2;
        }

        $ttdApprover = TandaTangan::where('id_user', $user->id)->active()->first();

        return view('surat-izin.show', compact('surat', 'canApprove', 'tahapApproval', 'ttdApprover'));
    }

    public function approve(Request $request, $id)
    {
        try {
            $surat = SuratIzin::with('user')->findOrFail($id);
            $user = Auth::user();
            $isGlobalAdmin = $user->isGlobalAdmin();

            // Security Check
            if (!$isGlobalAdmin && $surat->user->id_kantor != $user->id_kantor) {
                return redirect()->back()->with('error', 'Anda tidak diizinkan menyetujui surat dari karyawan kantor lain.');
            }

            if (in_array($surat->status_surat, ['disetujui', 'ditolak'])) {
                return redirect()->back()->with('error', 'Surat ini sudah diproses sebelumnya.');
            }

            $tahap = null;

            if ($surat->status_surat === 'menunggu_manajer' && ($user->roles->contains('nama_role', 'manajer') || $isGlobalAdmin)) {
                $tahap = 1;
            } elseif ($surat->status_surat === 'menunggu_hrd' && ($user->roles->contains('nama_role', 'hrd') || $isGlobalAdmin)) {
                $tahap = 2;
            }

            if (!$tahap) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menyetujui surat ini.');
            }

            $ttd = TandaTangan::where('id_user', $user->id)->active()->first();
            if (!$ttd) {
                return redirect()->back()->with('error', 'Anda belum memiliki tanda tangan digital.');
            }

            DB::beginTransaction();

            ApprovalSurat::create([
                'id_surat' => $surat->id_surat,
                'id_approver' => $user->id,
                'id_ttd_approver' => $ttd->id_tanda_tangan,
                'tahap' => $tahap,
                'status' => 'disetujui',
                'catatan' => $request->catatan,
            ]);

            if ($tahap === 1) {
                $surat->update(['status_surat' => 'menunggu_hrd']);
            } elseif ($tahap === 2) {
                $surat->update(['status_surat' => 'disetujui']);
                $this->prosesPersetujuanFinal($surat);
            }

            DB::commit();

            if ($tahap === 1) {
                $statusLabel = 'Menunggu HRD';
                app(NotifikasiService::class)->kirim(
                    $surat->id_user,
                    'izin_proses',
                    'Surat Izin Diproses',
                    'Surat izin Anda telah disetujui Manajer dan sedang menunggu persetujuan HRD.',
                    ['id_surat' => $surat->id_surat]
                );
            } else {
                $statusLabel = 'Disetujui';
                app(NotifikasiService::class)->kirim(
                    $surat->id_user,
                    'izin_disetujui',
                    'Surat Izin Disetujui ✅',
                    'Surat izin Anda telah disetujui sepenuhnya.',
                    ['id_surat' => $surat->id_surat]
                );
            }

            return redirect()->back()->with('success', "Surat berhasil disetujui. Status: {$statusLabel}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $surat = SuratIzin::with('user')->findOrFail($id);
            $user = Auth::user();
            $isGlobalAdmin = $user->isGlobalAdmin();

            // Security Check
            if (!$isGlobalAdmin && $surat->user->id_kantor != $user->id_kantor) {
                return redirect()->back()->with('error', 'Anda tidak diizinkan menolak surat dari karyawan kantor lain.');
            }

            if (in_array($surat->status_surat, ['disetujui', 'ditolak'])) {
                return redirect()->back()->with('error', 'Surat ini sudah diproses sebelumnya.');
            }

            $tahap = null;

            if ($surat->status_surat === 'menunggu_manajer' && ($user->roles->contains('nama_role', 'manajer') || $isGlobalAdmin)) {
                $tahap = 1;
            } elseif ($surat->status_surat === 'menunggu_hrd' && ($user->roles->contains('nama_role', 'hrd') || $isGlobalAdmin)) {
                $tahap = 2;
            }

            if (!$tahap) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses.');
            }

            DB::beginTransaction();

            ApprovalSurat::create([
                'id_surat' => $surat->id_surat,
                'id_approver' => $user->id,
                'id_ttd_approver' => null,
                'tahap' => $tahap,
                'status' => 'ditolak',
                'catatan' => $request->catatan,
            ]);

            $surat->update(['status_surat' => 'ditolak']);

            if ($surat->pengajuanIzin) {
                $surat->pengajuanIzin->update(['id_status' => 3]);
            }

            DB::commit();

            app(NotifikasiService::class)->kirim(
                $surat->id_user,
                'izin_ditolak',
                'Surat Izin Ditolak ❌',
                'Surat izin Anda ditolak. Catatan: ' . ($request->catatan ?? '-'),
                ['id_surat' => $surat->id_surat]
            );

            return redirect()->back()->with('success', 'Surat izin berhasil ditolak.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    private function prosesPersetujuanFinal(SuratIzin $surat)
    {
        $izin = $surat->pengajuanIzin;
        if (!$izin)
            return;

        $izin->load('jenisIzin');
        $izin->update(['id_status' => 2]);

        $statusPresensiId = 3;
        if ($izin->id_jenis_izin == 1) {
            $statusPresensiId = 4;
        }

        $startDate = Carbon::parse($izin->tanggal_mulai, 'Asia/Jakarta');
        $endDate = Carbon::parse($izin->tanggal_selesai, 'Asia/Jakarta');

        while ($startDate->lte($endDate)) {
            Presensi::updateOrCreate(
                [
                    'id_user' => $izin->id_user,
                    'tanggal' => $startDate->format('Y-m-d'),
                ],
                [
                    'id_status' => $statusPresensiId,
                    'jam_masuk' => null,
                    'jam_pulang' => null,
                    'id_validasi' => 1,
                    'alasan_telat' => $izin->jenisIzin->nama_izin . ': ' . $izin->alasan,
                ]
            );
            $startDate->addDay();
        }

        if ($izin->id_jenis_izin == 2) {
            $user = \App\Models\User::find($izin->id_user, ['*']);
            $jumlahHari = Carbon::parse($izin->tanggal_mulai)->diffInDays(Carbon::parse($izin->tanggal_selesai)) + 1;
            $user->decrement('sisa_cuti', $jumlahHari);
        }
    }
}
