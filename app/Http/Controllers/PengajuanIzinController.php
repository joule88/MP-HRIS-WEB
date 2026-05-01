<?php

namespace App\Http\Controllers;

use App\Enums\JenisIzin as JenisIzinEnum;
use App\Enums\StatusPengajuan;
use App\Enums\StatusPresensi;
use App\Enums\StatusSurat;
use App\Enums\StatusValidasi;
use App\Events\PengajuanIzinUpdated;
use App\Http\Requests\StorePengajuanIzinRequest;
use App\Models\Divisi;
use App\Models\JenisIzin;
use App\Models\Kantor;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use App\Models\StatusPengajuan as StatusPengajuanModel;
use App\Models\User;
use App\Models\SuratIzin;
use App\Models\TandaTangan;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengajuanIzinController extends Controller
{

    public function index(Request $request)
    {
        $statusId = $request->get('status');
        $user = Auth::user();
        $isGlobalAdmin = $user->isGlobalAdmin();

        $query = PengajuanIzin::with(['user', 'jenisIzin', 'statusPengajuan', 'suratIzin']);

        if (!$isGlobalAdmin) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('id_kantor', $user->id_kantor);
            });
        }

        if ($statusId) {
            $query->where('id_status', $statusId);
        }

        if ($request->has('search') && $request->search != '') {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%');
            });
        }

        $izin = $query->orderBy('created_at', 'desc')->paginate(15);

        $statuses = StatusPengajuanModel::orderBy('id_status')->get();
        $jenisIzinList = JenisIzin::all();

        $usersQuery = User::with(['kantor', 'divisi', 'jabatan'])
            ->select('id', 'nama_lengkap', 'nik', 'id_kantor', 'id_divisi', 'id_jabatan')
            ->where('status_aktif', 1)
            ->bukanHrd()
            ->orderBy('nama_lengkap', 'asc');

        if (!$isGlobalAdmin) {
            $usersQuery->where('id_kantor', $user->id_kantor);
        }

        $users = $usersQuery->get();
        $kantor = $isGlobalAdmin ? Kantor::all() : Kantor::where('id_kantor', $user->id_kantor)->get();
        $divisi = Divisi::all();

        return view('izin.index', compact('izin', 'statusId', 'statuses', 'jenisIzinList', 'users', 'kantor', 'divisi'));
    }

    public function store(StorePengajuanIzinRequest $request)
    {

        $jenisIzinData = JenisIzin::find($request->id_jenis_izin);
        if ($jenisIzinData && $jenisIzinData->id_jenis_izin == JenisIzinEnum::CUTI) {
            $pegawai = User::find($request->id_user);
            $tanggalMulai = Carbon::parse($request->tanggal_mulai);
            $tanggalSelesai = Carbon::parse($request->tanggal_selesai);
            $jumlahHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;

            if ($jumlahHari > 3) {
                return back()->withInput()->with('error', 'Pengajuan cuti maksimal 3 hari berturut-turut dalam satu pengajuan.');
            }

            if ($pegawai && $pegawai->sisa_cuti < $jumlahHari) {
                return back()->withInput()->with('error', "Sisa cuti pegawai tidak mencukupi. Sisa: {$pegawai->sisa_cuti} hari, diajukan: {$jumlahHari} hari.");
            }

            $minDate = Carbon::now('Asia/Jakarta')->addDays(7)->startOfDay();
            if (Carbon::parse($request->tanggal_mulai, 'Asia/Jakarta')->lt($minDate)) {
                return back()->withInput()->with('error', 'Pengajuan Cuti minimal H-7!');
            }
        }

        if ($jenisIzinData && $jenisIzinData->id_jenis_izin == JenisIzinEnum::SAKIT) {
            $tanggalMulai = Carbon::parse($request->tanggal_mulai);
            $jumlahHari = $tanggalMulai->diffInDays(Carbon::parse($request->tanggal_selesai)) + 1;

            $sudahSakitBulanIni = PengajuanIzin::where('id_user', $request->id_user)
                ->where('id_jenis_izin', JenisIzinEnum::SAKIT)
                ->whereIn('id_status', [StatusPengajuan::PENDING, StatusPengajuan::DISETUJUI])
                ->whereMonth('tanggal_mulai', $tanggalMulai->month)
                ->whereYear('tanggal_mulai', $tanggalMulai->year)
                ->exists();

            if (($jumlahHari > 1 || $sudahSakitBulanIni) && !$request->hasFile('bukti_file')) {
                return back()->withInput()->with('error',
                    $jumlahHari > 1
                        ? 'Izin sakit lebih dari 1 hari wajib melampirkan Surat Keterangan Dokter (SKD).'
                        : 'Sudah pernah izin sakit di bulan ini. Wajib melampirkan Surat Keterangan Dokter (SKD).'
                );
            }
        }

        $tglMulai = $request->tanggal_mulai;
        $tglSelesai = $request->tanggal_selesai;
        $userId = $request->id_user;

        $overlappingIzin = PengajuanIzin::where('id_user', $userId)
            ->whereIn('id_status', [StatusPengajuan::PENDING, StatusPengajuan::DISETUJUI])
            ->where(function ($q) use ($tglMulai, $tglSelesai) {
                $q->whereBetween('tanggal_mulai', [$tglMulai, $tglSelesai])
                  ->orWhereBetween('tanggal_selesai', [$tglMulai, $tglSelesai])
                  ->orWhere(function ($q2) use ($tglMulai, $tglSelesai) {
                      $q2->where('tanggal_mulai', '<=', $tglMulai)
                         ->where('tanggal_selesai', '>=', $tglSelesai);
                  });
            })->exists();

        if ($overlappingIzin) {
            return back()->withInput()->with('error', 'Pegawai sudah memiliki pengajuan Izin/Cuti (Pending/Disetujui) pada rentang tanggal tersebut!');
        }

        $existingPresensi = Presensi::where('id_user', $userId)
            ->whereBetween('tanggal', [$tglMulai, $tglSelesai])
            ->whereNotNull('jam_masuk')
            ->exists();

        if ($existingPresensi) {
            return back()->withInput()->with('error', 'Pegawai sudah tercatat melakukan absensi kehadiran pada rentang tanggal tersebut!');
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
                'id_status' => StatusPengajuan::PENDING
            ]);

            if ($izin->id_jenis_izin == JenisIzinEnum::CUTI) {
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
                    'status_surat' => StatusSurat::MENUNGGU_MANAJER,
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

            $izin = PengajuanIzin::with(['jenisIzin', 'user'])->findOrFail($id);
            $user = Auth::user();

            // Security Check
            if (!$user->isGlobalAdmin() && $izin->user->id_kantor != $user->id_kantor) {
                return redirect()->back()->with('error', 'Anda tidak diizinkan menyetujui pengajuan dari kantor lain.');
            }

            if ($izin->id_status !== StatusPengajuan::PENDING) {
                return redirect()->back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
            }

            if ($izin->id_jenis_izin == JenisIzinEnum::CUTI) {
                return redirect()->back()->with('error', 'Pengajuan Cuti harus disetujui melalui menu Surat Izin (Manajer → HRD).');
            }

            $izin->update(['id_status' => StatusPengajuan::DISETUJUI]);

            $statusPresensiId = StatusPresensi::IZIN;
            if ($izin->id_jenis_izin == JenisIzinEnum::SAKIT) {
                $statusPresensiId = StatusPresensi::SAKIT;
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
                        'id_validasi' => StatusValidasi::VALID,
                        'alasan_telat' => $izin->jenisIzin->nama_izin . ': ' . $izin->alasan
                    ]
                );

                $startDate->addDay();
            }

            DB::commit();

            app(NotifikasiService::class)->kirim(
                $izin->id_user,
                'izin_disetujui',
                'Pengajuan Izin Disetujui',
                'Pengajuan ' . $izin->jenisIzin->nama_izin . ' Anda telah disetujui.',
                ['id_izin' => $izin->id_izin]
            );

            broadcast(new PengajuanIzinUpdated(
                $izin->id_user,
                $izin->id_izin,
                'disetujui',
                $izin->jenisIzin->nama_izin,
                'Pengajuan ' . $izin->jenisIzin->nama_izin . ' disetujui.'
            ));

            return redirect()->back()->with('success', 'Izin disetujui & data presensi diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $izin = PengajuanIzin::with(['user', 'jenisIzin'])->findOrFail($id);
        $user = Auth::user();

        if (!$user->isGlobalAdmin() && $izin->user->id_kantor != $user->id_kantor) {
            return redirect()->back()->with('error', 'Anda tidak diizinkan menolak pengajuan dari kantor lain.');
        }

        if ($izin->id_status !== StatusPengajuan::PENDING) {
            return redirect()->back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        if ($izin->id_jenis_izin == JenisIzinEnum::CUTI) {
            return redirect()->back()->with('error', 'Pengajuan Cuti harus ditolak melalui menu Surat Izin.');
        }

        try {
            DB::beginTransaction();

            $izin->update([
                'id_status'         => StatusPengajuan::DITOLAK,
                'alasan_penolakan'  => $request->alasan_penolakan,
            ]);

            DB::commit();

            app(NotifikasiService::class)->kirim(
                $izin->id_user,
                'izin_ditolak',
                'Pengajuan Izin Ditolak',
                'Pengajuan ' . ($izin->jenisIzin->nama_izin ?? 'Izin') . ' Anda ditolak. Catatan: ' . ($request->alasan_penolakan ?? '-'),
                ['id_izin' => $izin->id_izin]
            );

            broadcast(new PengajuanIzinUpdated(
                $izin->id_user,
                $izin->id_izin,
                'ditolak',
                $izin->jenisIzin->nama_izin ?? 'Izin',
                'Pengajuan ' . ($izin->jenisIzin->nama_izin ?? 'Izin') . ' ditolak.'
            ));

            return redirect()->back()->with('success', 'Pengajuan izin berhasil ditolak.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menolak pengajuan: ' . $e->getMessage());
        }
    }
}
