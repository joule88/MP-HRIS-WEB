<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kantor;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $hariIni = Carbon::today()->format('Y-m-d');
        $totalPegawai = User::where('status_aktif', 1)->count();

        $rekapRaw = Presensi::whereDate('tanggal', $hariIni)
            ->selectRaw("
                SUM(CASE WHEN id_status IN (1,2) THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN id_status = 2 THEN 1 ELSE 0 END) as terlambat,
                SUM(CASE WHEN id_status = 3 THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN id_status = 4 THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN id_status = 5 THEN 1 ELSE 0 END) as alpha
            ")->first();

        $rekap = [
            'hadir' => $rekapRaw->hadir ?? 0,
            'izin' => $rekapRaw->izin ?? 0,
            'sakit' => $rekapRaw->sakit ?? 0,
            'alpha' => $rekapRaw->alpha ?? 0,
            'terlambat' => $rekapRaw->terlambat ?? 0
        ];

        $recentActivities = Presensi::with(['user.jabatan'])
            ->whereDate('tanggal', $hariIni)
            ->whereNotNull('jam_masuk')
            ->orderBy('jam_masuk', 'desc')
            ->take(5)
            ->get();

        $pendingIzin = \App\Models\PengajuanIzin::with('user')->where('id_status', 1)->orderBy('created_at', 'desc')->take(5)->get();
        $pendingSuratIzin = \App\Models\SuratIzin::with('user')->whereIn('status_surat', ['menunggu_manajer', 'menunggu_hrd'])->orderBy('created_at', 'desc')->take(5)->get();
        $pendingLembur = \App\Models\Lembur::with('user')->where('id_status', 1)->orderBy('created_at', 'desc')->take(5)->get();
        $pendingPoin = \App\Models\PenggunaanPoin::with('user')->where('id_status', 1)->orderBy('created_at', 'desc')->take(5)->get();
        $pendingPresensi = Presensi::with('user')->where('id_validasi', 2)->orderBy('created_at', 'desc')->take(5)->get();

        $pengajuanPending = [
            'izin' => $pendingIzin,
            'surat_izin' => $pendingSuratIzin,
            'lembur' => $pendingLembur,
            'poin' => $pendingPoin,
            'presensi' => $pendingPresensi
        ];

        $startDate = Carbon::today()->subDays(6)->format('Y-m-d');
        $endDate = $hariIni;

        $chartRaw = Presensi::whereBetween('tanggal', [$startDate, $endDate])
            ->whereIn('id_status', [1, 2])
            ->selectRaw("DATE(tanggal) as tgl, COUNT(*) as total")
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->pluck('total', 'tgl');

        $chartLabels = [];
        $chartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('d/m');
            $chartData[] = $chartRaw->get($date->format('Y-m-d'), 0);
        }

        return view('dashboard.index', compact('rekap', 'recentActivities', 'pengajuanPending', 'chartLabels', 'chartData', 'totalPegawai'));
    }
}
