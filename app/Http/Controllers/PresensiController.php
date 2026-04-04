<?php

namespace App\Http\Controllers;

use App\Enums\StatusPengajuan;
use App\Enums\StatusPresensi;
use App\Enums\StatusValidasi;
use App\Models\Presensi;
use App\Models\JadwalKerja;
use App\Models\Kantor;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use App\Http\Requests\StorePresensiRequest;
use App\Http\Requests\StoreManualPresensiRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGlobalAdmin = $user->isGlobalAdmin();

        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $divisiId = $request->get('divisi_id');

        $pendingQuery = Presensi::where('id_validasi', StatusValidasi::PENDING);
        if (!$isGlobalAdmin) {
            $pendingQuery->whereHas('user', function ($q) use ($user) {
                $q->where('id_kantor', $user->id_kantor);
            });
        }

        $pendingDates = $pendingQuery->selectRaw('DATE(tanggal) as tgl, COUNT(*) as jumlah')
            ->groupBy('tgl')
            ->orderBy('tgl', 'desc')
            ->get();

        $query = Presensi::with(['user.jabatan', 'user.kantor'])
            ->whereDate('tanggal', $tanggal);

        if (!$isGlobalAdmin) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('id_kantor', $user->id_kantor);
            });
        }

        if ($divisiId) {
            $query->whereHas('user', function ($q) use ($divisiId) {
                $q->where('id_divisi', $divisiId);
            });
        }

        $filterStatus = $request->get('filter_status');
        if ($filterStatus == 'tepat_waktu') {
            $query->where('id_status', StatusPresensi::TEPAT_WAKTU);
        } elseif ($filterStatus == 'terlambat') {
            $query->where('id_status', StatusPresensi::TERLAMBAT);
        } elseif ($filterStatus == 'pending') {
            $query->where('id_validasi', StatusValidasi::PENDING);
        }

        $presensi = $query->orderBy('jam_masuk', 'desc')->paginate(15);

        $divisi = \App\Models\Divisi::all();

        $userIds = $presensi->pluck('id_user')->unique();
        $jadwalCollection = JadwalKerja::with('shift')
            ->whereIn('id_user', $userIds)
            ->where('tanggal', $tanggal)
            ->get()
            ->keyBy('id_user');

        $poinCollection = \App\Models\PenggunaanPoin::with('jenisPengurangan')
            ->whereIn('id_user', $userIds)
            ->where('tanggal_penggunaan', $tanggal)
            ->where('id_status', StatusPengajuan::DISETUJUI)
            ->get()
            ->keyBy('id_user');

        $presensi->map(function ($item) use ($jadwalCollection, $poinCollection) {
            $jadwal = $jadwalCollection->get($item->id_user);
            $poinUsed = $poinCollection->get($item->id_user);

            $jamMasuk = $jadwal && $jadwal->shift ? $jadwal->shift->jam_mulai : '-';
            $jamPulang = $jadwal && $jadwal->shift ? $jadwal->shift->jam_selesai : '-';

            $item->jam_jadwal_masuk_original = $jamMasuk;
            $item->jam_jadwal_pulang_original = $jamPulang;
            $item->is_adjusted = false;
            $item->adjustment_note = null;

            if ($poinUsed) {
                $menit = $poinUsed->jumlah_poin * 30;
                $jenis = $poinUsed->jenisPengurangan->nama_pengurangan ?? '';

                if ($jenis == 'Pulang Cepat' && $jamPulang != '-') {
                    $jamPulang = Carbon::parse($jamPulang)->subMinutes($menit)->format('H:i:s');
                    $item->is_adjusted = true;
                    $item->adjustment_note = "Pulang Cepat -{$menit}m";
                } elseif ($jenis == 'Datang Terlambat' && $jamMasuk != '-') {
                    $jamMasuk = Carbon::parse($jamMasuk)->addMinutes($menit)->format('H:i:s');
                    $item->is_adjusted = true;
                    $item->adjustment_note = "Datang Telat +{$menit}m";
                }
            }

            $item->jam_jadwal_masuk = $jamMasuk;
            $item->jam_jadwal_pulang = $jamPulang;
            $item->nama_shift = $jadwal && $jadwal->shift ? $jadwal->shift->nama_shift : 'Non-Shift';

            $item->status_keterlambatan = 'Tepat Waktu';
            $item->badge_color = 'success';

            if ($item->id_status == StatusPresensi::TERLAMBAT) {
                $item->status_keterlambatan = $item->alasan_telat ?? 'Terlambat';
                $item->badge_color = 'rose';
            } elseif ($item->id_status == StatusPresensi::TEPAT_WAKTU) {
                $item->status_keterlambatan = 'Tepat Waktu';
                $item->badge_color = 'emerald';
            }

            $item->validasi_label = $item->id_validasi == StatusValidasi::VALID ? 'Disetujui' : ($item->id_validasi == StatusValidasi::DITOLAK ? 'Ditolak' : 'Pending');
            $item->validasi_color = $item->id_validasi == StatusValidasi::VALID ? 'emerald' : ($item->id_validasi == StatusValidasi::DITOLAK ? 'rose' : 'amber');

            $item->jarak_masuk = null;
            if ($item->user && $item->user->kantor && $item->lat_masuk && $item->lon_masuk) {
                $item->jarak_masuk = $this->calculateDistance(
                    $item->lat_masuk,
                    $item->lon_masuk,
                    $item->user->kantor->latitude,
                    $item->user->kantor->longitude
                );
                $item->jarak_masuk = round($item->jarak_masuk, 2);
            }

            $item->dalam_radius = false;
            if ($item->jarak_masuk !== null && $item->user && $item->user->kantor) {
                $item->dalam_radius = $item->jarak_masuk <= $item->user->kantor->radius;
            }

            return $item;
        });

        return view('presensi.index', compact('presensi', 'tanggal', 'divisi', 'pendingDates'));
    }

    public function approve($id)
    {
        $presensi = Presensi::with('user')->findOrFail($id);
        $user = Auth::user();

        if (!$user->isGlobalAdmin() && $presensi->user->id_kantor != $user->id_kantor) {
            return redirect()->back()->with('error', 'Anda tidak diizinkan menyetujui presensi dari kantor lain.');
        }

        $presensi->update(['id_validasi' => StatusValidasi::VALID]);

        app(NotifikasiService::class)->kirim(
            $presensi->id_user,
            'presensi_disetujui',
            'Presensi Disetujui ✅',
            'Presensi Anda pada tanggal ' . $presensi->tanggal . ' telah disetujui.',
            ['id_presensi' => $presensi->id_presensi]
        );

        return redirect()->back()->with('success', 'Presensi berhasil disetujui.');
    }

    public function reject($id)
    {
        $presensi = Presensi::with('user')->findOrFail($id);
        $user = Auth::user();

        if (!$user->isGlobalAdmin() && $presensi->user->id_kantor != $user->id_kantor) {
            return redirect()->back()->with('error', 'Anda tidak diizinkan menolak presensi dari kantor lain.');
        }

        $presensi->update(['id_validasi' => StatusValidasi::DITOLAK]);

        app(NotifikasiService::class)->kirim(
            $presensi->id_user,
            'presensi_ditolak',
            'Presensi Ditolak ❌',
            'Presensi Anda pada tanggal ' . $presensi->tanggal . ' ditolak. Silakan hubungi HRD.',
            ['id_presensi' => $presensi->id_presensi]
        );

        return redirect()->back()->with('success', 'Presensi berhasil ditolak.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'presensi_ids' => 'required|array',
            'action' => 'required|in:approve,reject'
        ]);

        $user = Auth::user();
        if (!$user->isGlobalAdmin()) {
            $invalidCount = Presensi::whereIn('id_presensi', $request->presensi_ids)
                ->whereHas('user', function ($q) use ($user) {
                    $q->where('id_kantor', '!=', $user->id_kantor);
                })
                ->count();

            if ($invalidCount > 0) {
                return redirect()->back()->with('error', 'Terdapat data dari kantor lain dalam pilihan Anda.');
            }
        }

        $statusValidasi = $request->action == 'approve' ? StatusValidasi::VALID : StatusValidasi::DITOLAK;

        Presensi::whereIn('id_presensi', $request->presensi_ids)
            ->where('id_validasi', StatusValidasi::PENDING)
            ->update(['id_validasi' => $statusValidasi]);

        $statusText = $request->action == 'approve' ? 'disetujui' : 'ditolak';
        return redirect()->back()->with('success', count($request->presensi_ids) . " pengajuan presensi berhasil {$statusText}.");
    }

    public function create()
    {
        $user = Auth::user();
        $isGlobalAdmin = $user->isGlobalAdmin();

        $pegawaiQuery = \App\Models\User::with('divisi')
            ->orderBy('nama_lengkap', 'asc');

        if (!$isGlobalAdmin) {
            $pegawaiQuery->where('id_kantor', $user->id_kantor);
        }

        $pegawai = $pegawaiQuery->get();

        $statuses = \Illuminate\Support\Facades\DB::table('status_presensi')->get();

        return view('presensi.create', compact('pegawai', 'statuses'));
    }

    public function storeManual(StoreManualPresensiRequest $request)
    {
        $existing = Presensi::where('id_user', $request->id_user)
            ->where('tanggal', $request->tanggal)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Pegawai bersangkutan sudah memiliki record presensi pada tanggal tersebut. Gunakan fitur Edit (Koreksi) jika ingin mengubah data.');
        }

        $jamMasuk = $request->jam_masuk ? $request->jam_masuk . ':00' : null;
        $jamPulang = $request->jam_pulang ? $request->jam_pulang . ':00' : null;

        Presensi::create([
            'id_user' => $request->id_user,
            'tanggal' => $request->tanggal,
            'id_status' => $request->id_status,
            'jam_masuk' => in_array($request->id_status, [StatusPresensi::TEPAT_WAKTU, StatusPresensi::TERLAMBAT]) ? $jamMasuk : null,
            'jam_pulang' => in_array($request->id_status, [StatusPresensi::TEPAT_WAKTU, StatusPresensi::TERLAMBAT]) ? $jamPulang : null,
            'alasan_telat' => $request->alasan_telat ?? 'Input Manual oleh Admin',
            'id_validasi' => StatusValidasi::VALID,
            'verifikasi_wajah' => 1,
            'lat_masuk' => null,
            'lon_masuk' => null,
            'lat_pulang' => null,
            'lon_pulang' => null,
            'keterangan_luar_radius' => 'Input Manual oleh Admin',
            'foto_wajah_masuk' => null,
            'foto_wajah_pulang' => null,
        ]);

        return redirect()->route('presensi.index', ['tanggal' => $request->tanggal])
            ->with('success', 'Presensi manual berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $presensi = Presensi::with('user.divisi')->findOrFail($id);
        $user = Auth::user();

        if (!$user->isGlobalAdmin() && $presensi->user->id_kantor != $user->id_kantor) {
            abort(403, 'Anda tidak diizinkan mengedit presensi dari kantor lain.');
        }

        $statuses = \Illuminate\Support\Facades\DB::table('status_presensi')->get();

        return view('presensi.edit', compact('presensi', 'statuses'));
    }

    public function updateManual(Request $request, $id)
    {
        $presensi = Presensi::with('user')->findOrFail($id);
        $user = Auth::user();

        if (!$user->isGlobalAdmin() && $presensi->user->id_kantor != $user->id_kantor) {
            return redirect()->back()->with('error', 'Anda tidak diizinkan mengoreksi presensi dari kantor lain.');
        }

        $request->validate([
            'id_status' => 'required|exists:status_presensi,id_status',
            'jam_masuk' => [
                \Illuminate\Validation\Rule::requiredIf(fn () => in_array($request->id_status, [StatusPresensi::TEPAT_WAKTU, StatusPresensi::TERLAMBAT])),
                'nullable',
                'date_format:H:i'
            ],
            'jam_pulang' => 'nullable|date_format:H:i',
            'alasan_telat' => 'required|string|max:255'
        ], [
            'alasan_telat.required' => 'Catatan koreksi wajib diisi sebagai dokumentasi log.',
            'jam_masuk.required' => 'Jam masuk wajib diisi untuk kehadiran Hadir/Terlambat.'
        ]);

        $presensi = Presensi::findOrFail($id);

        $jamMasuk = $request->jam_masuk ? $request->jam_masuk . ':00' : null;
        $jamPulang = $request->jam_pulang ? $request->jam_pulang . ':00' : null;

        $isAdjusted = false;
        $adjustmentNotes = [];

        if ($presensi->jam_masuk != $jamMasuk && in_array($request->id_status, [StatusPresensi::TEPAT_WAKTU, StatusPresensi::TERLAMBAT])) {
            $isAdjusted = true;
            $adjustmentNotes[] = "Datang dari " . substr($presensi->jam_masuk ?? '-', 0, 5) . " ke " . substr($jamMasuk, 0, 5);
        }

        if ($presensi->jam_pulang != $jamPulang && in_array($request->id_status, [StatusPresensi::TEPAT_WAKTU, StatusPresensi::TERLAMBAT])) {
            $isAdjusted = true;
            $adjustmentNotes[] = "Pulang dari " . substr($presensi->jam_pulang ?? '-', 0, 5) . " ke " . substr($jamPulang, 0, 5);
        }

        $presensi->update([
            'id_status' => $request->id_status,
            'jam_masuk' => in_array($request->id_status, [StatusPresensi::TEPAT_WAKTU, StatusPresensi::TERLAMBAT]) ? $jamMasuk : null,
            'jam_pulang' => in_array($request->id_status, [StatusPresensi::TEPAT_WAKTU, StatusPresensi::TERLAMBAT]) ? $jamPulang : null,
            'alasan_telat' => $request->alasan_telat . ($isAdjusted ? " | [Koreksi Admin: " . implode(', ', $adjustmentNotes) . "]" : ""),
            'id_validasi' => StatusValidasi::VALID,
        ]);

        return redirect()->route('presensi.index', ['tanggal' => $presensi->tanggal])
            ->with('success', 'Data presensi berhasil dikoreksi.');
    }

    public function store(StorePresensiRequest $request)
    {
        $user = Auth::user();
        $hariIni = Carbon::today()->toDateString();
        $jamSekarang = Carbon::now();
        $status = $request->status;

        if ($status === 'pulang') {
            return $this->handlePulang($request, $user, $hariIni, $jamSekarang);
        }

        $jadwal = JadwalKerja::with('shift')
            ->where('id_user', $user->id)
            ->where('tanggal', $hariIni)
            ->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki jadwal hari ini.'
            ], 403);
        }

        $cekPresensi = Presensi::where('id_user', $user->id)
            ->where('tanggal', $hariIni)
            ->first();

        if ($cekPresensi) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen masuk hari ini.'
            ], 400);
        }

        $kantor = $user->kantor;
        if (!$kantor) {
            return response()->json([
                'success' => false,
                'message' => 'Data kantor belum diatur untuk akun Anda.'
            ], 400);
        }

        $jarak = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $kantor->latitude,
            $kantor->longitude
        );

        $radiusKantor = $kantor->radius;
        $idValidasi = ($jarak <= $radiusKantor) ? StatusValidasi::VALID : StatusValidasi::PENDING;

        $presensiService = new \App\Services\PresensiService();
        $dynamicSchedule = $presensiService->getDynamicSchedule($user->id, $hariIni);

        $jamMasukEfektif = $dynamicSchedule
            ? $dynamicSchedule['jam_masuk']
            : $jadwal->shift->jam_mulai;
        $statusJadwal = $dynamicSchedule['status_jadwal'] ?? 'Normal';

        $jamMasukParsed = Carbon::parse($jamMasukEfektif, 'Asia/Jakarta');
        $toleransi = 15;
        $batasToleransi = $jamMasukParsed->copy()->addMinutes($toleransi);

        $idStatus = StatusPresensi::TEPAT_WAKTU;
        $alasanTelat = null;

        if ($jamSekarang->greaterThan($batasToleransi)) {
            $idStatus = StatusPresensi::TERLAMBAT;
            $selisihMenit = $jamSekarang->diffInMinutes($jamMasukParsed);
            $alasanTelat = "Terlambat {$selisihMenit} menit";
        }

        $fotoFile = $request->file('foto');
        $imageName = 'masuk_' . $user->id . '_' . time() . '.' . $fotoFile->getClientOriginalExtension();

        $verifikasiWajah = 0;
        $faceMessage = null;

        try {
            $faceService = new \App\Services\FaceRecognitionService();
            $result = $faceService->verifyFace($user->id, $fotoFile);

            if (isset($result['verified']) && $result['verified'] === true) {
                $verifikasiWajah = 1;
            }

            $faceMessage = $result['message'] ?? null;
        } catch (\Exception $e) {
        }

        $fotoFile->storeAs('uploads/absensi', $imageName, 'public');

        $presensi = Presensi::create([
            'id_user' => $user->id,
            'tanggal' => $hariIni,
            'jam_masuk' => $jamSekarang->format('H:i:s'),
            'lat_masuk' => $request->latitude,
            'lon_masuk' => $request->longitude,
            'foto_wajah_masuk' => 'uploads/absensi/' . $imageName,
            'id_status' => $idStatus,
            'alasan_telat' => $alasanTelat,
            'id_validasi' => $idValidasi,
            'keterangan_luar_radius' => $request->keterangan_luar_radius,
            'verifikasi_wajah' => $verifikasiWajah,
        ]);

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Absen masuk berhasil!',
            'data' => [
                'jam_masuk' => $jamSekarang->format('H:i'),
                'status' => ($idStatus == StatusPresensi::TEPAT_WAKTU) ? 'Tepat Waktu' : 'Terlambat',
                'jarak' => round($jarak, 2) . ' meter',
                'validasi_lokasi' => ($idValidasi == StatusValidasi::VALID) ? 'Dalam Radius' : 'Di Luar Radius (Pending)',
                'verifikasi_wajah' => $verifikasiWajah == 1 ? 'Terverifikasi' : 'Pending',
                'face_message' => $faceMessage,
            ]
        ], 200);
    }

    private function handlePulang($request, $user, $hariIni, $jamSekarang)
    {
        $presensi = Presensi::where('id_user', $user->id)
            ->whereNull('jam_pulang')
            ->latest()
            ->first();

        if (!$presensi) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum absen masuk atau sudah absen pulang.'
            ], 404);
        }

        $fotoFile = $request->file('foto');
        $imageName = 'pulang_' . $user->id . '_' . time() . '.' . $fotoFile->getClientOriginalExtension();

        $verifikasiPulang = 0;
        try {
            $faceService = new \App\Services\FaceRecognitionService();
            $result = $faceService->verifyFace($user->id, $fotoFile);
            if (isset($result['verified']) && $result['verified'] === true) {
                $verifikasiPulang = 1;
            }
        } catch (\Exception $e) {
        }

        $fotoFile->storeAs('uploads/absensi', $imageName, 'public');

        $presensiService = new \App\Services\PresensiService();
        $dynamicSchedule = $presensiService->getDynamicSchedule($user->id, $hariIni);

        $jamPulangEfektif = $dynamicSchedule
            ? $dynamicSchedule['jam_pulang']
            : null;

        $waktuPulangAwal = null;
        $waktuPulangAkhir = null;

        if ($jamPulangEfektif) {
            $jamPulangTarget = Carbon::parse($jamPulangEfektif, 'Asia/Jakarta');

            if ($jamSekarang->lessThan($jamPulangTarget)) {
                $waktuPulangAwal = $jamSekarang->diff($jamPulangTarget)->format('%H:%I:%S');
            } else if ($jamSekarang->greaterThan($jamPulangTarget)) {
                $waktuPulangAkhir = $jamSekarang->diff($jamPulangTarget)->format('%H:%I:%S');
            }
        }

        $presensi->update([
            'jam_pulang' => $jamSekarang->format('H:i:s'),
            'lat_pulang' => $request->latitude,
            'lon_pulang' => $request->longitude,
            'foto_wajah_pulang' => 'uploads/absensi/' . $imageName,
            'waktu_pulang_awal' => $waktuPulangAwal,
            'waktu_pulang_akhir' => $waktuPulangAkhir,
        ]);

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Absen pulang berhasil. Hati-hati di jalan!',
            'data' => [
                'jam_pulang' => $jamSekarang->format('H:i'),
                'verifikasi_wajah' => $verifikasiPulang == 1 ? 'Terverifikasi' : 'Pending',
            ]
        ], 200);
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
}
