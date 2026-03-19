<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use App\Models\Presensi;
use App\Models\PenggunaanPoin;
use App\Services\PoinService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{

    public function index()
    {
        try {
            $user = Auth::user()->load(['divisi', 'jabatan', 'kantor']);

            $today = Carbon::now('Asia/Jakarta')->toDateString();

            $jadwal = JadwalKerja::with(['shift'])
                ->where('id_user', $user->id)
                ->where('tanggal', $today)
                ->first();

            $presensi = Presensi::where('id_user', $user->id)
                ->where('tanggal', $today)
                ->first();

            $totalPoin = (new PoinService())->getActivePoints($user->id);

            $jamMasuk = $jadwal ? $jadwal->shift->jam_mulai : '-';
            $jamPulang = $jadwal ? $jadwal->shift->jam_selesai : '-';
            $isAdjusted = false;
            $adjustmentNote = null;
            $statusJadwal = 'Normal';

            if ($jadwal) {

                $poinOverrides = PenggunaanPoin::where('id_user', $user->id)
                    ->where('tanggal_penggunaan', $today)
                    ->where('id_status', 2)
                    ->get();

                foreach ($poinOverrides as $poin) {

                    if ($poin->id_pengurangan == 4 && $poin->jam_masuk_custom) {
                        $jamMasuk = substr($poin->jam_masuk_custom, 0, 5) . ':00';
                        $isAdjusted = true;
                        $adjustmentNote = "Datang Siang (Via Poin)";
                        $statusJadwal = 'Masuk Siang';
                    }

                    if ($poin->id_pengurangan == 5 && $poin->jam_pulang_custom) {
                        $jamPulang = substr($poin->jam_pulang_custom, 0, 5) . ':00';
                        $isAdjusted = true;
                        $adjustmentNote = $adjustmentNote ? "Masuk Siang & Pulang Cepat" : "Pulang Cepat (Via Poin)";
                        $statusJadwal = $statusJadwal == 'Masuk Siang' ? 'Full Custom' : 'Pulang Cepat';
                    }
                }
            }

            $ketMasuk = 'Belum Absen';
            $ketPulang = null;

            if ($presensi) {
                if ($presensi->waktu_terlambat) {
                    $ketMasuk = 'Terlambat';
                } else if ($presensi->waktu_masuk_awal) {
                    $ketMasuk = (str_contains($statusJadwal, 'Poin')) ? 'Masuk Siang (Poin) - Datang Awal' : 'Datang Awal';
                } else {
                    $ketMasuk = (str_contains($statusJadwal, 'Poin')) ? 'Masuk Siang (Poin)' : 'Tepat Waktu';
                }

                if ($presensi->jam_pulang) {
                    if ($presensi->waktu_pulang_awal) {
                        $ketPulang = 'Pulang Awal';
                    } else if ($presensi->waktu_pulang_akhir) {
                        $ketPulang = (str_contains($statusJadwal, 'Poin')) ? 'Pulang Cepat (Poin) - Lembur' : 'Lembur / Pulang Akhir';
                    } else {
                        $ketPulang = (str_contains($statusJadwal, 'Poin')) ? 'Pulang Cepat (Poin)' : 'Tepat Waktu';
                    }
                }
            }

            $dashboardData = [
                'user' => [
                    'id' => $user->id,
                    'nama_lengkap' => $user->nama_lengkap,
                    'email' => $user->email,
                    'foto' => $user->foto,
                    'jabatan' => $user->jabatan->nama_jabatan ?? 'N/A',
                    'divisi' => $user->divisi->nama_divisi ?? 'N/A',
                    'sisa_cuti' => (int) ($user->sisa_cuti ?? 0),
                    'status_aktif' => $user->status_aktif ?? 'Aktif',
                ],
                'poin' => (int) $totalPoin,
                'statistik_absensi' => [
                    'izin' => Presensi::where('id_user', $user->id)
                        ->whereMonth('tanggal', now()->month)
                        ->whereYear('tanggal', now()->year)
                        ->where('id_status', 3)
                        ->count(),
                    'alpha' => Presensi::where('id_user', $user->id)
                        ->whereMonth('tanggal', now()->month)
                        ->whereYear('tanggal', now()->year)
                        ->where('id_status', 5)
                        ->count(),
                ],
                'jadwal_hari_ini' => $jadwal ? [
                    'hari' => Carbon::parse($today, 'Asia/Jakarta')->translatedFormat('l, d F Y'),
                    'shift' => $jadwal->shift->nama_shift ?? '-',
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'is_adjusted' => $isAdjusted,
                    'note' => $adjustmentNote,
                    'status_jadwal' => $statusJadwal,
                    'kantor_nama' => $user->kantor->nama_kantor ?? '-',
                    'kantor_lat' => (float) ($user->kantor->latitude ?? 0),
                    'kantor_lon' => (float) ($user->kantor->longitude ?? 0),
                    'kantor_radius' => (float) ($user->kantor->radius ?? 200),
                ] : null,
                'presensi_hari_ini' => $presensi ? [
                    'jam_masuk' => $presensi->jam_masuk,
                    'jam_pulang' => $presensi->jam_pulang,
                    'status' => $presensi->id_status,
                    'keterangan' => $ketMasuk,
                    'keterangan_pulang' => $ketPulang,
                ] : null,
            ];

            return ApiResponse::success($dashboardData, 'Dashboard data berhasil dimuat');

        } catch (\Exception $e) {
            return ApiResponse::error('Gagal memuat dashboard: ' . $e->getMessage(), 500);
        }
    }
}
