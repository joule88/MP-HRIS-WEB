<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengumumanRequest;
use App\Http\Requests\UpdatePengumumanRequest;
use App\Models\Pengumuman;
use App\Services\NotifikasiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PengumumanController extends Controller
{
    public function index()
    {
        $pengumuman = Pengumuman::with('pembuat')
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        $title = 'Pengumuman';

        return view('pengumuman.index', compact('pengumuman', 'title'));
    }

    public function create()
    {
        $title = 'Buat Pengumuman';
        return view('pengumuman.create', compact('title'));
    }

    public function store(StorePengumumanRequest $request)
    {
        $data = $request->validated();
        $data['dibuat_oleh'] = Auth::id();

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('uploads/pengumuman/foto', 'public');
        }

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('uploads/pengumuman/lampiran', 'public');
        }

        $pengumuman = Pengumuman::create($data);

        app(NotifikasiService::class)->kirimBroadcast(
            'pengumuman_baru',
            '📢 Pengumuman Baru',
            $pengumuman->judul,
            ['id_pengumuman' => $pengumuman->id]
        );

        return redirect()->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function edit(Pengumuman $pengumuman)
    {
        $title = 'Edit Pengumuman';
        return view('pengumuman.edit', compact('pengumuman', 'title'));
    }

    public function update(UpdatePengumumanRequest $request, Pengumuman $pengumuman)
    {
        $data = $request->validated();

        if ($request->hasFile('foto')) {
            if ($pengumuman->foto) {
                Storage::disk('public')->delete($pengumuman->foto);
            }
            $data['foto'] = $request->file('foto')->store('uploads/pengumuman/foto', 'public');
        }

        if ($request->hasFile('lampiran')) {
            if ($pengumuman->lampiran) {
                Storage::disk('public')->delete($pengumuman->lampiran);
            }
            $data['lampiran'] = $request->file('lampiran')->store('uploads/pengumuman/lampiran', 'public');
        }

        $pengumuman->update($data);

        return redirect()->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        if ($pengumuman->foto) {
            Storage::disk('public')->delete($pengumuman->foto);
        }
        if ($pengumuman->lampiran) {
            Storage::disk('public')->delete($pengumuman->lampiran);
        }

        $pengumuman->delete();

        return redirect()->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}