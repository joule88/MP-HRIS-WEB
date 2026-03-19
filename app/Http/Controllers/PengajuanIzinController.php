<?php

namespace App\Http\Controllers;

use App\Models\PengajuanIzin;
use App\Models\Presensi;
use App\Models\JenisIzin;
use App\Models\User;
use App\Models\SuratIzin;
use App\Models\TandaTangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengajuanIzinController extends Controller
{

    public function index(Request $request)
    {
        $statusId = $request->get('status');

        $query = PengajuanIzin::with(['user', 'jenisIzin', 'statusPengajuan', 'suratIzin']);

        if ($statusId) {
            $query->where('id_status', $statusId);
        }

        if ($request->has('search') && $request->search != '') {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%');
            });
        }

        $izin = $query->orderBy('created_at', 'desc')->paginate(15);

        $statuses = \App\Models\StatusPengajuan::all();
        $jenisIzin = \App\Models\JenisIzin::all();
        $users = \App\Models\User::with(['kantor', 'divisi', 'jabatan'])->select('id', 'nama_lengkap', 'nik', 'id_kantor', 'id_divisi', 'id_jabatan')->orderBy('nama_lengkap', 'asc')->get();
        $kantor = \App\Models\Kantor::all();
        $divisi = \App\Models\Divisi::all();

        return view('izin.index', compact('izin', 'statusId', 'statuses', 'jenisIzin', 'users', 'kantor', 'divisi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id',
            'id_jenis_izin' => 'required|exists:jenis_izin,id_jenis_izin',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string',
            'bukti_file' => 'required|image|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);

        $jenisIzin = JenisIzin::find($request->id_jenis_izin);
        if ($jenisIzin && $jenisIzin->nama_izin == 'Cuti') {
            $diffParams = Carbon::parse($request->tanggal_mulai, 'Asia/Jakarta')->diffInDays(Carbon::now('Asia/Jakarta'));

            if (Carbon::parse($request->tanggal_mulai, 'Asia/Jakarta')->diffInDays(Carbon::now('Asia/Jakarta')) < 7) {
                return back()->with('error', 'Pengajuan Cuti minimal H-7!');
            }
        }

        try {
            DB::beginTransaction();

            $path = null;
            if ($request->hasFile('bukti_file')) {
                $path = $request->file('bukti_file')->store('uploads/izin', 'public');
            }

            $izin = PengajuanIzin::create([
                'id_user' => $request->id_user,
                'id_jenis_izin' => $request->id_jenis_izin,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'alasan' => $request->alasan,
                'bukti_file' => $path,
                'id_status' => 1
            ]);

            if ($izin->id_jenis_izin == 2) {
                $user = User::with(['jabatan', 'divisi'])->find($request->id_user);
                $ttdAktif = TandaTangan::where('id_user', $user->id)->active()->first();
                $tglMulai = Carbon::parse($request->tanggal_mulai)->translatedFormat('d F Y');
                $tglSelesai = Carbon::parse($request->tanggal_selesai)->translatedFormat('d F Y');

                $isiSurat = "Dengan hormat,\n\n"
                    . "Saya yang bertanda tangan di bawah ini:\n"
                    . "Nama: {$user->nama_lengkap}\n"
                    . "NIK: {$user->nik}\n"
                    . "Jabatan: " . ($user->jabatan->nama_jabatan ?? '-') . "\n"
                    . "Divisi: " . ($user->divisi->nama_divisi ?? '-') . "\n\n"
                    . "Dengan ini mengajukan Cuti mulai tanggal {$tglMulai} sampai dengan {$tglSelesai}.\n\n"
                    . "Alasan: {$request->alasan}\n\n"
                    . "Demikian surat ini saya buat dengan sebenar-benarnya. "
                    . "Atas perhatian dan persetujuannya, saya ucapkan terima kasih.";

                SuratIzin::create([
                    'id_izin' => $izin->id_izin,
                    'id_user' => $user->id,
                    'id_ttd_pengaju' => $ttdAktif?->id_tanda_tangan,
                    'isi_surat' => $isiSurat,
                    'status_surat' => 'menunggu_manajer',
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pengajuan izin berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat izin: ' . $e->getMessage());
        }
    }

    public function approve($id)
    {
        try {
            DB::beginTransaction();

            $izin = PengajuanIzin::with('jenisIzin')->findOrFail($id);

            if ($izin->id_status !== 1) {
                return redirect()->back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
            }

            if ($izin->id_jenis_izin == 2) {
                return redirect()->back()->with('error', 'Pengajuan Cuti harus disetujui melalui menu Surat Izin (Manajer → HRD).');
            }

            $izin->update(['id_status' => 2]);

            $statusPresensiId = 3;
            if ($izin->id_jenis_izin == 1) {
                $statusPresensiId = 4;
            }

            $startDate = Carbon::parse($izin->tanggal_mulai, 'Asia/Jakarta');
            $endDate = Carbon::parse($izin->tanggal_selesai, 'Asia/Jakarta');

            while ($startDate->lte($endDate)) {
                $tanggal = $startDate->format('Y-m-d');

                Presensi::updateOrCreate(
                    [
                        'id_user' => $izin->id_user,
                        'tanggal' => $tanggal
                    ],
                    [
                        'id_status' => $statusPresensiId,
                        'jam_masuk' => null,
                        'jam_pulang' => null,
                        'id_validasi' => 1,
                        'alasan_telat' => $izin->jenisIzin->nama_izin . ': ' . $izin->alasan
                    ]
                );

                $startDate->addDay();
            }

            if ($izin->id_jenis_izin == 2) {
                $user = \App\Models\User::find($izin->id_user);
                $jumlahHari = Carbon::parse($izin->tanggal_mulai)->diffInDays(Carbon::parse($izin->tanggal_selesai)) + 1;
                $user->decrement('sisa_cuti', $jumlahHari);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Izin disetujui & data presensi diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $izin = PengajuanIzin::findOrFail($id);

        if ($izin->id_status !== 1) {
            return redirect()->back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        if ($izin->id_jenis_izin == 2) {
            return redirect()->back()->with('error', 'Pengajuan Cuti harus ditolak melalui menu Surat Izin.');
        }

        $izin->update([
            'id_status' => 3
        ]);

        return redirect()->back()->with('success', 'Pengajuan izin berhasil ditolak.');
    }
}
