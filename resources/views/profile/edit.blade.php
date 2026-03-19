@extends('layouts.app')

@section('title', 'Pengaturan Akun')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">

        <x-page-header title="Pengaturan Akun" subtitle="Kelola informasi profil dan keamanan akun Anda." />

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col md:flex-row gap-8">
                        
                        <div class="flex flex-col items-center space-y-4">
                            <div class="relative w-32 h-32">
                                @if(auth()->user()->foto)
                                    <img src="{{ asset('storage/' . auth()->user()->foto) }}"
                                        class="w-full h-full rounded-full object-cover border-4 border-slate-100 shadow-md"
                                        id="preview-foto">
                                @else
                                    <div class="w-full h-full rounded-full bg-slate-100 flex items-center justify-center text-slate-400 border-4 border-slate-50 shadow-sm"
                                        id="placeholder-foto">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <img src=""
                                        class="w-full h-full rounded-full object-cover border-4 border-slate-100 shadow-md hidden"
                                        id="preview-foto-new">
                                @endif

                                <label for="foto-input"
                                    class="absolute bottom-0 right-0 p-2 bg-white rounded-full shadow-lg border border-slate-200 cursor-pointer hover:bg-slate-50 transition">
                                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </label>
                                <input type="file" id="foto-input" name="foto" class="hidden" accept="image/*"
                                    onchange="previewImage(this)">
                            </div>
                            <div class="text-center">
                                <h3 class="font-bold text-slate-800">{{ auth()->user()->nama_lengkap }}</h3>
                                <p class="text-sm text-slate-500">{{ auth()->user()->role->nama_role ?? 'User' }}</p>
                            </div>
                        </div>

                        <div class="flex-1 space-y-4">
                            <x-input label="Nama Lengkap" name="nama_lengkap" :value="old('nama_lengkap', auth()->user()->nama_lengkap)" required />

                            <x-input type="email" label="Alamat Email" name="email" :value="old('email', auth()->user()->email)" required />

                            <div class="pt-4 border-t border-slate-100">
                                <h4 class="text-sm font-bold text-slate-800 mb-3 uppercase tracking-wide">Ganti Password
                                </h4>
                                <p class="text-xs text-slate-500 mb-4">Kosongkan jika tidak ingin mengubah password.</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-input type="password" label="Password Baru" name="password"
                                        placeholder="Minimal 8 karakter" />
                                    <x-input type="password" label="Konfirmasi Password" name="password_confirmation"
                                        placeholder="Ulangi password baru" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-6 border-t border-slate-100">
                        <button type="submit"
                            class="px-6 py-2.5 bg-primary text-white font-semibold rounded-xl hover:bg-primary/90 transition shadow-lg shadow-indigo-500/20">
                            Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('preview-foto');
                    const previewNew = document.getElementById('preview-foto-new');
                    const placeholder = document.getElementById('placeholder-foto');

                    if (preview) {
                        preview.src = e.target.result;
                    } else {
                        if (placeholder) placeholder.classList.add('hidden');
                        if (previewNew) {
                            previewNew.src = e.target.result;
                            previewNew.classList.remove('hidden');
                        }
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
