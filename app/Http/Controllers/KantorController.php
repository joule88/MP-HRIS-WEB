<?php

namespace App\Http\Controllers;

use App\Models\Kantor;
use App\Http\Requests\StoreKantorRequest;
use App\Http\Requests\UpdateKantorRequest;

class KantorController extends Controller
{
    public function index()
    {
        $kantor = Kantor::latest()->paginate(10);
        return view('kantor.index', compact('kantor'));
    }

    public function store(StoreKantorRequest $request)
    {
        Kantor::create($request->validated());
        return redirect()->back()->with('success', 'Lokasi kantor berhasil ditambahkan.');
    }

    public function update(UpdateKantorRequest $request, $id)
    {
        $kantor = Kantor::findOrFail($id);
        $kantor->update($request->validated());
        return redirect()->back()->with('success', 'Data kantor berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            Kantor::findOrFail($id)->delete();
            return redirect()->back()->with('success', 'Data kantor berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->back()->with('error', 'Kantor tidak bisa dihapus karena masih digunakan oleh data pegawai.');
            }
            throw $e;
        }
    }
}