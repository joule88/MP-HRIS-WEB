<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanPresensiExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $divisiId = $request->input('id_divisi');

        $rekap = $this->buildRekap($bulan, $tahun, $divisiId);
        $divisiList = \App\Models\Divisi::all();

        return view('laporan.index', compact('rekap', 'bulan', 'tahun', 'divisiList', 'divisiId'));
    }

    public function cuti(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $search = $request->input('search');
        $divisiId = $request->input('id_divisi');

        $query = \App\Models\PengajuanIzin::with(['user.divisi', 'jenisIzin'])
            ->whereYear('tanggal_mulai', $tahun)
            ->whereMonth('tanggal_mulai', $bulan);

        if ($search) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        if ($divisiId) {
            $query->whereHas('user', function($q) use ($divisiId) {
                $q->where('id_divisi', $divisiId);
            });
        }

        $izinList = $query->orderBy('tanggal_mulai', 'desc')->paginate(15)->withQueryString();
        $divisiList = \App\Models\Divisi::all();

        return view('laporan.izin', compact('izinList', 'bulan', 'tahun', 'search', 'divisiId', 'divisiList'));
    }

    public function exportExcel(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $divisiId = $request->input('id_divisi');

        $rekap = $this->buildRekap($bulan, $tahun, $divisiId);

        $filename = "Laporan_Presensi_{$bulan}_{$tahun}.xlsx";

        return Excel::download(new LaporanPresensiExport($rekap, $bulan, $tahun), $filename);
    }

    public function exportPdf(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $divisiId = $request->input('id_divisi');

        $rekap = $this->buildRekap($bulan, $tahun, $divisiId);

        $pdf = Pdf::loadView('laporan.pdf', compact('rekap', 'bulan', 'tahun'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("Laporan_Presensi_{$bulan}_{$tahun}.pdf");
    }

    private function buildRekap(int $bulan, int $tahun, $divisiId = null): array
    {

        $idTepatWaktu = 1;
        $idTerlambat = 2;
        $idIzin = 3;
        $idSakit = 4;
        $idAlpha = 5;

        $pegawaiQuery = User::with(['jabatan', 'divisi'])
            ->where('status_aktif', 1)
            ->select('id', 'nama_lengkap', 'nik', 'id_jabatan', 'id_divisi')
            ->orderBy('nama_lengkap', 'asc');

        if ($divisiId) {
            $pegawaiQuery->where('id_divisi', $divisiId);
        }

        $pegawai = $pegawaiQuery->get();

        $presensiStats = Presensi::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->selectRaw("
                id_user,
                SUM(CASE WHEN id_status IN (?, ?) THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN id_status = ? THEN 1 ELSE 0 END) as terlambat,
                SUM(CASE WHEN id_status = ? THEN 1 ELSE 0 END) as alpha,
                SUM(CASE WHEN id_status = ? THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN id_status = ? THEN 1 ELSE 0 END) as sakit
            ", [$idTepatWaktu, $idTerlambat, $idTerlambat, $idAlpha, $idIzin, $idSakit])
            ->groupBy('id_user')
            ->get()
            ->keyBy('id_user');

        $poinStats = DB::table('poin_lembur')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->selectRaw('id_user, SUM(jumlah_poin) as total_poin')
            ->groupBy('id_user')
            ->pluck('total_poin', 'id_user');

        $rekap = [];

        foreach ($pegawai as $p) {
            $stats = $presensiStats->get($p->id);

            $rekap[] = [
                'user' => $p,
                'hadir' => $stats->hadir ?? 0,
                'terlambat' => $stats->terlambat ?? 0,
                'alpha' => $stats->alpha ?? 0,
                'izin' => $stats->izin ?? 0,
                'sakit' => $stats->sakit ?? 0,
                'poin_lembur' => $poinStats->get($p->id, 0)
            ];
        }

        return $rekap;
    }
}
