@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')

    <x-page-header title="Daftar Pengumuman" subtitle="Kelola informasi untuk karyawan">
        <x-button href="{{ route('pengumuman.create') }}" class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Buat Pengumuman
        </x-button>
    </x-page-header>

    <div class="mt-6">
        <x-table>
            <x-slot:header>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left w-14">No</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Judul</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Tanggal</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Dibuat Oleh</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
            </x-slot:header>
            @forelse ($pengumuman as $item)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $loop->iteration + ($pengumuman->currentPage() - 1) * $pengumuman->perPage() }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $item->judul }}</div>
                        <div class="text-sm text-gray-500 truncate max-w-xs">{{ Str::limit($item->isi, 50) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $item->tanggal->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $item->pembuat->nama_lengkap ?? 'System' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2">
                            <x-button-edit href="{{ route('pengumuman.edit', $item->id_pengumuman) }}" />

                            <form id="delete-form-{{ $item->id_pengumuman }}"
                                action="{{ route('pengumuman.destroy', $item->id_pengumuman) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                            <x-delete-button :id="$item->id_pengumuman" />
                        </div>
                    </td>
                </tr>
            @empty
                <x-empty-state colspan="5" message="Belum ada pengumuman" hint="Silakan buat pengumuman baru untuk karyawan." />
            @endforelse
        </x-table>

        <x-pagination :paginator="$pengumuman" />
    </div>
@endsection
