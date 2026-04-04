<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Pengumuman;

class PengumumanApiController extends Controller
{
    public function index()
    {
        try {
            $pengumuman = Pengumuman::with('pembuat.jabatan')
                ->orderBy('tanggal', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id_pengumuman,
                        'title' => $item->judul,
                        'description' => $item->isi,
                        'tanggal' => $item->tanggal?->format('Y-m-d'),
                        'foto_url' => $item->foto ? asset('storage/' . $item->foto) : null,
                        'lampiran_url' => $item->lampiran ? asset('storage/' . $item->lampiran) : null,
                        'jabatan' => $item->pembuat?->jabatan?->nama_jabatan ?? 'Admin',
                        'nama_pembuat' => $item->pembuat?->nama_lengkap ?? 'Admin',
                        'avatar_url' => $item->pembuat?->foto,
                    ];
                });

            return ApiResponse::success($pengumuman, 'Pengumuman berhasil dimuat');
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal memuat pengumuman: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $item = Pengumuman::with('pembuat.jabatan')->findOrFail($id);

            return ApiResponse::success([
                'id' => $item->id_pengumuman,
                'title' => $item->judul,
                'description' => $item->isi,
                'tanggal' => $item->tanggal?->format('Y-m-d'),
                'foto_url' => $item->foto ? asset('storage/' . $item->foto) : null,
                'lampiran_url' => $item->lampiran ? asset('storage/' . $item->lampiran) : null,
                'jabatan' => $item->pembuat?->jabatan?->nama_jabatan ?? 'Admin',
                'nama_pembuat' => $item->pembuat?->nama_lengkap ?? 'Admin',
                'avatar_url' => $item->pembuat?->foto,
            ], 'Detail pengumuman berhasil dimuat');
        } catch (\Exception $e) {
            return ApiResponse::error('Pengumuman tidak ditemukan', 404);
        }
    }
}
