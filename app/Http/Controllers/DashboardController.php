<?php

namespace App\Http\Controllers;

use App\Enums\StatusPengajuan;
use App\Enums\StatusPresensi;
use App\Enums\StatusSurat;
use App\Enums\StatusValidasi;
use App\Models\User;
use App\Models\Kantor;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isGlobalAdmin = $user->isGlobalAdmin();
        $hariIni = Carbon::today()->format('Y-m-d');

        $pegawaiQuery = User::where('status_aktif', 1);
        if (!$isGlobalAdmin) {
            $pegawaiQuery->where('id_kantor', $user->id_kantor);
        }
        $totalPegawai = $pegawaiQuery->count();

        $rekapQuery = Presensi::whereDate('tanggal', $hariIni);
        if (!$isGlobalAdmin) {
            $rekapQuery->whereHas('user', fn($q) => $q->where('id_kantor', $user->id_kantor));
        }
        $rekapRaw = $rekapQuery->selectRaw("
                SUM(CASE WHEN id_status IN (?,?) THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN id_status = ? THEN 1 ELSE 0 END) as terlambat,
                SUM(CASE WHEN id_status = ? THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN id_status = ? THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN id_status = ? THEN 1 ELSE 0 END) as alpha
            ", [
                StatusPresensi::TEPAT_WAKTU, StatusPresensi::TERLAMBAT,
                StatusPresensi::TERLAMBAT,
                StatusPresensi::IZIN,
                StatusPresensi::SAKIT,
                StatusPresensi::ALPHA,
            ])->first();

        $rekap = [
            'hadir' => $rekapRaw->hadir ?? 0,
            'izin' => $rekapRaw->izin ?? 0,
            'sakit' => $rekapRaw->sakit ?? 0,
            'alpha' => $rekapRaw->alpha ?? 0,
            'terlambat' => $rekapRaw->terlambat ?? 0
        ];

        $recentQuery = Presensi::with(['user.jabatan'])
            ->whereDate('tanggal', $hariIni)
            ->whereNotNull('jam_masuk');
        if (!$isGlobalAdmin) {
            $recentQuery->whereHas('user', fn($q) => $q->where('id_kantor', $user->id_kantor));
        }
        $recentActivities = $recentQuery->orderBy('jam_masuk', 'desc')->take(5)->get();

        $pendingIzinQuery = \App\Models\PengajuanIzin::with('user')->where('id_status', StatusPengajuan::PENDING);
        $pendingSuratQuery = \App\Models\SuratIzin::with('user')->whereIn('status_surat', [StatusSurat::MENUNGGU_MANAJER, StatusSurat::MENUNGGU_HRD]);
        $pendingLemburQuery = \App\Models\Lembur::with('user')->where('id_status', StatusPengajuan::PENDING);
        $pendingPoinQuery = \App\Models\PenggunaanPoin::with('user')->where('id_status', StatusPengajuan::PENDING);
        $pendingPresensiQuery = Presensi::with('user')->where('id_validasi', StatusValidasi::PENDING);

        if (!$isGlobalAdmin) {
            $kanторFilter = fn($q) => $q->where('id_kantor', $user->id_kantor);
            $pendingIzinQuery->whereHas('user', $kanторFilter);
            $pendingSuratQuery->whereHas('user', $kanторFilter);
            $pendingLemburQuery->whereHas('user', $kanторFilter);
            $pendingPoinQuery->whereHas('user', $kanторFilter);
            $pendingPresensiQuery->whereHas('user', $kanторFilter);
        }

        $pendingIzin = $pendingIzinQuery->orderBy('created_at', 'desc')->take(5)->get();
        $pendingSuratIzin = $pendingSuratQuery->orderBy('created_at', 'desc')->take(5)->get();
        $pendingLembur = $pendingLemburQuery->orderBy('created_at', 'desc')->take(5)->get();
        $pendingPoin = $pendingPoinQuery->orderBy('created_at', 'desc')->take(5)->get();
        $pendingPresensi = $pendingPresensiQuery->orderBy('created_at', 'desc')->take(5)->get();

        $pengajuanPending = [
            'izin' => $pendingIzin,
            'surat_izin' => $pendingSuratIzin,
            'lembur' => $pendingLembur,
            'poin' => $pendingPoin,
            'presensi' => $pendingPresensi
        ];

        $startDate = Carbon::today()->subDays(6)->format('Y-m-d');
        $endDate = $hariIni;

        $chartQuery = Presensi::whereBetween('tanggal', [$startDate, $endDate])
            ->whereIn('id_status', [StatusPresensi::TEPAT_WAKTU, StatusPresensi::TERLAMBAT]);
        if (!$isGlobalAdmin) {
            $chartQuery->whereHas('user', fn($q) => $q->where('id_kantor', $user->id_kantor));
        }
        $chartRaw = $chartQuery->selectRaw("DATE(tanggal) as tgl, COUNT(*) as total")
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
