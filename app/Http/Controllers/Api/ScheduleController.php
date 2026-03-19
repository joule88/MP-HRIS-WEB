<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use App\Models\PenggunaanPoin;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ScheduleController extends Controller
{

    public function today(Request $request)
    {
        try {
            $user = Auth::user();

            $today = Carbon::now('Asia/Jakarta')->toDateString();

            $jadwal = JadwalKerja::with(['shift', 'user.kantor'])
                ->where('id_user', $user->id)
                ->where('tanggal', $today)
                ->first();

            if (!$jadwal) {
                return ApiResponse::success(null, 'Tidak ada jadwal hari ini');
            }

            $shift = $jadwal->shift;
            $jamMasuk = $shift->jam_mulai ?? '00:00:00';
            $jamPulang = $shift->jam_selesai ?? '00:00:00';
            $namaShift = $shift->nama_shift ?? 'Unknown Shift';

            $statusJadwal = 'Normal';
            $note = null;

            $poinList = PenggunaanPoin::where('id_user', $user->id)
                ->whereDate('tanggal_penggunaan', $today)
                ->where('id_status', 2)
                ->get();

            foreach ($poinList as $poin) {

                if ($poin->id_pengurangan == 4 && $poin->jam_masuk_custom) {
                    $jamMasuk = substr($poin->jam_masuk_custom, 0, 5) . ':00';
                    $statusJadwal = 'Masuk Siang (Poin)';
                    $note = 'Jadwal disesuaikan poin';
                }

                if ($poin->id_pengurangan == 5 && $poin->jam_pulang_custom) {
                    $jamPulang = substr($poin->jam_pulang_custom, 0, 5) . ':00';

                    if ($statusJadwal != 'Normal') {
                        $statusJadwal = 'Full Custom (Poin)';
                    } else {
                        $statusJadwal = 'Pulang Cepat (Poin)';
                    }
                }
            }

            return ApiResponse::success([
                'hari' => Carbon::parse($today, 'Asia/Jakarta')->translatedFormat('l, d F Y'),
                'shift' => $namaShift,
                'jam_masuk' => $jamMasuk,
                'jam_pulang' => $jamPulang,
                'status_jadwal' => $statusJadwal,
                'note' => $note,
                'kantor' => $jadwal->user->kantor->nama_kantor ?? '-',
                'lokasi_kantor' => [
                    'lat' => $jadwal->user->kantor->latitude ?? 0,
                    'lon' => $jadwal->user->kantor->longitude ?? 0,
                ],
            ], 'Jadwal hari ini ditemukan');

        } catch (\Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function checkScheduleByDate(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date_format:Y-m-d',
        ]);

        try {
            $user = Auth::user();
            $tanggal = $request->tanggal;

            $jadwal = JadwalKerja::with(['shift'])
                ->where('id_user', $user->id)
                ->where('tanggal', $tanggal)
                ->first();

            if (!$jadwal) {
                return ApiResponse::error('Tidak ada jadwal kerja pada tanggal ini', 404);
            }

            $shift = $jadwal->shift;
            $jamMasuk = $shift->jam_mulai ?? '00:00:00';
            $jamPulang = $shift->jam_selesai ?? '00:00:00';

            $statusJadwal = 'Normal';
            $note = null;

            $poinList = PenggunaanPoin::where('id_user', $user->id)
                ->whereDate('tanggal_penggunaan', $tanggal)
                ->where('id_status', 2)
                ->get();

            foreach ($poinList as $poin) {
                if ($poin->id_pengurangan == 4 && $poin->jam_masuk_custom) {
                    $jamMasuk = substr($poin->jam_masuk_custom, 0, 5) . ':00';
                    $statusJadwal = 'Masuk Siang (Poin)';
                }

                if ($poin->id_pengurangan == 5 && $poin->jam_pulang_custom) {
                    $jamPulang = substr($poin->jam_pulang_custom, 0, 5) . ':00';
                    $statusJadwal = ($statusJadwal == 'Masuk Siang (Poin)') ? 'Full Custom' : 'Pulang Cepat (Poin)';
                }
            }

            return ApiResponse::success([
                'tanggal' => Carbon::parse($tanggal, 'Asia/Jakarta')->translatedFormat('l, d F Y'),
                'jam_masuk' => $jamMasuk,
                'jam_pulang' => $jamPulang,

                'jam_mulai' => $jamMasuk,
                'jam_selesai' => $jamPulang,

                'status_jadwal' => $statusJadwal,
                'note' => $note,
            ], 'Jadwal ditemukan');

        } catch (\Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function getMonthlySchedule(Request $request)
    {

        $month = $request->month ?? Carbon::now()->month;
        $year = $request->year ?? Carbon::now()->year;

        try {
            $user = Auth::user();

            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

            $jadwals = JadwalKerja::with(['shift'])
                ->where('id_user', $user->id)
                ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
                ->get()
                ->keyBy('tanggal');

            $poinOverrides = PenggunaanPoin::where('id_user', $user->id)
                ->whereBetween('tanggal_penggunaan', [$startDate->toDateString(), $endDate->toDateString()])
                ->where('id_status', 2)
                ->get()
                ->groupBy('tanggal_penggunaan');

            $presensis = Presensi::where('id_user', $user->id)
                ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
                ->get()
                ->keyBy('tanggal');

            $hariLiburs = \App\Models\HariLibur::whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
                ->where(function($query) use ($user) {
                    $query->whereNull('id_kantor')
                          ->orWhere('id_kantor', $user->id_kantor);
                })
                ->get()
                ->keyBy(function ($item) {
                    return Carbon::parse($item->tanggal)->toDateString();
                });

            $results = [];
            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                $dateStr = $date->toDateString();
                $jadwalItem = $jadwals[$dateStr] ?? null;
                $presensiItem = $presensis[$dateStr] ?? null;
                $hariLiburItem = $hariLiburs[$dateStr] ?? null;

                $dataHari = [
                    'tanggal' => $dateStr,
                    'hari' => $date->translatedFormat('l'),
                    'is_hari_kerja' => false,
                    'is_hari_libur' => $hariLiburItem ? true : false,
                    'keterangan_libur' => $hariLiburItem ? $hariLiburItem->keterangan : null,
                    'shift_nama' => $hariLiburItem ? $hariLiburItem->keterangan : 'Libur / Off',
                    'jam_masuk' => '-',
                    'jam_pulang' => '-',
                    'status_poin' => null,
                    'status_presensi' => $presensiItem ? 'Hadir' : 'Belum Absen',
                    'warna_kalender' => $hariLiburItem ? '#EF4444' : '#E0E0E0'
                ];

                if ($jadwalItem) {
                    $dataHari['is_hari_kerja'] = true;

                    $shift = $jadwalItem->shift;
                    $dataHari['shift_nama'] = $shift->nama_shift ?? 'Unknown Shift';
                    $dataHari['jam_masuk'] = $shift->jam_mulai ?? '00:00:00';
                    $dataHari['jam_pulang'] = $shift->jam_selesai ?? '00:00:00';

                    $dataHari['warna_kalender'] = '#3B82F6';

                    $poinFound = null;

                    foreach ($poinOverrides as $keyDate => $poins) {
                        if (Carbon::parse($keyDate)->toDateString() == $dateStr) {
                            $poinFound = $poins;
                            break;
                        }
                    }

                    if ($poinFound) {
                        foreach ($poinFound as $poin) {
                            if ($poin->id_pengurangan == 4 && $poin->jam_masuk_custom) {
                                $dataHari['jam_masuk'] = substr($poin->jam_masuk_custom, 0, 5) . ':00';
                                $dataHari['status_poin'] = 'Masuk Siang';
                                $dataHari['warna_kalender'] = '#F59E0B';
                            }
                            if ($poin->id_pengurangan == 5 && $poin->jam_pulang_custom) {
                                $dataHari['jam_pulang'] = substr($poin->jam_pulang_custom, 0, 5) . ':00';
                                $dataHari['status_poin'] = $dataHari['status_poin'] ? 'Full Custom' : 'Pulang Cepat';
                                $dataHari['warna_kalender'] = '#F59E0B';
                            }
                        }
                    }

                    if ($presensiItem) {
                        $dataHari['warna_kalender'] = '#10B981';
                    }
                }

                $results[] = $dataHari;
            }

            return ApiResponse::success($results, 'Data kalender berhasil dimuat');

        } catch (\Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
}
