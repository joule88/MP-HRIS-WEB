<?php

namespace App\Http\Controllers;

use App\Enums\StatusPengajuan;
use App\Models\User;
use App\Models\Lembur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanLemburExport;

class LaporanLemburController extends Controller
{

    public function index(Request $request)
    {
        $bulan    = $request->input('bulan', date('m'));
        $tahun    = $request->input('tahun', date('Y'));
        $search   = $request->input('search');
        $divisiId = $request->input('id_divisi');

        $authUser      = Auth::user();
        $isGlobalAdmin = $authUser->isGlobalAdmin();

        [$pegawai, $rekap] = $this->buildRekap(
            $bulan, $tahun, $divisiId,
            $isGlobalAdmin ? null : $authUser->id_kantor,
            $search
        );

        $divisiList = \App\Models\Divisi::all();

        return view('laporan-lembur.index', compact('pegawai', 'rekap', 'bulan', 'tahun', 'search', 'divisiId', 'divisiList'));
    }

    public function exportExcel(Request $request)
    {
        $bulan    = $request->input('bulan', date('m'));
        $tahun    = $request->input('tahun', date('Y'));
        $divisiId = $request->input('id_divisi');

        $authUser      = Auth::user();
        $isGlobalAdmin = $authUser->isGlobalAdmin();

        [$pegawai, $rekap] = $this->buildRekap(
            $bulan, $tahun, $divisiId,
            $isGlobalAdmin ? null : $authUser->id_kantor
        );

        $adaDataLembur = $pegawai->contains(fn($p) => $p->lemburs->isNotEmpty());
        if (!$adaDataLembur) {
            return redirect()->back()->with('error', 'Tidak ada data lembur yang disetujui untuk diekspor pada periode tersebut.');
        }

        $filename = "Laporan_Lembur_{$bulan}_{$tahun}.xlsx";

        return Excel::download(new LaporanLemburExport($pegawai, $rekap, $bulan, $tahun), $filename);
    }

    private function buildRekap(string $bulan, string $tahun, $divisiId = null, $kantorId = null, $search = null): array
    {
        $query = User::with(['divisi', 'jabatan', 'lemburs' => function ($q) use ($bulan, $tahun) {
            $q->whereYear('tanggal_lembur', $tahun)
              ->whereMonth('tanggal_lembur', $bulan)
              ->where('id_status', StatusPengajuan::DISETUJUI);
        }])
        ->bukanHrd();

        if ($kantorId) {
            $query->where('id_kantor', $kantorId);
        }

        if ($divisiId) {
            $query->where('id_divisi', $divisiId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%')
                  ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }

        $pegawai = $query->orderBy('nama_lengkap', 'asc')->paginate(15)->withQueryString();

        $rekap = [];
        foreach ($pegawai as $p) {
            $totalMenit = $p->lemburs->sum('durasi_menit');
            $jam        = floor($totalMenit / 60);
            $menit      = $totalMenit % 60;

            $rekap[$p->id] = [
                'total_menit'    => $totalMenit,
                'format_jam'     => "{$jam}j {$menit}m",
                'jumlah_hari'    => $p->lemburs->count(),
                'poin_diperoleh' => $p->lemburs->sum('jumlah_poin'),
            ];
        }

        return [$pegawai, $rekap];
    }
}
