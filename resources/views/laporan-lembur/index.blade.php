@extends('layouts.app')

@section('title', 'Laporan Lembur')

@section('content')
<div class="space-y-6">

    <x-page-header title="Laporan Lembur" subtitle="Rekapitulasi jam dan poin lembur pegawai bulanan.">
        <x-slot:actions>
            <div class="flex gap-2">
                <a href="{{ route('laporan-lembur.exportExcel', ['bulan' => $bulan, 'tahun' => $tahun, 'id_divisi' => $divisiId, 'search' => $search]) }}" class="inline-flex items-center justify-center px-4 py-2 border border-emerald-600 font-medium rounded-lg text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition-colors shadow-sm text-sm h-[42px]">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Excel
                </a>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        
        <div class="p-4 border-b border-slate-100 bg-slate-50/50">
            <form action="{{ route('laporan-lembur.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="w-full md:w-40">
                    <x-select label="Bulan" name="bulan" onchange="this.form.submit()" class="!mb-0">
                        @for($i=1; $i<=12; $i++)
                            @php $val = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $val }}" {{ $bulan == $val ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month((int)$i)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </x-select>
                </div>
                
                <div class="w-full md:w-32">
                    <x-select label="Tahun" name="tahun" onchange="this.form.submit()" class="!mb-0">
                        @for($i=date('Y'); $i>=2023; $i--)
                            <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </x-select>
                </div>

                <div class="w-full md:w-48">
                    <x-select label="Divisi" name="id_divisi" onchange="this.form.submit()" class="!mb-0">
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
                
                <div>
                    <x-button type="submit" variant="secondary" class="h-[44px]">
                        Filter
                    </x-button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <x-table>
                <x-slot:header>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Pegawai</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Jml Hari Lembur</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Durasi (Menit / Jam)</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Poin Diperoleh</th>
                </x-slot:header>

                @forelse($pegawai as $p)
                <tr class="hover:bg-slate-50 transition border-b border-slate-50 last:border-b-0">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <x-avatar :name="$p->nama_lengkap" class="mr-3" />
                            <div>
                                <div class="font-bold text-slate-800">{{ $p->nama_lengkap }}</div>
                                <div class="text-xs text-slate-500">{{ $p->nik ?? '-' }} • {{ $p->divisi->nama_divisi ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        <span class="inline-flex items-center justify-center px-2.5 py-1 text-sm font-semibold rounded-full bg-slate-100 text-slate-700">
                            {{ $rekap[$p->id]['jumlah_hari'] }} Hari
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <div class="flex flex-col items-end">
                            <span class="font-bold text-slate-800 text-sm">{{ number_format($rekap[$p->id]['total_menit'], 0, ',', '.') }} Menit</span>
                            <span class="text-xs text-slate-500 font-mono">≈ {{ $rekap[$p->id]['format_jam'] }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        @if($rekap[$p->id]['poin_diperoleh'] > 0)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-indigo-100 text-indigo-700">
                                +{{ number_format($rekap[$p->id]['poin_diperoleh'], 0, ',', '.') }} Poin
                            </span>
                        @else
                            <span class="text-slate-400 text-xs">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <x-empty-state colspan="4" message="Tidak ada data lembur yang disetujui pada periode ini." />
                @endforelse
            </x-table>
        </div>

        @if($pegawai->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $pegawai->links() }}
        </div>
        @endif

    </div>
</div>
@endsection
