@extends('layouts.app')

@section('title', 'Laporan Izin & Cuti')

@section('content')
<div class="space-y-6">

    <x-page-header title="Laporan Izin & Cuti" subtitle="Histori pengajuan izin dan cuti pegawai secara bulanan.">
        <x-slot:actions>
            <div class="flex gap-2">
                <x-button type="link" href="#" class="!bg-emerald-50 !text-emerald-700 hover:!bg-emerald-100 !border-emerald-600 !ring-0 flex items-center gap-2 h-[42px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Excel
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        
        <div class="p-4 border-b border-slate-100 bg-slate-50/50">
            <form action="{{ route('laporan.izin') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="w-full md:w-40">
                    <x-select label="Bulan" name="bulan" onchange="this.form.submit()">
                        @for($i=1; $i<=12; $i++)
                            @php $val = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $val }}" {{ $bulan == $val ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month((int)$i)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </x-select>
                </div>
                
                <div class="w-full md:w-32">
                    <x-select label="Tahun" name="tahun" onchange="this.form.submit()">
                        @for($i=date('Y'); $i>=2023; $i--)
                            <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </x-select>
                </div>

                <div class="w-full md:w-48">
                    <x-select label="Divisi" name="id_divisi" onchange="this.form.submit()">
                        <option value="">Semua Divisi</option>
                        @foreach($divisiList as $div)
                            <option value="{{ $div->id_divisi }}" {{ ($divisiId ?? '') == $div->id_divisi ? 'selected' : '' }}>
                                {{ $div->nama_divisi }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div class="flex-1 min-w-[200px]">
                    <x-input type="text" name="search" label="Cari Pegawai" value="{{ $search }}" placeholder="Nama/NIK..." 
                        class="!mb-0" oninput="if(this.value.length === 0) this.form.submit()" />
                </div>
                
                <div class="pb-1">
                    <x-button type="submit" variant="secondary" class="h-[42px]">
                        Filter
                    </x-button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <x-table>
                <x-slot:header>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Pegawai</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Tanggal</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Kategori</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Alasan</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                </x-slot:header>

                @forelse($izinList as $izin)
                <tr class="hover:bg-slate-50 transition border-b border-slate-50 last:border-b-0">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <x-avatar :name="$izin->user->nama_lengkap ?? 'Unknown'" class="mr-3" />
                            <div>
                                <div class="font-bold text-slate-800">{{ $izin->user->nama_lengkap ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $izin->user->divisi->nama_divisi ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 font-medium">
                        @if($izin->tanggal_mulai == $izin->tanggal_selesai)
                            {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->translatedFormat('d M Y') }}
                        @else
                            {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->translatedFormat('d M Y') }}
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-slate-100 text-slate-800 rounded-md">
                            {{ $izin->jenisIzin->nama_izin ?? 'Izin Khusus' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">
                        <div class="max-w-xs truncate" title="{{ $izin->alasan }}">{{ $izin->alasan }}</div>
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        @php
                            $color = 'yellow'; $label = 'Menunggu';
                            if($izin->status == 'disetujui') { $color = 'green'; $label = 'Disetujui'; }
                            elseif($izin->status == 'ditolak') { $color = 'red'; $label = 'Ditolak'; }
                        @endphp
                        <x-badge color="{{ $color }}">
                            {{ $label }}
                        </x-badge>
                    </td>
                </tr>
                @empty
                <x-empty-state colspan="5" message="Tidak ada data pengajuan izin/cuti pada periode ini." />
                @endforelse
            </x-table>
        </div>

        @if($izinList->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $izinList->links() }}
        </div>
        @endif

    </div>
</div>
@endsection
