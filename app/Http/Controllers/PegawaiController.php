<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Kantor;
use App\Models\Role;
use App\Http\Requests\StorePegawaiRequest;
use App\Http\Requests\UpdatePegawaiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['divisi', 'jabatan', 'kantor', 'roles'])
            ->whereDoesntHave('roles', function ($q) {
                $q->where('nama_role', 'hrd');
            });

        if ($request->filled('filter_jabatan')) {
            $query->where('id_jabatan', $request->filter_jabatan);
        }

        if ($request->filled('filter_kantor')) {
            $query->where('id_kantor', $request->filter_kantor);
        }

        if ($request->filled('filter_status')) {
            $status = $request->filter_status == 'active' ? 1 : 0;
            $query->where('status_aktif', $status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $pegawai = $query->latest()->paginate(10)->withQueryString();

        $allKantor = Kantor::withCount([
            'users as total_pegawai' => function ($q) {
                $q->whereDoesntHave('roles', function ($sq) {
                    $sq->where('nama_role', 'hrd');
                });
            }
        ])->get();

        $stats = [
            'kantor_list' => $allKantor,
            'total' => User::whereDoesntHave('roles', function ($q) {
                $q->where('nama_role', 'hrd');
            })->count(),
            'active' => User::where('status_aktif', 1)
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('nama_role', 'hrd');
                })->count(),
        ];

        $allJabatan = Jabatan::all();

        return view('pegawai.index', compact('pegawai', 'stats', 'allJabatan', 'allKantor'));
    }

    private function generateIdKaryawan($tglBergabung, $idDivisi, $idJabatan)
    {
        // 1. Ambil Tahun & Bulan (YYYYMM)
        $date = \Carbon\Carbon::parse($tglBergabung);
        $yearMonth = $date->format('Ym');

        // 2. Ambil 2 huruf dari Divisi & Jabatan (XXYY)
        $divisi = Divisi::find($idDivisi);
        $jabatan = Jabatan::find($idJabatan);

        $kodeDivisi = 'XX';
        if ($divisi) {
            $words = explode(' ', $divisi->nama_divisi);
            if (count($words) >= 2) {
                $kodeDivisi = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
            } else {
                $kodeDivisi = strtoupper(substr($divisi->nama_divisi, 0, 2));
            }
        }

        $kodeJabatan = 'YY';
        if ($jabatan) {
            $words = explode(' ', $jabatan->nama_jabatan);
            if (count($words) >= 2) {
                $kodeJabatan = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
            } else {
                $kodeJabatan = strtoupper(substr($jabatan->nama_jabatan, 0, 2));
            }
        }
        $kodeTengah = $kodeDivisi . $kodeJabatan;

        // 3. Generate Nomor Urut (NNN) per tahun bergabun
        $yearStarts = $date->copy()->startOfYear()->toDateString();
        $yearEnds = $date->copy()->endOfYear()->toDateString();

        $lastUser = User::whereBetween('tgl_bergabung', [$yearStarts, $yearEnds])
            ->whereNotNull('nik')
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastUser && preg_match('/-(\d{3})$/', $lastUser->nik, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        }

        $nomorUrut = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Format: MPG-202603-ITST-001
        return "MPG-{$yearMonth}-{$kodeTengah}-{$nomorUrut}";
    }

    public function store(StorePegawaiRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('foto-profil', 'public');
        }

        // Generate ID Karyawan Otomatis
        $tglBergabung = $data['tgl_bergabung'] ?? now()->toDateString();
        $data['nik'] = $this->generateIdKaryawan($tglBergabung, $data['id_divisi'], $data['id_jabatan']);

        $data['password'] = Hash::make('Mpg123!');
        $data['status_aktif'] = 1;
        $data['sisa_cuti'] = 12;

        $user = User::create($data);

        if ($request->filled('id_role')) {
            $user->roles()->attach($request->id_role);
        }

        return redirect()->route('pegawai.index')->with('success', 'Pegawai baru berhasil ditambahkan.');
    }

    public function update(UpdatePegawaiRequest $request, $id)
    {
        $pegawai = User::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('foto')) {
            if ($pegawai->foto) {
                Storage::disk('public')->delete($pegawai->foto);
            }
            $data['foto'] = $request->file('foto')->store('foto-profil', 'public');
        }

        $pegawai->update($data);

        if ($request->filled('id_role')) {
            $pegawai->roles()->sync([$request->id_role]);
        }

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function create()
    {
        $divisi = Divisi::all();
        $jabatan = Jabatan::all();
        $kantor = Kantor::all();
        $roles = Role::all();
        return view('pegawai.create', compact('divisi', 'jabatan', 'kantor', 'roles'));
    }

    public function show($id)
    {
        $pegawai = User::with(['divisi', 'jabatan', 'kantor', 'roles'])->findOrFail($id);

        $sisaPoin = \App\Models\PoinLembur::where('id_user', $id)
            ->where('is_fully_used', false)
            ->whereDate('expired_at', '>=', now()->toDateString())
            ->sum('sisa_poin');

        $historyTambah = \App\Models\PoinLembur::where('id_user', $id)
            ->select('id_poin as id', 'tanggal', 'jumlah_poin as jumlah', 'keterangan', \DB::raw("'penambahan' as tipe"))
            ->orderBy('tanggal', 'desc')
            ->take(10)
            ->get();

        $historyKurang = \App\Models\PenggunaanPoin::where('id_user', $id)
            ->where('id_status', 1)
            ->select('id_penggunaan as id', 'tanggal_penggunaan as tanggal', 'jumlah_poin as jumlah', \DB::raw("'Penggunaan Poin' as keterangan"), \DB::raw("'pengurangan' as tipe"))
            ->orderBy('tanggal_penggunaan', 'desc')
            ->take(10)
            ->get();

        $historyPoin = $historyTambah->concat($historyKurang)->sortByDesc('tanggal')->take(10);

        $riwayatPresensi = \App\Models\Presensi::with(['status'])
            ->where('id_user', $id)
            ->whereDate('tanggal', '>=', now()->subDays(30)->toDateString())
            ->orderBy('tanggal', 'desc')
            ->get();

        $jadwalKerja = \App\Models\JadwalKerja::with('shift')
            ->where('id_user', $id)
            ->whereIn('tanggal', $riwayatPresensi->pluck('tanggal'))
            ->get()
            ->keyBy('tanggal');

        foreach ($riwayatPresensi as $presensi) {
            $presensi->setRelation('jadwal', $jadwalKerja->get($presensi->tanggal));
        }

        $riwayatIzin = \App\Models\PengajuanIzin::with('jenisIzin')
            ->where('id_user', $id)
            ->whereDate('tanggal_mulai', '>=', now()->subDays(30)->toDateString())
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        return view('pegawai.show', compact('pegawai', 'sisaPoin', 'historyPoin', 'riwayatPresensi', 'riwayatIzin'));
    }

    public function edit($id)
    {
        $pegawai = User::findOrFail($id);
        $divisi = Divisi::all();
        $jabatan = Jabatan::all();
        $kantor = Kantor::all();
        $roles = Role::all();
        return view('pegawai.edit', compact('pegawai', 'divisi', 'jabatan', 'kantor', 'roles'));
    }

    public function destroy($id)
    {
        $pegawai = User::findOrFail($id);

        if ($pegawai->status_aktif == 0) {
            $pegawai->delete();
            return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil dihapus (Soft Delete).');
        }

        $pegawai->update(['status_aktif' => 0]);

        return redirect()->route('pegawai.index')->with('success', 'Status pegawai telah diubah menjadi Non-Aktif (Resigned). Histori data tetap tersimpan.');
    }
    public function resetPassword($id)
    {
        $pegawai = User::findOrFail($id);

        $defaultPassword = 'Mpg123!';
        $pegawai->update([
            'password' => Hash::make($defaultPassword),
        ]);

        $pegawai->tokens()->delete();

        return redirect()->route('pegawai.show', $id)
            ->with('success', "Password {$pegawai->nama_lengkap} berhasil direset ke default (Mpg123!).");
    }
}
