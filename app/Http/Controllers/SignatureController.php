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
        return back()->with('error', 'Tanda tangan hanya dapat dibuat melalui aplikasi Mobile. Silakan buka menu Profil → Tanda Tangan di HP Anda.');
    }
}
