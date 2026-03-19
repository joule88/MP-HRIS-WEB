@extends('layouts.app')

@section('title', 'Laporan Bulanan')

@section('content')
    <div class="space-y-6">

        <x-page-header title="Rekapitulasi Presensi" subtitle="Laporan kinerja karyawan per bulan" />

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">

            <form method="GET" action="{{ route('laporan.index') }}"
                class="flex flex-wrap gap-4 items-end mb-6 border-b border-slate-100 pb-6">
                <div class="w-full md:w-40">
                    <x-select label="Bulan" name="bulan">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                            </option>
                        @endfor
                    </x-select>
                </div>
                <div class="w-full md:w-32">
                    <x-select label="Tahun" name="tahun">
                        @for ($y = date('Y'); $y >= date('Y') - 2; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </x-select>
                </div>
                <div class="w-full md:w-48">
                    <x-select label="Divisi" name="id_divisi">
                        <option value="">Semua Divisi</option>
                        @foreach($divisiList as $div)
                            <option value="{{ $div->id_divisi }}" {{ $divisiId == $div->id_divisi ? 'selected' : '' }}>
                                {{ $div->nama_divisi }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
                <div class="w-full md:w-auto pb-0.5 flex gap-2">
                    <x-button type="submit" variant="primary" class="h-[42px]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg>
                        Tampilkan
                    </x-button>
                    <a href="{{ route('laporan.export', ['bulan' => $bulan, 'tahun' => $tahun, 'id_divisi' => $divisiId]) }}" data-turbo="false"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:text-emerald-800 border border-emerald-200 rounded-lg text-sm font-semibold transition-all h-[42px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Ekspor Excel
                    </a>
                    
                    <a href="{{ route('laporan.exportPdf', ['bulan' => $bulan, 'tahun' => $tahun, 'id_divisi' => $divisiId]) }}" data-turbo="false"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 border border-red-200 rounded-lg text-sm font-semibold transition-all h-[42px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Ekspor PDF
                    </a>
                </div>

            </form>

            <x-table>
                <x-slot:header>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Karyawan</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Divisi</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Total Hadir</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Izin</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Sakit</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Terlambat (Kali)</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Poin Lembur</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Alpha</th>
                </x-slot:header>

                @forelse ($rekap as $row)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <x-avatar :name="$row['user']->nama_lengkap ?? '-'" class="mr-3" />
                                <div>
                                    <div class="text-sm font-medium text-slate-900">{{ $row['user']->nama_lengkap }}</div>
                                    <div class="text-xs text-slate-500">{{ $row['user']->jabatan->nama_jabatan ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                            {{ $row['user']->divisi->nama_divisi ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-slate-700">
                            <x-badge color="green">{{ $row['hadir'] }} Hari</x-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            @if($row['izin'] > 0)
                                <x-badge color="blue">{{ $row['izin'] }}x</x-badge>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            @if($row['sakit'] > 0)
                                <x-badge color="orange">{{ $row['sakit'] }}x</x-badge>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            @if($row['terlambat'] > 0)
                                <x-badge color="red">{{ $row['terlambat'] }}x</x-badge>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <span class="text-purple-600 font-medium">{{ $row['poin_lembur'] }} Poin</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            @if($row['alpha'] > 0)
                                <x-badge color="red" variant="flat">{{ $row['alpha'] }} Hari</x-badge>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <x-empty-state colspan="7" message="Tidak ada data presensi pada periode ini" />
                @endforelse
            </x-table>
        </div>
    </div>
@endsection
