@extends('layouts.app')

@section('content')

    <div class="max-w-2xl mx-auto">
        <x-page-header title="Edit Pengumuman" subtitle="Perbarui informasi pengumuman" />

        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="{{ route('pengumuman.update', $pengumuman->id_pengumuman) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <x-input label="Judul Pengumuman" name="judul" value="{{ old('judul', $pengumuman->judul) }}"
                        placeholder="Contoh: Libur Hari Raya" required />

                    <x-date-input label="Tanggal Tayang" name="tanggal"
                        value="{{ old('tanggal', $pengumuman->tanggal->format('Y-m-d')) }}" required />

                    <x-textarea label="Isi Pengumuman" name="isi" value="{{ old('isi', $pengumuman->isi) }}"
                        placeholder="Tuliskan detail pengumuman di sini..." rows="5" required />

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
