@extends('layouts.app')

@section('content')

    <div class="max-w-2xl mx-auto">
        <x-page-header title="Buat Pengumuman Baru" subtitle="Isi form berikut untuk membuat pengumuman" />

        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="{{ route('pengumuman.store') }}" method="POST">
                @csrf
                <div class="space-y-5">
                    <x-input label="Judul Pengumuman" name="judul" placeholder="Contoh: Libur Hari Raya" required />

                    <x-date-input label="Tanggal Tayang" name="tanggal" required />

                    <x-textarea label="Isi Pengumuman" name="isi" placeholder="Tuliskan detail pengumuman di sini..."
                        rows="5" required />

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
