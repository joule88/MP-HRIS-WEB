<?php

namespace App\Http\Controllers;

use App\Enums\JenisKompensasi as JenisKompensasiEnum;
use App\Enums\StatusPengajuan;
use App\Events\LemburUpdated;
use App\Models\Lembur;
use App\Models\User;
use App\Models\JenisKompensasi;
use App\Services\LemburService;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LemburController extends Controller
{
    protected $lemburService;

    public function __construct(LemburService $lemburService)
    {
        $this->lemburService = $lemburService;
    }

    public function index()
    {
        $user = Auth::user();
        $isGlobalAdmin = $user->isGlobalAdmin();

        $query = Lembur::with(['user.jabatan', 'status', 'kompensasi']);

        if (!$isGlobalAdmin) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('id_kantor', $user->id_kantor);
            });
        }

        $lembur = $query->orderByRaw("CASE WHEN id_status = " . StatusPengajuan::PENDING . " THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('lembur.index', compact('lembur'));
    }

    public function create()
    {
        $user = Auth::user();
        $query = User::where('status_aktif', 1);

        if (!$user->isGlobalAdmin()) {
            $query->where('id_kantor', $user->id_kantor);
        }

        $pegawai = $query->orderBy('nama_lengkap', 'asc')->get();
        $kompensasi = JenisKompensasi::all();
        return view('lembur.create', compact('pegawai', 'kompensasi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id',
            'tanggal_lembur' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|different:jam_mulai',
            'id_kompensasi' => 'required|exists:jenis_kompensasi,id_kompensasi',
            'keterangan' => 'nullable|string|max:255'
        ]);

        $userAuth = Auth::user();
        $targetUser = User::findOrFail($request->id_user);

        if (!$userAuth->isGlobalAdmin() && $targetUser->id_kantor != $userAuth->id_kantor) {
            return redirect()->back()->with('error', 'Anda tidak diizinkan membuat lembur untuk pegawai kantor lain.')->withInput();
        }

        try {
            $lembur = null;
            DB::transaction(function () use ($request, $targetUser, &$lembur) {
                $lembur = $this->lemburService->createLembur($targetUser, $request->all());
                $this->lemburService->approve($lembur);
            });

            if ($lembur) {
                $message = 'Lembur manual Anda pada tanggal ' . $lembur->tanggal_lembur->translatedFormat('d F Y') . ' telah ditambahkan dan disetujui oleh HRD.';
                if ($lembur->id_kompensasi == JenisKompensasiEnum::TAMBAHAN_POIN) {
                    $message .= ' Poin telah ditambahkan ke saldo Anda.';
                }
                app(NotifikasiService::class)->kirim(
                    $targetUser->id,
                    'lembur_disetujui',
                    'Lembur Manual Disetujui',
                    $message,
                    ['id_lembur' => $lembur->id_lembur]
                );

                broadcast(new LemburUpdated($targetUser->id, $lembur->id_lembur, 'disetujui', $message));
            }

            return redirect()->route('lembur.index')->with('success', 'Data lembur manual berhasil ditambahkan dan disetujui otomatis.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan lembur: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, Lembur $lembur)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'alasan_penolakan' => 'required_if:action,reject|nullable|string|max:500'
        ]);

        $lembur->load('user');
        $userAuth = Auth::user();
        if (!$userAuth->isGlobalAdmin() && $lembur->user->id_kantor != $userAuth->id_kantor) {
            return redirect()->back()->with('error', 'Anda tidak diizinkan memproses data dari kantor lain.');
        }

        try {
            if ($request->action == 'approve') {
                $this->lemburService->approve($lembur);
                $message = 'Pengajuan lembur berhasil disetujui.';

                if ($lembur->id_kompensasi == JenisKompensasiEnum::TAMBAHAN_POIN) {
                    $message .= ' Poin telah ditambahkan ke karyawan.';
                }

                app(NotifikasiService::class)->kirim(
                    $lembur->id_user,
                    'lembur_disetujui',
                    'Lembur Disetujui',
                    'Pengajuan lembur Anda pada tanggal ' . $lembur->tanggal_lembur . ' telah disetujui.',
                    ['id_lembur' => $lembur->id_lembur]
                );

                broadcast(new LemburUpdated($lembur->id_user, $lembur->id_lembur, 'disetujui', $message));
            } else {
                $this->lemburService->reject($lembur, $request->alasan_penolakan);
                $message = 'Pengajuan lembur berhasil ditolak.';

                app(NotifikasiService::class)->kirim(
                    $lembur->id_user,
                    'lembur_ditolak',
                    'Lembur Ditolak',
                    'Pengajuan lembur Anda pada tanggal ' . $lembur->tanggal_lembur . ' ditolak. Alasan: ' . ($request->alasan_penolakan ?? '-'),
                    ['id_lembur' => $lembur->id_lembur]
                );

                broadcast(new LemburUpdated($lembur->id_user, $lembur->id_lembur, 'ditolak', $message));
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses pengajuan: ' . $e->getMessage());
        }
    }
}