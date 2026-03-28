<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengumumanRequest;
use App\Http\Requests\UpdatePengumumanRequest;
use App\Models\Pengumuman;
use App\Services\NotifikasiService;
use Illuminate\Support\Facades\Auth;

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
        $pengumuman->update($request->validated());

        return redirect()->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return redirect()->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}