@extends('layouts.app')

@section('title', 'Riwayat Tukar Shift')

@section('content')
    <div class="space-y-6">

        <div class="flex justify-between items-center sm:hidden mb-4">
            <h1 class="text-2xl font-bold text-slate-800">Riwayat Tukar Shift</h1>
        </div>

        <x-page-header title="Riwayat Tukar Shift" subtitle="Daftar log eksekusi penukaran jadwal kerja antar pegawai.">
            <div class="flex gap-2">
                <x-back-button href="{{ route('jadwal.index') }}" />
                <a href="{{ route('tukar-shift.create') }}"
                    class="px-5 py-2.5 bg-primary text-white text-sm font-semibold justify-center rounded-xl hover:bg-primary/90 transition shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Tukar Shift
                </a>
            </div>
        </x-page-header>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left font-semibold text-slate-600">Terjadi Pada</th>
                        <th scope="col" class="px-6 py-4 text-left font-semibold text-slate-600">Pegawai 1</th>
                        <th scope="col" class="px-6 py-4 text-left font-semibold text-slate-600">Pegawai 2</th>
                        <th scope="col" class="px-6 py-4 text-left font-semibold text-slate-600">Keterangan</th>
                        <th scope="col" class="px-6 py-4 text-left font-semibold text-slate-600">Dieksekusi Oleh</th>
                        <th scope="col" class="px-6 py-4 text-right font-semibold text-slate-600">Aksi</th>
                    </tr>
                </x-slot>
                    @forelse($riwayat as $log)
                        <tr class="hover:bg-slate-50 transition border-b border-slate-100 last:border-0">
                            <td class="px-6 py-4 align-middle">
                                <span class="text-slate-800 font-medium whitespace-nowrap">
                                    {{ $log->created_at->translatedFormat('d F Y, H:i') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 align-middle">
                                <div class="font-medium text-slate-800">{{ optional($log->user1)->nama_lengkap }}</div>
                                <div class="text-xs text-slate-500">{{ optional($log->jadwal1)->tanggal }}</div>
                                @if(optional($log->jadwal1)->shift)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-600 mt-1">
                                        {{ $log->jadwal1->shift->nama_shift }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 align-middle">
                                <div class="font-medium text-slate-800">{{ optional($log->user2)->nama_lengkap }}</div>
                                <div class="text-xs text-slate-500">{{ optional($log->jadwal2)->tanggal }}</div>
                                @if(optional($log->jadwal2)->shift)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-fuchsia-50 text-fuchsia-600 mt-1">
                                        {{ $log->jadwal2->shift->nama_shift }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 align-middle">
                                <span class="text-slate-600 line-clamp-2">{{ $log->keterangan ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 align-middle">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-200">
                                    {{ optional($log->execAdmin)->nama_lengkap }}
                                </span>
                            </td>
                            <td class="px-6 py-4 align-middle text-right">
                                @php
                                    $detailData = [
                                        'tanggal_eksekusi' => $log->created_at->translatedFormat('d F Y, H:i'),
                                        'admin' => optional($log->execAdmin)->nama_lengkap,
                                        'keterangan' => $log->keterangan ?? '-',
                                        'user1' => optional($log->user1)->nama_lengkap,
                                        'user1_nik' => optional($log->user1)->nik ?? '-',
                                        'tanggal1' => optional($log->jadwal1)->tanggal ? \Carbon\Carbon::parse($log->jadwal1->tanggal)->translatedFormat('d F Y') : '-',
                                        'shift1' => optional(optional($log->jadwal1)->shift)->nama_shift ?? '-',
                                        'jam1' => optional(optional($log->jadwal1)->shift)->jam_mulai ? substr(optional($log->jadwal1)->shift->jam_mulai, 0, 5) . ' - ' . substr(optional($log->jadwal1)->shift->jam_selesai, 0, 5) : '-',
                                        'user2' => optional($log->user2)->nama_lengkap,
                                        'user2_nik' => optional($log->user2)->nik ?? '-',
                                        'tanggal2' => optional($log->jadwal2)->tanggal ? \Carbon\Carbon::parse($log->jadwal2->tanggal)->translatedFormat('d F Y') : '-',
                                        'shift2' => optional(optional($log->jadwal2)->shift)->nama_shift ?? '-',
                                        'jam2' => optional(optional($log->jadwal2)->shift)->jam_mulai ? substr(optional($log->jadwal2)->shift->jam_mulai, 0, 5) . ' - ' . substr(optional($log->jadwal2)->shift->jam_selesai, 0, 5) : '-',
                                    ];
                                @endphp
                                <x-button type="button" variant="secondary" onclick="openDetailModal(this)" data-log="{{ json_encode($detailData) }}" class="px-3 py-1.5 text-xs inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Detail
                                </x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-slate-600 font-medium">Belum ada riwayat tukar shift.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
            </x-table>
        </div>
    </div>

    <x-modal name="detail-tukar-shift" title="Detail Tukar Shift">
        <div class="space-y-6">
            <div class="flex justify-between items-start pb-4 border-b border-slate-100">
                <div>
                    <p class="text-xs text-slate-500 mb-1">Dieksekusi Oleh</p>
                    <p class="font-bold text-slate-800 text-sm" id="detail-admin"></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-500 mb-1">Tanggal Eksekusi</p>
                    <p class="font-medium text-slate-800 bg-slate-50 border border-slate-100 px-2 py-1 rounded inline-block text-sm" id="detail-tgl-eksekusi"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative">
                
                <div class="hidden md:flex absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-10 h-10 bg-white border border-slate-200 rounded-full items-center justify-center text-slate-400 z-10 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                    <p class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-3">Pegawai 1</p>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                            <span id="detail-avatar-1"></span>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm" id="detail-user1"></p>
                            <p class="text-xs text-slate-500 font-mono" id="detail-nik1"></p>
                        </div>
                    </div>
                    <div class="space-y-2 bg-white rounded-lg p-3 border border-blue-50">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500">Tanggal</span>
                            <span class="text-xs font-semibold text-slate-800" id="detail-tanggal1"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500">Shift</span>
                            <span class="text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100 px-2 py-0.5 rounded" id="detail-shift1"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500">Jam Kerja</span>
                            <span class="text-xs font-mono text-slate-600" id="detail-jam1"></span>
                        </div>
                    </div>
                </div>

                <div class="bg-fuchsia-50 border border-fuchsia-100 rounded-xl p-4">
                    <p class="text-xs font-bold text-fuchsia-800 uppercase tracking-wider mb-3">Pegawai 2</p>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-fuchsia-100 text-fuchsia-600 flex items-center justify-center font-bold text-sm">
                            <span id="detail-avatar-2"></span>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm" id="detail-user2"></p>
                            <p class="text-xs text-slate-500 font-mono" id="detail-nik2"></p>
                        </div>
                    </div>
                    <div class="space-y-2 bg-white rounded-lg p-3 border border-fuchsia-50">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500">Tanggal</span>
                            <span class="text-xs font-semibold text-slate-800" id="detail-tanggal2"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500">Shift</span>
                            <span class="text-xs font-bold bg-fuchsia-50 text-fuchsia-700 border border-fuchsia-100 px-2 py-0.5 rounded" id="detail-shift2"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500">Jam Kerja</span>
                            <span class="text-xs font-mono text-slate-600" id="detail-jam2"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-sm font-semibold text-slate-700 mb-2">Keterangan / Alasan</p>
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm text-slate-700" id="detail-keterangan">
                </div>
            </div>
            
            <div class="flex justify-end pt-4 border-t border-slate-100 mt-2">
                <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', 'detail-tukar-shift')">Tutup</x-button>
            </div>
        </div>
    </x-modal>
@endsection

@section('script')
    <script>
        function openDetailModal(buttonElement) {
            try {
                const dataStr = buttonElement.getAttribute('data-log');
                const data = JSON.parse(dataStr);
                
                document.getElementById('detail-admin').textContent = data.admin || '-';
                document.getElementById('detail-tgl-eksekusi').textContent = data.tanggal_eksekusi || '-';
                document.getElementById('detail-keterangan').textContent = data.keterangan || '-';
                
                document.getElementById('detail-user1').textContent = data.user1 || '-';
                document.getElementById('detail-nik1').textContent = data.user1_nik || '-';
                document.getElementById('detail-avatar-1').textContent = data.user1 ? data.user1.substring(0, 2).toUpperCase() : '??';
                document.getElementById('detail-tanggal1').textContent = data.tanggal1 || '-';
                document.getElementById('detail-shift1').textContent = data.shift1 || '-';
                document.getElementById('detail-jam1').textContent = data.jam1 || '-';

                document.getElementById('detail-user2').textContent = data.user2 || '-';
                document.getElementById('detail-nik2').textContent = data.user2_nik || '-';
                document.getElementById('detail-avatar-2').textContent = data.user2 ? data.user2.substring(0, 2).toUpperCase() : '??';
                document.getElementById('detail-tanggal2').textContent = data.tanggal2 || '-';
                document.getElementById('detail-shift2').textContent = data.shift2 || '-';
                document.getElementById('detail-jam2').textContent = data.jam2 || '-';
                
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'detail-tukar-shift' }));
            } catch (e) {
                console.error('Error parsing detail data:', e);
            }
        }
    </script>
@endsection
