<?php

namespace App\Http\Controllers;

use App\Models\Lembur;
use App\Models\User;
use App\Models\JenisKompensasi;
use App\Services\LemburService;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
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
        $lembur = Lembur::with(['user', 'status', 'kompensasi'])
            ->orderByRaw("CASE WHEN id_status = 1 THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('lembur.index', compact('lembur'));
    }

    public function create()
    {
        $pegawai = User::where('status_aktif', 1)->orderBy('nama_lengkap', 'asc')->get();
        $kompensasi = JenisKompensasi::all();
        return view('lembur.create', compact('pegawai', 'kompensasi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id',
            'tanggal_lembur' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'id_kompensasi' => 'required|exists:jenis_kompensasi,id_kompensasi',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::findOrFail($request->id_user);
                $lembur = $this->lemburService->createLembur($user, $request->all());
                $this->lemburService->approve($lembur);
            });

            return redirect()->route('lembur.index')->with('success', 'Data lembur manual berhasil ditambahkan dan disetujui otomatis.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan lembur: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, Lembur $lembur)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'alasan_penolakan' => 'required_if:action,reject|nullable|string'
        ]);

        try {
            if ($request->action == 'approve') {
                $this->lemburService->approve($lembur);
                $message = 'Pengajuan lembur berhasil disetujui.';

                if ($lembur->id_kompensasi == 2) {
                    $message .= ' Poin telah ditambahkan ke karyawan.';
                }

                app(NotifikasiService::class)->kirim(
                    $lembur->id_user,
                    'lembur_disetujui',
                    'Lembur Disetujui',
                    'Pengajuan lembur Anda pada tanggal ' . $lembur->tanggal_lembur . ' telah disetujui.',
                    ['id_lembur' => $lembur->id_lembur]
                );
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
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses pengajuan: ' . $e->getMessage());
        }
    }
}