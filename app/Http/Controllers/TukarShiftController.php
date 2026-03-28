<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JadwalKerja;
use App\Models\RiwayatTukarShift;
use App\Http\Requests\StoreTukarShiftRequest;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TukarShiftController extends Controller
{

    public function index()
    {
        $riwayat = RiwayatTukarShift::with(['user1', 'user2', 'jadwal1.shift', 'jadwal2.shift', 'execAdmin'])
            ->latest()
            ->get();

        return view('tukar-shift.index', compact('riwayat'));
    }

    public function create()
    {
        $pegawai = User::with('kantor')->whereDoesntHave('roles', function ($q) {
            $q->where('nama_role', 'hrd');
        })->get();

        $kantor = \App\Models\Kantor::orderBy('nama_kantor', 'asc')->get();

        return view('tukar-shift.create', compact('pegawai', 'kantor'));
    }

    public function store(StoreTukarShiftRequest $request)
    {
        DB::beginTransaction();

        try {
            $jadwal1 = JadwalKerja::findOrFail($request->id_jadwal_1);
            $jadwal2 = JadwalKerja::findOrFail($request->id_jadwal_2);

            // 1. Validasi kepemilikan jadwal
            if ($jadwal1->id_user != $request->id_user_1 || $jadwal2->id_user != $request->id_user_2) {
                return redirect()->back()->withInput()->with('error', 'Data jadwal tidak sesuai dengan pegawai yang dipilih.');
            }

            // 2. Validasi tanggal tidak boleh di masa lalu
            $hariIni = Carbon::today()->toDateString();
            if ($jadwal1->tanggal < $hariIni || $jadwal2->tanggal < $hariIni) {
                return redirect()->back()->withInput()->with('error', 'Tidak bisa menukar shift untuk tanggal yang sudah lewat.');
            }

            // 3. Validasi presensi belum ada di tanggal tujuan
            $presensi1 = \App\Models\Presensi::where('id_user', $request->id_user_1)
                ->where('tanggal', $jadwal1->tanggal)
                ->exists();
            if ($presensi1) {
                return redirect()->back()->withInput()->with('error', 'Pegawai Pertama sudah memiliki data presensi pada tanggal (' . $jadwal1->tanggal . '). Tukar shift tidak dapat dilakukan.');
            }

            $presensi2 = \App\Models\Presensi::where('id_user', $request->id_user_2)
                ->where('tanggal', $jadwal2->tanggal)
                ->exists();
            if ($presensi2) {
                return redirect()->back()->withInput()->with('error', 'Pegawai Kedua sudah memiliki data presensi pada tanggal (' . $jadwal2->tanggal . '). Tukar shift tidak dapat dilakukan.');
            }

            // 4. Validasi bentrok jadwal kerja
            $conflict1 = JadwalKerja::where('id_user', $request->id_user_1)
                ->where('tanggal', $jadwal2->tanggal)
                ->where('id_jadwal', '!=', $jadwal1->id_jadwal)
                ->exists();
            if ($conflict1) {
                return redirect()->back()->withInput()->with('error', 'Pegawai Pertama sudah memiliki jadwal kerja lain pada tanggal tujuan (' . $jadwal2->tanggal . ').');
            }

            $conflict2 = JadwalKerja::where('id_user', $request->id_user_2)
                ->where('tanggal', $jadwal1->tanggal)
                ->where('id_jadwal', '!=', $jadwal2->id_jadwal)
                ->exists();
            if ($conflict2) {
                return redirect()->back()->withInput()->with('error', 'Pegawai Kedua sudah memiliki jadwal kerja lain pada tanggal tujuan (' . $jadwal1->tanggal . ').');
            }

            // 5. Validasi bentrok dengan Penggunaan Poin (Cuti / dll)
            $poin1 = \App\Models\PenggunaanPoin::where('id_user', $request->id_user_1)
                ->where('tanggal_penggunaan', $jadwal2->tanggal)
                ->where('id_status', 2)
                ->exists();
            if ($poin1) {
                return redirect()->back()->withInput()->with('error', 'Pegawai Pertama memiliki riwayat Cuti/Penggunaan Poin pada tanggal tujuan (' . $jadwal2->tanggal . ').');
            }

            $poin2 = \App\Models\PenggunaanPoin::where('id_user', $request->id_user_2)
                ->where('tanggal_penggunaan', $jadwal1->tanggal)
                ->where('id_status', 2)
                ->exists();
            if ($poin2) {
                return redirect()->back()->withInput()->with('error', 'Pegawai Kedua memiliki riwayat Cuti/Penggunaan Poin pada tanggal tujuan (' . $jadwal1->tanggal . ').');
            }

            $tempUser1 = $jadwal1->id_user;
            $jadwal1->id_user = $jadwal2->id_user;
            $jadwal2->id_user = $tempUser1;

            $jadwal1->save();
            $jadwal2->save();

            RiwayatTukarShift::create([
                'id_user_1' => $request->id_user_1,
                'id_jadwal_1' => $jadwal1->id_jadwal,
                'id_user_2' => $request->id_user_2,
                'id_jadwal_2' => $jadwal2->id_jadwal,
                'keterangan' => $request->keterangan,
                'created_by' => Auth::id() ?? 1,
            ]);

            DB::commit();

            $user1 = User::find($request->id_user_1, ['*']);
            $user2 = User::find($request->id_user_2, ['*']);

            app(NotifikasiService::class)->kirim(
                $request->id_user_1,
                'tukar_shift',
                'Jadwal Shift Ditukar',
                'Shift Anda telah ditukar dengan ' . ($user2->nama_lengkap ?? 'pegawai lain') . '.'
            );

            app(NotifikasiService::class)->kirim(
                $request->id_user_2,
                'tukar_shift',
                'Jadwal Shift Ditukar',
                'Shift Anda telah ditukar dengan ' . ($user1->nama_lengkap ?? 'pegawai lain') . '.'
            );

            return redirect()->route('tukar-shift.index')
                ->with('success', 'Berhasil menukar shift kerja untuk kedua pegawai tersebut.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menukar shift: ' . $e->getMessage());
        }
    }

    public function getJadwalUser(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id'
        ]);

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->addMonth()->endOfMonth()->toDateString();

        $jadwal = JadwalKerja::with('shift')
            ->where('id_user', $request->id_user)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->orderBy('tanggal', 'asc')
            ->get();

        return response()->json($jadwal);
    }
}
