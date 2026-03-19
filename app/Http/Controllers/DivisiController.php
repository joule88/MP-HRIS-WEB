<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Http\Requests\StoreDivisiRequest;
use App\Http\Requests\UpdateDivisiRequest;

class DivisiController extends Controller
{
    public function index()
    {
        $divisi = Divisi::latest()->paginate(10);
        return view('divisi.index', compact('divisi'));
    }

    public function store(StoreDivisiRequest $request)
    {

        Divisi::create($request->validated());

        return redirect()->back()->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function update(UpdateDivisiRequest $request, $id)
    {
        $divisi = Divisi::findOrFail($id);

        $divisi->update($request->validated());

        return redirect()->back()->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            Divisi::findOrFail($id)->delete();
            return redirect()->back()->with('success', 'Divisi berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->back()->with('error', 'Divisi tidak bisa dihapus karena masih digunakan oleh data pegawai.');
            }
            throw $e;
        }
    }
}
