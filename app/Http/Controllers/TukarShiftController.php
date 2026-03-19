<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JadwalKerja;
use App\Models\RiwayatTukarShift;
use App\Http\Requests\StoreTukarShiftRequest;
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
            $q->where('nama_role', 'admin')->orWhere('nama_role', 'Admin');
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
