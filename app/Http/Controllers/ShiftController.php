<?php

namespace App\Http\Controllers;

use App\Models\ShiftKerja;
use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $query = ShiftKerja::withCount('jadwalKerja');

        if ($request->filled('search')) {
            $query->where('nama_shift', 'like', '%' . $request->search . '%');
        }

        $shifts = $query->latest('id_shift')->paginate(10)->withQueryString();

        return view('shift.index', compact('shifts'));
    }

    public function store(StoreShiftRequest $request)
    {
        ShiftKerja::create($request->validated());

        return redirect()->route('shift.index')
            ->with('success', 'Shift kerja berhasil ditambahkan.');
    }

    public function update(UpdateShiftRequest $request, $id)
    {
        $shift = ShiftKerja::findOrFail($id);
        $shift->update($request->validated());

        return redirect()->route('shift.index')
            ->with('success', 'Shift kerja berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $shift = ShiftKerja::findOrFail($id);

        if ($shift->jadwalKerja()->exists()) {
            return redirect()->back()
                ->with('error', 'Shift tidak bisa dihapus karena masih digunakan di jadwal.');
        }

        $shift->delete();

        return redirect()->route('shift.index')
            ->with('success', 'Shift kerja berhasil dihapus.');
    }
}