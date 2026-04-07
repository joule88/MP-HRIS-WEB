<?php

namespace App\Http\Controllers\Api;

use App\Enums\JenisPengurangan;
use App\Enums\StatusPengajuan;
use App\Enums\StatusPresensi;
use App\Enums\StatusValidasi;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Services\PresensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PenggunaanPoin;
use Carbon\Carbon;

class PresensiController extends Controller
{
    protected $presensiService;

    public function __construct(PresensiService $presensiService)
    {
        $this->presensiService = $presensiService;
    }

    public function index()
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $jadwal = \App\Models\JadwalKerja::with(['shift', 'user'])
            ->where('id_user', $user->id)
            ->where('tanggal', $today)
            ->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada jadwal hari ini'
            ], 404);
        }

        $jamMasuk = $jadwal->shift->jam_mulai;
        $jamPulang = $jadwal->shift->jam_selesai;
        $infoJadwal = $jadwal->shift->nama_shift;

        $poinOverrides = PenggunaanPoin::where('id_user', $user->id)
            ->whereDate('tanggal_penggunaan', $today)
            ->where('id_status', StatusPengajuan::DISETUJUI)
            ->get();

        foreach ($poinOverrides as $poin) {
            if ($poin->id_pengurangan == JenisPengurangan::MASUK_SIANG_POIN && $poin->jam_masuk_custom) {
                $jamMasuk = substr($poin->jam_masuk_custom, 0, 5) . ':00';
                $infoJadwal .= ' (Masuk Siang)';
            }
            if ($poin->id_pengurangan == JenisPengurangan::PULANG_CEPAT_POIN && $poin->jam_pulang_custom) {
                $jamPulang = substr($poin->jam_pulang_custom, 0, 5) . ':00';
                $infoJadwal .= ' (Pulang Cepat)';
            }
        }

        $presensi = Presensi::where('id_user', $user->id)
            ->where('tanggal', $today)
            ->first();

        $ketMasuk = 'Belum Absen';
        $ketPulang = null;

        if ($presensi) {
            if ($presensi->waktu_terlambat) {
                $ketMasuk = 'Terlambat';
            } else if ($presensi->waktu_masuk_awal) {
                $ketMasuk = (str_contains($infoJadwal, 'Poin')) ? 'Masuk Siang (Poin) - Datang Awal' : 'Datang Awal';
            } else {
                $ketMasuk = (str_contains($infoJadwal, 'Poin')) ? 'Masuk Siang (Poin)' : 'Tepat Waktu';
            }

            if ($presensi->jam_pulang) {
                if ($presensi->waktu_pulang_awal) {
                    $ketPulang = 'Pulang Awal';
                } else if ($presensi->waktu_pulang_akhir) {
                    $ketPulang = (str_contains($infoJadwal, 'Poin')) ? 'Pulang Cepat (Poin) - Lembur' : 'Lembur / Pulang Akhir';
                } else {
                    $ketPulang = (str_contains($infoJadwal, 'Poin')) ? 'Pulang Cepat (Poin)' : 'Tepat Waktu';
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'jadwal' => [
                    'nama_shift' => $infoJadwal,
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'tanggal' => Carbon::parse($today)->translatedFormat('l, d F Y'),
                ],
                'presensi' => [
                    'jam_masuk' => $presensi ? $presensi->jam_masuk : null,
                    'jam_pulang' => $presensi ? $presensi->jam_pulang : null,
                    'foto_masuk' => $presensi ? $presensi->foto_masuk : null,
                    'status_validasi' => $presensi ? $presensi->id_validasi : null,
                    'keterangan' => $presensi ? $ketMasuk : null,
                    'keterangan_pulang' => $presensi ? $ketPulang : null,
                ]
            ]
        ]);
    }

    public function checkRadius(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            $user = Auth::user();
            $kantor = $user->kantor;

            if (!$kantor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data kantor belum disetting.',
                ], 400);
            }

            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $kantor->latitude,
                $kantor->longitude,
            );

            $isWithinRadius = $distance <= $kantor->radius;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'is_within_radius' => $isWithinRadius,
                    'distance' => round($distance, 2),
                    'radius' => $kantor->radius,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'foto' => 'required|image|max:2048',
                'status' => 'required|in:masuk,pulang',
                'keterangan_luar_radius' => 'nullable|string|max:500',
                'alasan_telat' => 'nullable|string|max:500',
            ]);

            $user = Auth::user();

            if ($request->status == 'masuk') {
                $data = $this->presensiService->absenMasuk($user, $request);
                return ApiResponse::success($data, 'Absen masuk berhasil');
            } else {
                $data = $this->presensiService->absenPulang($user, $request);
                return ApiResponse::success($data, 'Absen pulang berhasil');
            }

        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            if ($code < 100 || $code > 599)
                $code = 500;
            return ApiResponse::error($e->getMessage(), $code);
        }
    }

    public function history(Request $request)
    {
        try {
            $userId = Auth::id();
            $month = $request->get('month', Carbon::now('Asia/Jakarta')->month);
            $year = $request->get('year', Carbon::now('Asia/Jakarta')->year);

            $history = Presensi::where('id_user', $userId)
                ->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year)
                ->latest('tanggal')
                ->get();

            $enriched = $history->map(function ($item) use ($userId) {
                $jadwal = \App\Models\JadwalKerja::with('shift')
                    ->where('id_user', $userId)
                    ->where('tanggal', $item->tanggal)
                    ->first();

                $namaShift = $jadwal?->shift?->nama_shift ?? '-';

                $statusMasuk = '-';
                if ($item->jam_masuk) {
                    if ($item->id_status == StatusPresensi::ALPHA) {
                        $statusMasuk = 'Alpha (Batal)';
                    } elseif ($item->waktu_terlambat) {
                        $statusMasuk = 'Terlambat';
                    } elseif ($item->waktu_masuk_awal) {
                        $statusMasuk = 'Datang Awal';
                    } else {
                        $statusMasuk = 'Tepat Waktu';
                    }
                } else {
                    if ($item->alasan_telat && str_contains($item->alasan_telat, 'Cuti')) {
                        $statusMasuk = 'Cuti';
                    } elseif ($item->id_status == StatusPresensi::IZIN) {
                        $statusMasuk = 'Izin';
                    } elseif ($item->id_status == StatusPresensi::SAKIT) {
                        $statusMasuk = 'Sakit';
                    } elseif ($item->id_status != null && $item->id_status != StatusPresensi::TEPAT_WAKTU && $item->id_status != StatusPresensi::TERLAMBAT) {

                        $statusStatus = \DB::table('status_presensi')->where('id_status', $item->id_status)->value('nama_status');
                        if ($statusStatus) {
                            $statusMasuk = $statusStatus;
                        }
                    }
                }

                $statusPulang = null;
                if ($item->jam_pulang) {
                    if ($item->waktu_pulang_awal) {
                        $statusPulang = 'Pulang Awal';
                    } elseif ($item->waktu_pulang_akhir) {
                        $statusPulang = 'Lembur';
                    } else {
                        $statusPulang = 'Tepat Waktu';
                    }
                }

                $totalJam = '-';
                if ($item->jam_masuk && $item->jam_pulang) {
                    try {
                        $masuk = Carbon::parse($item->tanggal . ' ' . $item->jam_masuk);
                        $pulang = Carbon::parse($item->tanggal . ' ' . $item->jam_pulang);
                        
                        if ($pulang->lessThan($masuk)) {
                            $pulang->addDay();
                        }
                        
                        $diffMinutes = $pulang->diffInMinutes($masuk);
                        $hours = floor($diffMinutes / 60);
                        $minutes = $diffMinutes % 60;
                        $totalJam = $hours . 'j ' . $minutes . 'm';
                    } catch (\Exception $e) {
                        $totalJam = '-';
                    }
                } elseif ($item->jam_masuk && !$item->jam_pulang && $item->id_status == StatusPresensi::ALPHA) {
                    $totalJam = '0j 0m (Batal)';
                }

                return [
                    'id_presensi' => $item->id_presensi,
                    'tanggal' => Carbon::parse($item->tanggal)->translatedFormat('l, d F Y'),
                    'tanggal_raw' => $item->tanggal,
                    'jam_masuk' => $item->jam_masuk,
                    'jam_pulang' => $item->jam_pulang,
                    'shift' => $namaShift,
                    'status_masuk' => $statusMasuk,
                    'status_pulang' => $statusPulang,
                    'total_jam' => $totalJam,
                    'waktu_terlambat' => $item->waktu_terlambat,
                    'keterangan_luar_radius' => $item->keterangan_luar_radius,
                    'verifikasi_wajah' => (bool) $item->verifikasi_wajah,
                    'status_validasi' => $item->id_validasi,
                    'alasan_penolakan' => $item->alasan_penolakan,
                    'foto_masuk_url' => $item->foto_wajah_masuk
                        ? asset('storage/' . $item->foto_wajah_masuk)
                        : null,
                    'foto_pulang_url' => $item->foto_wajah_pulang
                        ? asset('storage/' . $item->foto_wajah_pulang)
                        : null,
                ];
            });

            return ApiResponse::success([
                'data' => $enriched->values(),
                'month' => (int) $month,
                'year' => (int) $year,
            ]);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function resubmit(Request $request, $id)
    {
        try {
            $request->validate([
                'keterangan' => 'required|string|max:500',
            ]);

            $presensi = Presensi::where('id_presensi', $id)
                ->where('id_user', Auth::id())
                ->firstOrFail();

            if ($presensi->id_validasi != StatusValidasi::DITOLAK) {
                return ApiResponse::error('Hanya presensi yang ditolak yang bisa diajukan ulang.', 422);
            }

            $presensi->update([
                'id_validasi' => StatusValidasi::PENDING,
                'alasan_penolakan' => null,
                'keterangan_luar_radius' => $request->keterangan,
            ]);

            return ApiResponse::success(null, 'Presensi berhasil diajukan ulang. Menunggu validasi admin.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('Data presensi tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
