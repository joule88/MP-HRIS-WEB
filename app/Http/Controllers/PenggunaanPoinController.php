<?php

namespace App\Http\Controllers;

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
        $penggunaan = PenggunaanPoin::with(['user', 'jenisPengurangan', 'status'])
            ->orderByRaw("CASE WHEN id_status = 1 THEN 0 ELSE 1 END")
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

        try {
            if ($request->action == 'approve') {
                DB::transaction(function () use ($penggunaan) {
                    $penggunaan->update(['id_status' => 2]);
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
                $penggunaan->update([
                    'id_status' => 3,
                    'alasan_penolakan' => $request->alasan_penolakan
                ]);

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
