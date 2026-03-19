<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\JadwalKerja;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use App\Models\PenggunaanPoin;

class PresensiService
{
    public function getDynamicSchedule($userId, $date)
    {
        $jadwal = JadwalKerja::with('shift')
            ->where('id_user', $userId)
            ->where('tanggal', $date)
            ->first();

        if (!$jadwal) {
            return null;
        }

        $jamMasuk = $jadwal->shift->jam_mulai;
        $jamPulang = $jadwal->shift->jam_selesai;
        $status = 'Normal';
        $lokasi = $jadwal->kantor;

        $penggunaanPoin = PenggunaanPoin::where('id_user', $userId)
            ->where('tanggal_penggunaan', $date)
            ->where('id_status', 2)
            ->first();

        if ($penggunaanPoin) {
            if ($penggunaanPoin->id_pengurangan == 4 && $penggunaanPoin->jam_masuk_custom) {
                $jamMasuk = $penggunaanPoin->jam_masuk_custom;
                if (strlen($jamMasuk) == 5)
                    $jamMasuk .= ':00';
                $status = 'Masuk Siang (Poin)';
            }

            if ($penggunaanPoin->id_pengurangan == 5 && $penggunaanPoin->jam_pulang_custom) {
                $jamPulang = $penggunaanPoin->jam_pulang_custom;
                if (strlen($jamPulang) == 5)
                    $jamPulang .= ':00';
                $status = 'Pulang Cepat (Poin)';
            }
        }

        return [
            'jam_masuk' => $jamMasuk,
            'jam_pulang' => $jamPulang,
            'shift_nama' => $jadwal->shift->nama_shift,
            'status_jadwal' => $status,
            'lokasi' => $lokasi
        ];
    }

    public function absenMasuk($user, $request)
    {
        $hariIni = Carbon::today('Asia/Jakarta')->toDateString();
        $jamSekarang = Carbon::now('Asia/Jakarta');

        $jadwal = JadwalKerja::with(['shift'])
            ->where('id_user', $user->id)
            ->where('tanggal', $hariIni)
            ->first();

        if (!$jadwal) {
            throw new \Exception('Anda tidak memiliki jadwal kerja hari ini.', 403);
        }

        $cekPresensi = Presensi::where('id_user', $user->id)
            ->where('tanggal', $hariIni)
            ->first();

        if ($cekPresensi) {
            throw new \Exception('Anda sudah melakukan absen masuk hari ini.', 400);
        }

        $kantor = $user->kantor;
        if (!$kantor) {
            throw new \Exception('Kantor belum disetting.', 400);
        }

        $jarak = $this->calculateDistance($request->latitude, $request->longitude, $kantor->latitude, $kantor->longitude);
        $radiusKantor = $kantor->radius;
        $isDalamRadius = ($jarak <= $radiusKantor);

        $dynamicSchedule = $this->getDynamicSchedule($user->id, $hariIni);

        if (!$dynamicSchedule) {
            $jamMasukEfektif = $jadwal->shift->jam_mulai;
            $statusJadwal = 'Normal';
        } else {
            $jamMasukEfektif = $dynamicSchedule['jam_masuk'];
            $statusJadwal = $dynamicSchedule['status_jadwal'];
        }

        $toleransi = 10;
        $batasTerlambat = Carbon::parse($jamMasukEfektif, 'Asia/Jakarta')->addMinutes($toleransi);

        $isTerlambat = $jamSekarang->greaterThan($batasTerlambat);

        $jamJadwalMasuk = Carbon::parse($jamMasukEfektif, 'Asia/Jakarta');
        $waktuTerlambat = null;
        $waktuMasukAwal = null;

        if ($jamSekarang->greaterThan($jamJadwalMasuk)) {
            $waktuTerlambat = $jamSekarang->diff($jamJadwalMasuk)->format('%H:%I:%S');
        } else if ($jamSekarang->lessThan($jamJadwalMasuk)) {
            $waktuMasukAwal = $jamSekarang->diff($jamJadwalMasuk)->format('%H:%I:%S');
        }

        $idValidasi = 2;
        $statusKehadiran = 'Tepat Waktu';
        $alasanTelat = $request->alasan_telat ?? null;

        if ($isTerlambat) {
            $idValidasi = 1;
            $statusKehadiran = 'Terlambat';
        } else if ($waktuMasukAwal != null) {
            $idValidasi = 2;
            $statusKehadiran = 'Datang Awal';
            if (str_contains($statusJadwal, 'Poin')) {
                $statusKehadiran = 'Masuk Siang (Poin) - Datang Awal';
            }
        } else {
            $idValidasi = 2;

            if (str_contains($statusJadwal, 'Poin')) {
                $statusKehadiran = 'Masuk Siang (Poin)';
            }
        }

        if (!$isDalamRadius) {
            $idValidasi = 1;
        }

        $idStatus = ($statusKehadiran === 'Terlambat') ? 2 : 1;

        $faceVerified = 0;
        if ($user->dataWajah && $user->dataWajah->is_verified == 1) {
            $faceVerified = 1;
        }

        $fotoPath = $this->uploadFile($request->file('foto'), 'masuk', $user->id);

        $presensi = Presensi::create([
            'id_user' => $user->id,
            'tanggal' => $hariIni,
            'jam_masuk' => $jamSekarang->format('H:i:s'),
            'lat_masuk' => $request->latitude,
            'lon_masuk' => $request->longitude,
            'foto_wajah_masuk' => $fotoPath,
            'id_status' => $idStatus,
            'alasan_telat' => $alasanTelat,
            'id_validasi' => $idValidasi,
            'verifikasi_wajah' => $faceVerified,
            'keterangan_luar_radius' => $request->keterangan_luar_radius ?? null,
            'waktu_terlambat' => $waktuTerlambat,
            'waktu_masuk_awal' => $waktuMasukAwal
        ]);

        return [
            'jam_masuk' => $jamSekarang->format('H:i'),
            'status_kehadiran' => $statusKehadiran,
            'dalam_radius' => $isDalamRadius,
            'jarak' => round($jarak) . ' meter',
            'face_verified' => (bool) $faceVerified,
            'jam_efektif' => $jamMasukEfektif
        ];
    }

