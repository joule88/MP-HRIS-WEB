<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class CutiController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = User::with(['jabatan', 'divisi'])->whereHas('roles', function($q) {
            $q->where('nama_role', '!=', 'super_admin');
        });

        if ($search) {
            $query->where('nama_lengkap', 'like', '%' . $search . '%')
                  ->orWhere('nik', 'like', '%' . $search . '%');
        }

        $pegawai = $query->orderBy('nama_lengkap', 'asc')->paginate(15);

        return view('cuti.index', compact('pegawai', 'search'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sisa_cuti' => 'required|integer|min:0',
            'alasan' => 'nullable|string|max:255'
        ]);

        $user = User::findOrFail($id);

        $sisaLama = $user->sisa_cuti;
        $user->update([
            'sisa_cuti' => $request->sisa_cuti
        ]);

        return redirect()->back()->with('success', 'Sisa cuti ' . $user->nama_lengkap . ' berhasil diperbarui.');
    }

    public function resetMassal(Request $request)
    {
        $request->validate([
            'jumlah_hari' => 'required|integer|min:0'
        ]);

        User::query()->update([
            'sisa_cuti' => $request->jumlah_hari
        ]);

        return redirect()->back()->with('success', 'Sisa cuti semua pegawai berhasil di-reset menjadi ' . $request->jumlah_hari . ' hari.');
    }
}
