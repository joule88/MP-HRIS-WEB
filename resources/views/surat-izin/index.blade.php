@extends('layouts.app')

@section('title', 'Surat Izin')

@section('content')
    <div class="space-y-6">

        <x-page-header title="Surat Izin Digital" subtitle="Kelola dan setujui surat izin karyawan." class="lg:items-end">
            <div class="flex gap-3">
                <form action="{{ route('surat-izin.index') }}" method="GET" class="flex gap-2 w-full md:w-auto">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <x-input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pegawai..."
                        class="!mb-0 w-full md:w-64" />
                    <x-button type="submit" variant="secondary" class="h-10 px-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </x-button>
                </form>

                <x-filter-select name="status"
                    onchange="window.location.href='{{ route('surat-izin.index') }}?status=' + this.value + '&search={{ request('search') }}'">
                    <option value="">Semua Status</option>
                    <option value="menunggu_manajer" {{ request('status') == 'menunggu_manajer' ? 'selected' : '' }}>Menunggu
                        Manajer</option>
                    <option value="menunggu_hrd" {{ request('status') == 'menunggu_hrd' ? 'selected' : '' }}>Menunggu HRD
                    </option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </x-filter-select>
            </div>
        </x-page-header>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <x-table>
                <x-slot:header>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-left">No. Surat</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-left">Pegawai</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-left">Jenis Izin</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-left">Tanggal</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
                </x-slot:header>

                @forelse($suratList as $surat)
                    <tr class="hover:bg-slate-50 border-b border-slate-50 last:border-b-0">
                        <td class="px-6 py-4">
                            <span class="text-sm font-mono font-bold text-slate-700">{{ $surat->nomor_surat }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs border border-slate-200">
                                    {{ substr($surat->user->nama_lengkap ?? 'U', 0, 2) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-sm">{{ $surat->user->nama_lengkap ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $surat->user->nik ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-600">
                                {{ $surat->pengajuanIzin->jenisIzin->nama_izin ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $surat->created_at?->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $badgeColor = match ($surat->status_surat) {
                                    'disetujui' => 'green',
                                    'ditolak' => 'red',
                                    'menunggu_hrd' => 'blue',
                                    default => 'yellow'
                                };
                                $statusLabel = match ($surat->status_surat) {
                                    'menunggu_manajer' => 'Menunggu Manajer',
                                    'menunggu_hrd' => 'Menunggu HRD',
                                    'disetujui' => 'Disetujui',
                                    'ditolak' => 'Ditolak',
                                    default => $surat->status_surat
                                };
                            @endphp
                            <x-badge color="{{ $badgeColor }}">{{ $statusLabel }}</x-badge>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('surat-izin.show', $surat->id_surat) }}"
                                class="px-3 py-1.5 text-xs font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <x-empty-state colspan="6" message="Belum ada surat izin." />
                @endforelse
            </x-table>
        </div>

        <x-pagination :paginator="$suratList->appends(request()->query())" />
    </div>
@endsection
