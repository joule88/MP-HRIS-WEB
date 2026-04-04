@extends('layouts.app')

@section('content')

    <div class="max-w-2xl mx-auto">
        <x-page-header title="Edit Pengumuman" subtitle="Perbarui informasi pengumuman" />

        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="{{ route('pengumuman.update', $pengumuman->id_pengumuman) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <x-input label="Judul Pengumuman" name="judul" value="{{ old('judul', $pengumuman->judul) }}"
                        placeholder="Contoh: Libur Hari Raya" required />

                    <x-date-input label="Tanggal Tayang" name="tanggal"
                        value="{{ old('tanggal', $pengumuman->tanggal->format('Y-m-d')) }}" required />

                    <x-textarea label="Isi Pengumuman" name="isi" value="{{ old('isi', $pengumuman->isi) }}"
                        placeholder="Tuliskan detail pengumuman di sini..." rows="5" required />

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Foto (Opsional)</label>
                        @if($pengumuman->foto)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $pengumuman->foto) }}" alt="Foto Pengumuman" class="h-32 rounded-lg object-cover border" />
                                <p class="text-xs text-slate-400 mt-1">Foto saat ini. Upload baru untuk mengganti.</p>
                            </div>
                        @endif
                        <input type="file" name="foto" accept="image/jpeg,image/png,image/jpg"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100" />
                        <p class="mt-1 text-xs text-slate-400">Format: JPG, PNG. Maks 2MB</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Lampiran (Opsional)</label>
                        @if($pengumuman->lampiran)
                            <div class="mb-2">
                                <a href="{{ asset('storage/' . $pengumuman->lampiran) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    Lihat lampiran saat ini
                                </a>
                                <p class="text-xs text-slate-400 mt-1">Upload baru untuk mengganti.</p>
                            </div>
                        @endif
                        <input type="file" name="lampiran" accept=".pdf,.doc,.docx,.xls,.xlsx"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100" />
                        <p class="mt-1 text-xs text-slate-400">Format: PDF, DOC, DOCX, XLS, XLSX. Maks 5MB</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <x-back-button href="{{ route('pengumuman.index') }}" />
                        <x-button type="submit">
                            Simpan Perubahan
                        </x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
