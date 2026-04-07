@extends('layouts.app')

@section('title', 'Pengaturan Akun')

@section('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <style>
        .cropper-view-box,
        .cropper-face {
            border-radius: 50%;
        }

        .cropper-view-box {
            outline: 3px solid rgba(255, 255, 255, 0.8);
            outline-offset: 0;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
        }

        .cropper-face {
            background-color: transparent !important;
        }

        .cropper-dashed,
        .cropper-point,
        .cropper-line {
            display: none !important;
        }

        .cropper-crop-box {
            border-radius: 50%;
        }

        .cropper-modal {
            background-color: rgba(15, 23, 42, 0.85);
        }
    </style>
@endsection

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
                                <input type="file" id="foto-input" name="foto" class="hidden" accept="image/*">
                            </div>
                            <div class="text-center">
                                <h3 class="font-bold text-slate-800">{{ auth()->user()->nama_lengkap }}</h3>
                                <p class="text-sm text-slate-500">{{ auth()->user()->roles->first()->nama_role ?? 'User' }}</p>
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
                        <button type="submit" id="submitBtn"
                            class="px-6 py-2.5 bg-primary text-white font-semibold rounded-xl hover:bg-primary/90 transition shadow-lg shadow-indigo-500/20">
                            Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Modal Crop Foto --}}
    <div id="crop-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-500/50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden" onclick="event.stopPropagation()">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Atur Foto Profil</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Geser dan zoom untuk mengatur posisi foto</p>
                </div>
                <button id="crop-cancel-btn" type="button"
                    class="p-2 rounded-lg hover:bg-slate-100 transition text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Crop Area --}}
            <div class="relative bg-slate-900 flex items-center justify-center" style="height: 380px;">
                <img id="crop-image" src="" alt="Crop Preview" class="max-w-full max-h-full">
            </div>

            {{-- Toolbar --}}
            <div class="flex items-center justify-center gap-2 px-6 py-3 bg-slate-50 border-t border-b border-slate-100">
                <button id="crop-rotate-left" type="button"
                    class="p-2.5 rounded-xl hover:bg-white hover:shadow-sm transition text-slate-500 hover:text-slate-700"
                    title="Putar Kiri">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h1l1-2m0 0l2-2m-2 2l2 2m6-8a9 9 0 11-4.2 1.1"></path>
                    </svg>
                </button>
                <button id="crop-rotate-right" type="button"
                    class="p-2.5 rounded-xl hover:bg-white hover:shadow-sm transition text-slate-500 hover:text-slate-700"
                    title="Putar Kanan">
                    <svg class="w-5 h-5 transform scale-x-[-1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h1l1-2m0 0l2-2m-2 2l2 2m6-8a9 9 0 11-4.2 1.1"></path>
                    </svg>
                </button>
                <div class="w-px h-6 bg-slate-200 mx-1"></div>
                <button id="crop-zoom-in" type="button"
                    class="p-2.5 rounded-xl hover:bg-white hover:shadow-sm transition text-slate-500 hover:text-slate-700"
                    title="Zoom In">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                    </svg>
                </button>
                <button id="crop-zoom-out" type="button"
                    class="p-2.5 rounded-xl hover:bg-white hover:shadow-sm transition text-slate-500 hover:text-slate-700"
                    title="Zoom Out">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path>
                    </svg>
                </button>
                <div class="w-px h-6 bg-slate-200 mx-1"></div>
                <button id="crop-reset" type="button"
                    class="p-2.5 rounded-xl hover:bg-white hover:shadow-sm transition text-slate-500 hover:text-slate-700"
                    title="Reset">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4">
                <button id="crop-cancel-btn-footer" type="button"
                    class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition"
                    onclick="document.getElementById('crop-cancel-btn').click()">
                    Batal
                </button>
                <button id="crop-save-btn" type="button"
                    class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-primary rounded-xl hover:bg-primary/90 transition shadow-lg shadow-indigo-500/20">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan
                </button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script src="{{ asset('js/image-cropper.js') }}"></script>
@endsection
