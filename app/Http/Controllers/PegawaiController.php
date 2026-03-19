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
                $q->where('nama_role', 'admin');
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
                    $sq->where('nama_role', 'admin');
                });
            }
        ])->get();

        $stats = [
            'kantor_list' => $allKantor,
            'total' => User::whereDoesntHave('roles', function ($q) {
                $q->where('nama_role', 'admin');
            })->count(),
            'active' => User::where('status_aktif', 1)
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('nama_role', 'admin');
                })->count(),
        ];

        $allJabatan = Jabatan::all();

        return view('pegawai.index', compact('pegawai', 'stats', 'allJabatan', 'allKantor'));
    }

    public function store(StorePegawaiRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('foto-profil', 'public');
        }

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

        $riwayatPresensi = \App\Models\Presensi::with(['status', 'jadwal.shift'])
            ->where('id_user', $id)
            ->whereDate('tanggal', '>=', now()->subDays(30)->toDateString())
            ->orderBy('tanggal', 'desc')
            ->get();

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
}
