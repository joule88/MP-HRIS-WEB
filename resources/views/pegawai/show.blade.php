@extends('layouts.app')

@section('title', 'Detail Pegawai')

@section('content')
    <div class="space-y-6">

        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Profil Pegawai</h2>
                <p class="text-slate-500 text-sm">Informasi detail, jabatan, dan catatan waktu pegawai.</p>
            </div>
            <x-back-button href="{{ route('pegawai.index') }}" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="h-32 bg-gradient-to-br from-[#130F26] to-[#2B2545] relative">
                        <div class="absolute -bottom-12 left-1/2 transform -translate-x-1/2">
                            @if($pegawai->foto)
                                <img src="{{ asset('storage/' . $pegawai->foto) }}"
                                    class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md">
                            @else
                                <div
                                    class="w-24 h-24 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 border-4 border-white shadow-md">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="pt-16 pb-6 px-6 text-center">
                        <h3 class="text-lg font-bold text-slate-800">{{ $pegawai->nama_lengkap }}</h3>
                        <p class="text-sm font-medium text-slate-500">{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</p>

                        <div class="mt-4 flex justify-center gap-2">
                            <x-badge color="{{ $pegawai->status_aktif ? 'green' : 'gray' }}">
                                {{ $pegawai->status_aktif ? 'Aktif' : 'Non-Aktif' }}
                            </x-badge>
                            <x-badge color="{{ $pegawai->is_face_registered ? 'blue' : 'orange' }}">
                                {{ $pegawai->is_face_registered ? 'Wajah Terdaftar' : 'Belum Ada Wajah' }}
                            </x-badge>
                        </div>

                        <hr class="my-6 border-slate-100">

                        <div class="space-y-3 text-sm text-left">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2">
                                    </path>
                                </svg>
                                <span class="text-slate-600 font-mono">{{ $pegawai->nik ?? 'NIK belum diatur' }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <span class="text-slate-600">{{ $pegawai->email }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                    </path>
                                </svg>
                                <span class="text-slate-600">{{ $pegawai->no_telp ?? '-' }}</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-slate-400 mt-0.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                </svg>
                                <span class="text-slate-600">{{ $pegawai->alamat ?? 'Alamat belum diatur' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Departemen</h4>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-slate-400 mb-1">Divisi</p>
                            <p class="text-sm font-medium text-slate-800">{{ $pegawai->divisi->nama_divisi ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 mb-1">Penempatan Kantor</p>
                            <p class="text-sm font-medium text-slate-800">{{ $pegawai->kantor->nama_kantor ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 mb-1">Tanggal Bergabung</p>
                            <p class="text-sm font-medium text-slate-800">
                                {{ \Carbon\Carbon::parse($pegawai->tgl_bergabung)->translatedFormat('d F Y') }}</p>
                        </div>
                    </div>
                </div>

                @if(auth()->user()->roles->contains('nama_role', 'hrd') || auth()->user()->isGlobalAdmin())
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Aksi Cepat</h4>
                    <div class="space-y-3">
                        <button type="button"
                            onclick="confirmAction(event, 'reset-password-form', 'Password akan direset ke default (Mpg123!). Pegawai harus login ulang setelah reset.', '#f59e0b', 'Ya, Reset Password')"
                            class="w-full flex items-center gap-3 px-4 py-3 bg-amber-50 text-amber-700 border border-amber-200 rounded-xl hover:bg-amber-100 transition text-sm font-semibold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                            Reset Password
                        </button>
                        <form id="reset-password-form" action="{{ route('pegawai.reset-password', $pegawai->id) }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    </div>
                </div>
                @endif
            </div>

            <div class="lg:col-span-2 space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div
                        class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl shadow-sm border border-emerald-100 p-6 flex flex-col justify-between relative overflow-hidden">
                        <div class="relative z-10">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-emerald-800 font-bold mb-1">Sisa Cuti Tahunan</h4>
                                    <p class="text-xs text-emerald-600 font-medium tracking-wide">Periode {{ date('Y') }}
                                    </p>
                                </div>
                                <div
                                    class="w-10 h-10 bg-white/60 rounded-full flex items-center justify-center text-emerald-600 shadow-sm border border-emerald-100/50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-6 flex items-baseline gap-2 text-emerald-900">
                                <span class="text-4xl font-black tracking-tight">{{ $pegawai->sisa_cuti ?? 0 }}</span>
                                <span class="text-sm font-bold opacity-80">Hari</span>
                            </div>
                        </div>
                        <svg class="absolute -bottom-8 -right-8 w-32 h-32 text-emerald-500/10 transform rotate-12"
                            fill="currentColor" viewBox="0 0 100 100">
                            <path
                                d="M50 0 C22.4 0 0 22.4 0 50 s22.4 50 50 50 s50-22.4 50-50 S77.6 0 50 0z M50 90 C27.9 90 10 72.1 10 50 S27.9 10 50 10 s40 17.9 40 40 S72.1 90 50 90z">
                            </path>
                        </svg>
                    </div>

                    <div
                        class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-2xl shadow-sm border border-indigo-100 p-6 flex flex-col justify-between relative overflow-hidden">
                        <div class="relative z-10">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-indigo-800 font-bold mb-1">Saldo Poin Lembur</h4>
                                    <p class="text-xs text-indigo-600 font-medium tracking-wide">Dapat digunakan absen
                                        telat/pulang</p>
                                </div>
                                <div
                                    class="w-10 h-10 bg-white/60 rounded-full flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-6 flex items-baseline gap-2 text-indigo-900">
                                <span class="text-4xl font-black tracking-tight">{{ $sisaPoin }}</span>
                                <span class="text-sm font-bold opacity-80">Poin</span>
                            </div>
                        </div>
                        <svg class="absolute -bottom-8 -right-8 w-32 h-32 text-indigo-500/10 transform rotate-12"
                            fill="currentColor" viewBox="0 0 100 100">
                            <path
                                d="M50 0 C22.4 0 0 22.4 0 50 s22.4 50 50 50 s50-22.4 50-50 S77.6 0 50 0z M50 90 C27.9 90 10 72.1 10 50 S27.9 10 50 10 s40 17.9 40 40 S72.1 90 50 90z">
                            </path>
                        </svg>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="font-bold text-slate-800">Riwayat Mutasi Poin</h3>
                    </div>
                    <div class="p-0">
                        <x-table>
                            <x-slot:header>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">
                                    Tanggal</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">
                                    Keterangan</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">
                                    Jumlah</th>
                            </x-slot:header>

                            @forelse($historyPoin as $history)
                                <tr class="hover:bg-slate-50 transition border-b border-slate-50 last:border-b-0">
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ \Carbon\Carbon::parse($history->tanggal)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-slate-800">
                                        {{ $history->keterangan ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        @if($history->tipe == 'penambahan')
                                            <span
                                                class="inline-flex py-1 px-3 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                                +{{ $history->jumlah }} Poin
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex py-1 px-3 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                                -{{ $history->jumlah }} Poin
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state colspan="3" message="Tidak ada mutasi poin." />
                            @endforelse
                        </x-table>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mt-6">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="font-bold text-slate-800">Riwayat Presensi (30 Hari Terakhir)</h3>
                        <a href="{{ route('presensi.index', ['search' => $pegawai->nik]) }}" class="text-xs font-semibold text-primary hover:text-blue-800 transition-colors">Lihat Semua &rarr;</a>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <x-table>
                            <x-slot:header>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Tanggal</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Jadwal / Shift</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Jam Kerja</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                            </x-slot:header>

                            @forelse($riwayatPresensi as $presensi)
                                <tr class="hover:bg-slate-50 transition border-b border-slate-50 last:border-b-0">
                                    <td class="px-6 py-4 text-sm font-medium text-slate-800 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($presensi->tanggal)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600 text-center whitespace-nowrap">
                                        {{ $presensi->jadwal?->shift?->nama_shift ?? 'Shift Default' }}<br>
                                        <span class="text-xs text-slate-400">{{ substr($presensi->jadwal?->shift?->jam_mulai ?? '08:00', 0, 5) }} - {{ substr($presensi->jadwal?->shift?->jam_selesai ?? '17:00', 0, 5) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="flex flex-col text-sm items-center justify-center">
                                            <span class="font-mono font-bold {{ $presensi->jam_masuk > ($presensi->jadwal?->shift?->jam_mulai ?? '08:00') ? 'text-red-600' : 'text-emerald-600' }}">
                                                {{ $presensi->jam_masuk ? \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i') : '--:--' }}
                                            </span>
                                            <span class="text-xs text-slate-400 font-mono mt-0.5">s/d</span>
                                            <span class="font-mono font-bold">
                                                {{ $presensi->jam_pulang ? \Carbon\Carbon::parse($presensi->jam_pulang)->format('H:i') : '--:--' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <x-badge color="{{ $presensi->status->nama_status == 'Hadir' ? 'green' : ($presensi->status->nama_status == 'Terlambat' ? 'yellow' : 'red') }}">
                                            {{ $presensi->status->nama_status ?? 'Unknown' }}
                                        </x-badge>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state colspan="4" message="Tidak ada data presensi dalam 30 hari terakhir." />
                            @endforelse
                        </x-table>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mt-6">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="font-bold text-slate-800">Riwayat Izin & Cuti (30 Hari Terakhir)</h3>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <x-table>
                            <x-slot:header>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Tanggal</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Kategori</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Alasan</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                            </x-slot:header>

                            @forelse($riwayatIzin as $izin)
                                <tr class="hover:bg-slate-50 transition border-b border-slate-50 last:border-b-0">
                                    <td class="px-6 py-4 text-sm font-medium text-slate-800 whitespace-nowrap">
                                        @if($izin->tanggal_mulai == $izin->tanggal_selesai)
                                            {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->translatedFormat('d M Y') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->translatedFormat('d M Y') }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-800 font-semibold whitespace-nowrap">
                                        {{ $izin->jenisIzin->nama_izin ?? '-' }}
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
                                <x-empty-state colspan="4" message="Tidak ada pengajuan izin dalam 30 hari terakhir." />
                            @endforelse
                        </x-table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
