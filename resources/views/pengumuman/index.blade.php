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
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <x-table>
            <x-slot:header>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left w-14">No</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Judul</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Tanggal</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Dibuat Oleh</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
            </x-slot:header>
            @forelse ($pengumuman as $item)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                        {{ $loop->iteration + ($pengumuman->currentPage() - 1) * $pengumuman->perPage() }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-slate-900">{{ $item->judul }}</div>
                        <div class="text-sm text-slate-500 truncate max-w-xs">{{ Str::limit($item->isi, 50) }}</div>
                        @if($item->foto || $item->lampiran)
                            <div class="flex gap-2 mt-1">
                                @if($item->foto)
                                    <span class="inline-flex items-center gap-1 text-xs text-blue-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        Foto
                                    </span>
                                @endif
                                @if($item->lampiran)
                                    <span class="inline-flex items-center gap-1 text-xs text-green-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                        Lampiran
                                    </span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                        {{ $item->tanggal ? $item->tanggal->translatedFormat('d M Y') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
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
        </div>

        <x-pagination :paginator="$pengumuman" />
    </div>
@endsection
