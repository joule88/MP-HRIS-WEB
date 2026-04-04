<?php

namespace App\Http\Controllers;

use App\Enums\StatusPengajuan;
use App\Models\PenggunaanPoin;
use App\Services\PoinService;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenggunaanPoinController extends Controller
{
    protected $poinService;

    public function __construct(PoinService $poinService)
    {
        $this->poinService = $poinService;
    }

    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $isGlobalAdmin = $user->isGlobalAdmin();

        $query = PenggunaanPoin::with(['user', 'jenisPengurangan', 'status']);

        if (!$isGlobalAdmin) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('id_kantor', $user->id_kantor);
            });
        }

        $penggunaan = $query->orderByRaw("CASE WHEN id_status = " . StatusPengajuan::PENDING . " THEN 0 ELSE 1 END")
            ->orderBy('tanggal_diajukan', 'desc')
            ->paginate(10);

        return view('penggunaan-poin.index', compact('penggunaan'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'alasan_penolakan' => 'required_if:action,reject|nullable|string'
        ]);

        $penggunaan = PenggunaanPoin::findOrFail($id);

        $penggunaan->load('user');
        $userAuth = \Illuminate\Support\Facades\Auth::user();
        if (!$userAuth->isGlobalAdmin() && $penggunaan->user->id_kantor != $userAuth->id_kantor) {
            return redirect()->back()->with('error', 'Anda tidak diizinkan memproses data dari kantor lain.');
        }

        if ($request->action == 'approve' && $penggunaan->id_status != StatusPengajuan::PENDING) {
            return redirect()->back()->with('error', 'Hanya pengajuan PENDING yang dapat disetujui.');
        }
        
        if ($request->action == 'reject' && in_array($penggunaan->id_status, [StatusPengajuan::DITOLAK])) {
            return redirect()->back()->with('error', 'Pengajuan ini sudah ditolak.');
        }

        try {
            if ($request->action == 'approve') {
                $saldoAktif = $this->poinService->getActivePoints($penggunaan->id_user);
                if ($saldoAktif < $penggunaan->jumlah_poin) {
                    return redirect()->back()->with('error',
                        'Saldo poin pegawai tidak mencukupi (mungkin sudah expired). Saldo aktif: ' . $saldoAktif . ', dibutuhkan: ' . $penggunaan->jumlah_poin
                    );
                }

                DB::transaction(function () use ($penggunaan) {
                    $penggunaan->update(['id_status' => StatusPengajuan::DISETUJUI]);
                    $this->poinService->deductPoin(
                        $penggunaan->id_user,
                        $penggunaan->jumlah_poin,
                        $penggunaan->id_penggunaan
                    );
                });

                app(NotifikasiService::class)->kirim(
                    $penggunaan->id_user,
                    'poin_disetujui',
                    'Penggunaan Poin Disetujui',
                    'Pengajuan penggunaan poin Anda pada tanggal ' . $penggunaan->tanggal_penggunaan . ' telah disetujui.',
                    ['id_penggunaan' => $penggunaan->id_penggunaan]
                );

                $message = 'Pengajuan berhasil disetujui dan poin telah dipotong.';
            } else {
                DB::transaction(function () use ($penggunaan, $request) {
                    if ($penggunaan->id_status == StatusPengajuan::DISETUJUI) {
                        $this->poinService->refundPoin($penggunaan->id_penggunaan);
                    }
                    $penggunaan->update([
                        'id_status' => StatusPengajuan::DITOLAK,
                        'alasan_penolakan' => $request->alasan_penolakan
                    ]);
                });

                app(NotifikasiService::class)->kirim(
                    $penggunaan->id_user,
                    'poin_ditolak',
                    'Penggunaan Poin Ditolak',
                    'Pengajuan penggunaan poin Anda pada tanggal ' . $penggunaan->tanggal_penggunaan . ' ditolak.',
                    ['id_penggunaan' => $penggunaan->id_penggunaan]
                );

                $message = 'Pengajuan berhasil ditolak.';
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }
}
