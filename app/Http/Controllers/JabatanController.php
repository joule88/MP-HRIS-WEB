<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Http\Requests\StoreJabatanRequest;
use App\Http\Requests\UpdateJabatanRequest;

class JabatanController extends Controller
{
    public function index()
    {
        $jabatan = Jabatan::latest()->paginate(10);
        return view('jabatan.index', compact('jabatan'));
    }

    public function store(StoreJabatanRequest $request)
    {
        Jabatan::create($request->validated());
        return redirect()->back()->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function update(UpdateJabatanRequest $request, $id)
    {
        $jabatan = Jabatan::findOrFail($id);
        $jabatan->update($request->validated());
        return redirect()->back()->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            Jabatan::findOrFail($id)->delete();
            return redirect()->back()->with('success', 'Jabatan berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->back()->with('error', 'Jabatan tidak bisa dihapus karena masih digunakan oleh data pegawai.');
            }
            throw $e;
        }
    }
}