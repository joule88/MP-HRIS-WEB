<?php

namespace App\Http\Controllers;

use App\Models\JadwalKerja;
use App\Models\ShiftKerja;
use App\Models\User;
use App\Models\Kantor;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\PenggunaanPoin;
use App\Services\PoinService;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isGlobalAdmin = $user->isGlobalAdmin();

        $kantor = $isGlobalAdmin ? Kantor::all() : Kantor::where('id_kantor', $user->id_kantor)->get();
        $divisi = Divisi::all();
        $shifts = ShiftKerja::all()->map(function ($s) {
            $s->color = $this->getShiftColor($s->nama_shift);
            return $s;
        });

        $pegawaiQuery = User::where('status_aktif', 1);
        if (!$isGlobalAdmin) {
            $pegawaiQuery->where('id_kantor', $user->id_kantor);
        }
        $pegawai = $pegawaiQuery->orderBy('nama_lengkap', 'asc')->get();

        return view('jadwal.index', compact('kantor', 'divisi', 'shifts', 'pegawai'));
    }

    public function getEvents(Request $request)
    {
        $start = Carbon::parse($request->start)->format('Y-m-d');
        $end = Carbon::parse($request->end)->format('Y-m-d');

        $user = Auth::user();
        $isGlobalAdmin = $user->isGlobalAdmin();

        $query = JadwalKerja::with(['user.kantor', 'user.jabatan', 'shift'])
            ->whereDate('tanggal', '>=', $start)
            ->whereDate('tanggal', '<=', $end);

        if (!$isGlobalAdmin) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('id_kantor', $user->id_kantor);
            });
        }

        if ($request->filled('filter_kantor') && $request->filter_kantor != "") {
            // Jika bukan admin, pastikan filter_kantor sesuai dengan kantornya
            if (!$isGlobalAdmin && $request->filter_kantor != $user->id_kantor) {
                $query->whereHas('user', function ($q) use ($user) {
                    $q->where('id_kantor', $user->id_kantor);
                });
            } else {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('id_kantor', $request->filter_kantor);
                });
            }
        }

        if ($request->filled('filter_divisi') && $request->filter_divisi != "") {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('id_divisi', $request->filter_divisi);
            });
        }

        $jadwals = $query->get();

        $poinUsed = PenggunaanPoin::with('jenisPengurangan')
            ->whereBetween('tanggal_penggunaan', [$start, $end])
            ->where('id_status', 2)
            ->get()
            ->groupBy(function ($item) {
                return $item->id_user . '-' . $item->tanggal_penggunaan->format('Y-m-d');
            });

        $events = [];

        foreach ($jadwals as $jadwalItem) {
            $key = $jadwalItem->id_user . '-' . $jadwalItem->tanggal;

            $shiftName = $jadwalItem->shift?->nama_shift ?? 'Shift ?';
            $jamMulai = $jadwalItem->shift?->jam_mulai ?? '00:00:00';
            $jamSelesai = $jadwalItem->shift?->jam_selesai ?? '23:59:00';

            $originalJamMulai = $jamMulai;
            $originalJamSelesai = $jamSelesai;

            $color = $this->getShiftColor($shiftName);

            $title = ($jadwalItem->user?->nama_lengkap ?? 'Unknown') . "\n(" . $shiftName . ")";
            $description = "Jam: " . substr($jamMulai, 0, 5) . " - " . substr($jamSelesai, 0, 5);
            $isPoin = false;
            $poinInfo = [];

            if (isset($poinUsed[$key])) {
                $isPoin = true;
                $listPenggunaan = $poinUsed[$key];

                $color = '#f6c23e';

                foreach ($listPenggunaan as $penggunaan) {
                    $jenis = $penggunaan->jenisPengurangan?->nama_pengurangan ?? 'Unknown';
                    if ($penggunaan->id_pengurangan == 4 && $penggunaan->jam_masuk_custom) {
                        $jamMulai = substr($penggunaan->jam_masuk_custom, 0, 5) . ':00';
                        $description .= "\n\n⚠️ DATANG TELAT (Poin)\nJam Masuk Baru: $jamMulai";
                        $poinInfo[] = "⚠️ Datang Terlambat (Menjadi $jamMulai)";
                    } elseif ($penggunaan->id_pengurangan == 5 && $penggunaan->jam_pulang_custom) {
                        $jamSelesai = substr($penggunaan->jam_pulang_custom, 0, 5) . ':00';
                        $description .= "\n\n⚠️ PULANG CEPAT (Poin)\nJam Pulang Baru: $jamSelesai";
                        $poinInfo[] = "⚠️ Pulang Cepat (Menjadi $jamSelesai)";
                    } else {
                        $description .= "\n[INFO] " . $jenis;
                        $poinInfo[] = $jenis;
                    }
                }
            }

            $events[] = [
                'id' => $jadwalItem->id_jadwal,
                'title' => $title,
                'start' => $jadwalItem->tanggal,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'description' => $description,
                    'is_poin' => $isPoin,
                    'is_holiday' => false,
                    'poin_info' => $poinInfo,
                    'nama_user' => $jadwalItem->user?->nama_lengkap ?? '-',
                    'nama_shift' => $shiftName,
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'original_jam_mulai' => $originalJamMulai,
                    'original_jam_selesai' => $originalJamSelesai,
                    'kantor' => $jadwalItem->user?->kantor?->nama_kantor ?? '-',
                    'jabatan' => $jadwalItem->user?->jabatan?->nama_jabatan ?? '-',
                    'id_shift' => $jadwalItem->id_shift,
                    'id_user' => $jadwalItem->id_user,
                ]
            ];
        }

        $hariLibursQuery = \App\Models\HariLibur::whereBetween('tanggal', [$start, $end]);
        if ($request->filled('filter_kantor') && $request->filter_kantor != "") {
            $hariLibursQuery->where(function ($q) use ($request) {
                $q->whereNull('id_kantor')->orWhere('id_kantor', $request->filter_kantor);
            });
        }
        $hariLiburs = $hariLibursQuery->get();

        foreach ($hariLiburs as $hl) {
            $events[] = [
                'id' => 'libur-' . $hl->id,
                'title' => '🔴 ' . $hl->keterangan,
                'start' => $hl->tanggal,
                'backgroundColor' => '#ef4444',
                'borderColor' => '#ef4444',
                'display' => 'block',
                'extendedProps' => [
                    'is_holiday' => true,
                    'is_poin' => false,
                    'keterangan' => $hl->keterangan,
                    'nama_user' => $hl->keterangan,
                    'nama_shift' => 'Hari Libur',
                    'jam_mulai' => '00:00',
                    'jam_selesai' => '00:00',
                ]
            ];
        }

        return response()->json($events);
    }

    private function getShiftColor($namaShift)
    {
        if (stripos($namaShift, 'Pagi') !== false)
            return '#10b981';
        if (stripos($namaShift, 'Siang') !== false)
            return '#3b82f6';
        if (stripos($namaShift, 'Sore') !== false)
            return '#f59e0b';
        if (stripos($namaShift, 'Malam') !== false)
            return '#6b7280';
        return '#6366f1';
    }

    public function create()
    {
        $user = Auth::user();
        $isGlobalAdmin = $user->isGlobalAdmin();

        $shifts = ShiftKerja::all();
        $kantor = $isGlobalAdmin ? Kantor::all() : Kantor::where('id_kantor', $user->id_kantor)->get();
        $divisi = Divisi::all();

        $pegawaiQuery = User::where('status_aktif', 1)->with(['kantor', 'jabatan']);
        if (!$isGlobalAdmin) {
            $pegawaiQuery->where('id_kantor', $user->id_kantor);
        }
        $pegawai = $pegawaiQuery->orderBy('nama_lengkap', 'asc')->get();

        return view('jadwal.create', compact('shifts', 'pegawai', 'kantor', 'divisi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'id_shift' => 'required|exists:shift_kerja,id_shift',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $user = Auth::user();
        $isGlobalAdmin = $user->isGlobalAdmin();

        // Validasi Kantor (Security Check)
        if (!$isGlobalAdmin) {
            $invalidUsers = User::whereIn('id', $request->user_ids)
                ->where('id_kantor', '!=', $user->id_kantor)
                ->exists();

            if ($invalidUsers) {
                return redirect()->back()->with('error', 'Anda tidak diizinkan membuat jadwal untuk karyawan di luar kantor Anda.');
            }
        }

        $startDate = Carbon::parse($request->tanggal_mulai);
        $endDate = Carbon::parse($request->tanggal_selesai);
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        $shiftId = $request->id_shift;
        $userIds = $request->user_ids;

        $count = 0;
        $refundCount = 0;
        $poinService = new PoinService();

        $usersData = \App\Models\User::whereIn('id', $userIds)->get(['id', 'id_kantor'])->keyBy('id');

        try {
            DB::transaction(function () use ($period, $userIds, $shiftId, &$count, &$refundCount, $poinService, $usersData) {
                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $libursDate = \App\Models\HariLibur::where('tanggal', $dateStr)->get();

                    foreach ($userIds as $userId) {
                        $user = $usersData->get($userId);
                        $isLibur = $libursDate->contains(function ($libur) use ($user) {
                            return is_null($libur->id_kantor) || ($user && $libur->id_kantor == $user->id_kantor);
                        });

                        if ($isLibur) {
                            continue;
                        }
                        $conflictPoin = PenggunaanPoin::where('id_user', $userId)
                            ->where('tanggal_penggunaan', $dateStr)
                            ->where('id_status', 2)
                            ->first();

                        if ($conflictPoin) {
                            $poinService->refundPoin($conflictPoin->id_penggunaan);
                            $refundCount++;
                        }

                        JadwalKerja::updateOrCreate(
                            ['id_user' => $userId, 'tanggal' => $dateStr],
                            ['id_shift' => $shiftId]
                        );
                        $count++;
                    }
                }
            });

            $message = "Berhasil memproses $count jadwal kerja!";
            if ($refundCount > 0) {
                $message .= " ⚠️ PERINGATAN: $refundCount penggunaan poin dibatalkan otomatis & saldo dikembalikan karena konflik jadwal.";
                return redirect()->route('jadwal.index')->with('warning', $message);
            }

            return redirect()->route('jadwal.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_shift' => 'required|exists:shift_kerja,id_shift',
        ]);

        $jadwal = JadwalKerja::findOrFail($id);
        $user = Auth::user();

        // Security Check
        if (!$user->isGlobalAdmin() && $jadwal->user->id_kantor != $user->id_kantor) {
            return response()->json(['success' => false, 'message' => 'Anda tidak diizinkan mengubah jadwal karyawan di luar kantor Anda.'], 403);
        }

        $warningMsg = null;

        try {
            DB::transaction(function () use ($jadwal, $request, &$warningMsg) {
                $conflictPoin = PenggunaanPoin::where('id_user', $jadwal->id_user)
                    ->where('tanggal_penggunaan', $jadwal->tanggal)
                    ->where('id_status', 2)
                    ->first();

                if ($conflictPoin) {
                    $poinService = new PoinService();
                    $poinService->refundPoin($conflictPoin->id_penggunaan);
                    $warningMsg = "Jadwal diperbarui, namun penggunaan poin user pada tanggal ini telah DIBATALKAN otomatis.";
                }

                $jadwal->update(['id_shift' => $request->id_shift]);
            });

            return response()->json([
                'success' => true,
                'message' => $warningMsg ?? 'Jadwal berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $jadwal = JadwalKerja::findOrFail($id);
        $user = Auth::user();

        // Security Check
        if (!$user->isGlobalAdmin() && $jadwal->user->id_kantor != $user->id_kantor) {
            return response()->json(['success' => false, 'message' => 'Anda tidak diizinkan menghapus jadwal karyawan di luar kantor Anda.'], 403);
        }

        $jadwal->delete();

        return response()->json(['success' => true, 'message' => 'Jadwal berhasil dihapus']);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $user = Auth::user();
        $isGlobalAdmin = $user->isGlobalAdmin();

        if (!$isGlobalAdmin) {
            $invalidUsers = User::whereIn('id', $request->user_ids)
                ->where('id_kantor', '!=', $user->id_kantor)
                ->exists();

            if ($invalidUsers) {
                return redirect()->back()->with('error', 'Anda tidak diizinkan menghapus jadwal karyawan di luar kantor Anda.');
            }
        }

        try {
            DB::transaction(function () use ($request) {
                JadwalKerja::whereIn('id_user', $request->user_ids)
                    ->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai])
                    ->delete();
            });

            return redirect()->back()->with('success', 'Jadwal kerja berhasil dihapus pada rentang tanggal tersebut.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus jadwal: ' . $e->getMessage());
        }
    }

    public function checkConflicts(Request $request)
    {
        $userIds = $request->user_ids;
        $startDate = $request->tanggal_mulai;
        $endDate = $request->tanggal_selesai;

        if (empty($userIds) || !$startDate || !$endDate) {
            return response()->json(['has_conflict' => false]);
        }

        $conflicts = PenggunaanPoin::with('user')
            ->whereIn('id_user', $userIds)
            ->whereBetween('tanggal_penggunaan', [$startDate, $endDate])
            ->where('id_status', 2)
            ->get();

        if ($conflicts->count() > 0) {
            $details = $conflicts->map(function ($item) {
                return "- " . $item->user->nama_lengkap . " (" . date('d M Y', strtotime($item->tanggal_penggunaan)) . ")";
            })->join('<br>');

            return response()->json([
                'has_conflict' => true,
                'message' => "Ditemukan <b>{$conflicts->count()} karyawan</b> yang sedang menggunakan Poin (Cuti/Pulang Cepat) pada tanggal ini:<br><br><small>$details</small><br><br>Jika dilanjutkan, <b>Penggunaan Poin akan DIBATALKAN otomatis</b> dan saldo dikembalikan."
            ]);
        }

        return response()->json(['has_conflict' => false]);
    }
}