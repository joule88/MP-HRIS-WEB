<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use App\Http\Requests\StoreHariLiburRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HariLiburController extends Controller
{
    public function index(Request $request)
    {
        $query = HariLibur::with('kantor');

        if ($request->filled('search')) {
            $query->where('keterangan', 'like', '%' . $request->search . '%')
                ->orWhere('tanggal', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('id_kantor')) {
            $query->where('id_kantor', $request->id_kantor);
        }

        $hariLiburs = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();
        $kantors = \App\Models\Kantor::all();

        return view('hari_libur.index', compact('hariLiburs', 'kantors'));
    }

    public function store(StoreHariLiburRequest $request)
    {
        HariLibur::create($request->validated());

        return redirect()->route('hari-libur.index')
            ->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal'     => 'required|date',
            'keterangan'  => 'required|string|max:255',
            'id_kantor'   => 'nullable|exists:kantor,id_kantor',
        ]);

        $hariLibur = HariLibur::findOrFail($id);
        $hariLibur->update($request->only(['tanggal', 'keterangan', 'id_kantor']));

        return redirect()->route('hari-libur.index')
            ->with('success', 'Hari libur berhasil diperbarui.');
    }

    public function destroy($id)
    {
        HariLibur::findOrFail($id)->delete();

        return redirect()->route('hari-libur.index')
            ->with('success', 'Hari libur berhasil dihapus.');
    }

    public function syncHolidays(Request $request)
    {
        $year = $request->get('year', date('Y'));

        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()->get("https://api-hari-libur.vercel.app/api?year={$year}");

            if ($response->successful()) {
                $body = $response->json();
                $holidays = $body['data'] ?? [];
                $count = 0;

                foreach ($holidays as $h) {
                    if (isset($h['date'])) {
                        $tanggal = $h['date'];
                        $keterangan = $h['description'];

                        $exists = HariLibur::where('tanggal', '=', $tanggal)->exists();
                        if (!$exists) {
                            HariLibur::create(['tanggal' => $tanggal, 'keterangan' => $keterangan]);
                            $count++;
                        }
                    }
                }

                return redirect()->route('hari-libur.index')
                    ->with('success', "Berhasil sinkronisasi {$count} hari libur baru untuk tahun {$year}.");
            }

            return redirect()->route('hari-libur.index')
                ->with('error', 'Gagal mengambil data dari API Hari Libur.');

        } catch (\Exception $e) {
            return redirect()->route('hari-libur.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
