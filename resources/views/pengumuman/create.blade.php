@extends('layouts.app')

@section('content')

    <div class="max-w-2xl mx-auto">
        <x-page-header title="Buat Pengumuman Baru" subtitle="Isi form berikut untuk membuat pengumuman" />

        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="{{ route('pengumuman.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-5">
                    <x-input label="Judul Pengumuman" name="judul" placeholder="Contoh: Libur Hari Raya" required />

                    <x-date-input label="Tanggal Tayang" name="tanggal" required />

                    <x-textarea label="Isi Pengumuman" name="isi" placeholder="Tuliskan detail pengumuman di sini..."
                        rows="5" required />

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Foto (Opsional)</label>
                        <input type="file" name="foto" accept="image/jpeg,image/png,image/jpg"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100" />
                        <p class="mt-1 text-xs text-slate-400">Format: JPG, PNG. Maks 2MB</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Lampiran (Opsional)</label>
                        <input type="file" name="lampiran" accept=".pdf,.doc,.docx,.xls,.xlsx"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100" />
                        <p class="mt-1 text-xs text-slate-400">Format: PDF, DOC, DOCX, XLS, XLSX. Maks 5MB</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <x-back-button href="{{ route('pengumuman.index') }}" />
                        <x-button type="submit">
                            Simpan Pengumuman
                        </x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
