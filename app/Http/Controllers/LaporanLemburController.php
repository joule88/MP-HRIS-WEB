<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Lembur;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanLemburExport;

class LaporanLemburController extends Controller
{

    public function index(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $search = $request->input('search');
        $divisiId = $request->input('id_divisi');

        $query = User::with(['divisi', 'jabatan', 'lemburs' => function($q) use ($bulan, $tahun) {
            $q->whereYear('tanggal_lembur', $tahun)
              ->whereMonth('tanggal_lembur', $bulan)
              ->where('id_status', 2);
        }])
        ->whereHas('roles', function($q) {
            $q->where('nama_role', '!=', 'super_admin');
        });

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%')
                  ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }

        if ($divisiId) {
            $query->where('id_divisi', $divisiId);
        }

        $pegawai = $query->orderBy('nama_lengkap', 'asc')->paginate(15)->withQueryString();
        $divisiList = \App\Models\Divisi::all();

        $rekap = [];
        foreach ($pegawai as $p) {
            $totalMenit = $p->lemburs->sum('durasi_menit');
            $jam = floor($totalMenit / 60);
            $menit = $totalMenit % 60;

            $rekap[$p->id] = [
                'total_menit'    => $totalMenit,
                'format_jam'     => "{$jam}j {$menit}m",
                'jumlah_hari'    => $p->lemburs->count(),
                'poin_diperoleh' => $p->lemburs->sum('jumlah_poin'),
            ];
        }

        return view('laporan-lembur.index', compact('pegawai', 'rekap', 'bulan', 'tahun', 'search', 'divisiId', 'divisiList'));
    }

    public function exportExcel(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $divisiId = $request->input('id_divisi');

        $query = User::with(['divisi', 'jabatan', 'lemburs' => function($q) use ($bulan, $tahun) {
            $q->whereYear('tanggal_lembur', $tahun)
              ->whereMonth('tanggal_lembur', $bulan)
              ->where('id_status', 2);
        }])
        ->whereHas('roles', function($q) {
            $q->where('nama_role', '!=', 'super_admin');
        });

        if ($divisiId) {
            $query->where('id_divisi', $divisiId);
        }

        $pegawai = $query->orderBy('nama_lengkap', 'asc')->get();

        // Guard: cek apakah ada data lembur di periode tersebut
        $adaDataLembur = $pegawai->contains(fn($p) => $p->lemburs->isNotEmpty());
        if (!$adaDataLembur) {
            return redirect()->back()->with('error', 'Tidak ada data lembur yang disetujui untuk diekspor pada periode tersebut.');
        }

        $rekap = [];
        foreach ($pegawai as $p) {
            $totalMenit = $p->lemburs->sum('durasi_menit');
            $jam = floor($totalMenit / 60);
            $menit = $totalMenit % 60;

            $rekap[$p->id] = [
                'total_menit'    => $totalMenit,
                'format_jam'     => "{$jam}j {$menit}m",
                'jumlah_hari'    => $p->lemburs->count(),
                'poin_diperoleh' => $p->lemburs->sum('jumlah_poin'),
            ];
        }

        $filename = "Laporan_Lembur_{$bulan}_{$tahun}.xlsx";

        return Excel::download(new LaporanLemburExport($pegawai, $rekap, $bulan, $tahun), $filename);
    }
}
