<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignatureRequest;
use App\Models\TandaTangan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SignatureController extends Controller
{
    public function show()
    {
        $ttd = TandaTangan::where('id_user', Auth::id())->active()->first();
        return view('signature.show', compact('ttd'));
    }

    public function store(StoreSignatureRequest $request)
    {
        try {
            $base64 = $request->signature_data;

            if (str_contains($base64, ',')) {
                $base64 = explode(',', $base64)[1];
            }

            $imageData = base64_decode($base64);
            if ($imageData === false) {
                return back()->with('error', 'Data tanda tangan tidak valid.');
            }

            $fileName = 'uploads/ttd/' . Auth::id() . '_' . time() . '.png';
            Storage::disk('public')->put($fileName, $imageData);

            TandaTangan::where('id_user', Auth::id())->update(['is_active' => false]);

            TandaTangan::create([
                'id_user' => Auth::id(),
                'file_ttd' => $fileName,
                'is_active' => true,
            ]);

            return back()->with('success', 'Tanda tangan berhasil disimpan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan tanda tangan: ' . $e->getMessage());
        }
    }
}
