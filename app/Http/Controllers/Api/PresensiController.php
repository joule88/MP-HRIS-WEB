<?php

namespace App\Http\Controllers\Api;

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
            ->where('id_status', 2)
            ->get();

        foreach ($poinOverrides as $poin) {
            if ($poin->id_pengurangan == 4 && $poin->jam_masuk_custom) {
                $jamMasuk = substr($poin->jam_masuk_custom, 0, 5) . ':00';
                $infoJadwal .= ' (Masuk Siang)';
            }
            if ($poin->id_pengurangan == 5 && $poin->jam_pulang_custom) {
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

    public function store(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'foto' => 'required|image|max:2048',
                'status' => 'required|in:masuk,pulang',
                'keterangan_luar_radius' => 'nullable|string|max:500',
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
            $history = Presensi::where('id_user', $userId)
                ->latest('tanggal')
                ->paginate(10);

            $enriched = $history->getCollection()->map(function ($item) use ($userId) {
                $jadwal = \App\Models\JadwalKerja::with('shift')
                    ->where('id_user', $userId)
                    ->where('tanggal', $item->tanggal)
                    ->first();

                $namaShift = $jadwal?->shift?->nama_shift ?? '-';

                $statusMasuk = '-';
                if ($item->jam_masuk) {
                    if ($item->waktu_terlambat) {
                        $statusMasuk = 'Terlambat';
                    } elseif ($item->waktu_masuk_awal) {
                        $statusMasuk = 'Datang Awal';
                    } else {
                        $statusMasuk = 'Tepat Waktu';
                    }
                } else {
                    if ($item->alasan_telat && str_contains($item->alasan_telat, 'Cuti')) {
                        $statusMasuk = 'Cuti';
                    } elseif ($item->id_status == 3) {
                        $statusMasuk = 'Izin';
                    } elseif ($item->id_status == 4) {
                        $statusMasuk = 'Sakit';
                    } elseif ($item->id_status != null && $item->id_status != 1 && $item->id_status != 2) {

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
                        $diff = $masuk->diff($pulang);
                        $totalJam = $diff->h . 'j ' . $diff->i . 'm';
                    } catch (\Exception $e) {
                        $totalJam = '-';
                    }
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
                ];
            });

            $history->setCollection($enriched);

            return ApiResponse::success($history);

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

            if ($presensi->id_validasi != 3) {
                return ApiResponse::error('Hanya presensi yang ditolak yang bisa diajukan ulang.', 422);
            }

            $presensi->update([
                'id_validasi' => 2,
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