    public function absenPulang($user, $request)
    {
        $jamSekarang = Carbon::now('Asia/Jakarta');
        $hariIni = Carbon::today('Asia/Jakarta')->toDateString();

        $presensi = Presensi::where('id_user', $user->id)
            ->whereNull('jam_pulang')
            ->latest()
            ->first();

        if (!$presensi) {
            throw new \Exception('Belum ada data absen masuk hari ini atau sudah pulang.', 404);
        }

        if ($presensi->jam_pulang) {
            throw new \Exception('Anda sudah absen pulang hari ini.', 400);
        }

        $dynamicSchedule = $this->getDynamicSchedule($user->id, $hariIni);

        $jamPulangEfektif = $dynamicSchedule['jam_pulang'];
        $statusJadwal = $dynamicSchedule['status_jadwal'];

        $jamPulangTarget = Carbon::parse($jamPulangEfektif, 'Asia/Jakarta');
        $isPulangAwal = $jamSekarang->lessThan($jamPulangTarget);

        $waktuPulangAwal = null;
        $waktuPulangAkhir = null;

        if ($jamSekarang->lessThan($jamPulangTarget)) {
            $waktuPulangAwal = $jamSekarang->diff($jamPulangTarget)->format('%H:%I:%S');
        } else if ($jamSekarang->greaterThan($jamPulangTarget)) {
            $waktuPulangAkhir = $jamSekarang->diff($jamPulangTarget)->format('%H:%I:%S');
        }

        $statusPulang = 'Tepat Waktu';
        $keterangan = $request->keterangan_pulang ?? null;

        if ($isPulangAwal) {
            $statusPulang = 'Pulang Awal';
        } else if ($waktuPulangAkhir != null) {
            $statusPulang = 'Lembur / Pulang Akhir';
        } else {
            if (str_contains($statusJadwal, 'Poin')) {
                $statusPulang = 'Pulang Cepat (Poin)';
            }
        }

        $fotoPath = $this->uploadFile($request->file('foto'), 'pulang', $user->id);

        $presensi->update([
            'jam_pulang' => $jamSekarang->format('H:i:s'),
            'lat_pulang' => $request->latitude,
            'lon_pulang' => $request->longitude,
            'foto_wajah_pulang' => $fotoPath,
            'keterangan_pulang' => $keterangan,
            'waktu_pulang_awal' => $waktuPulangAwal,
            'waktu_pulang_akhir' => $waktuPulangAkhir
        ]);

        return [
            'jam_pulang' => $jamSekarang->format('H:i'),
            'status_pulang' => $statusPulang,
            'keterangan' => $keterangan
        ];
    }

    private function uploadFile(UploadedFile $file, $type, $userId)
    {
        $fileName = "{$type}_{$userId}_" . time() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('uploads/absensi', $fileName, 'public');
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
