@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-header
        title="{{ Carbon\Carbon::now('Asia/Jakarta')->hour < 12 ? 'Selamat Pagi' : (Carbon\Carbon::now('Asia/Jakarta')->hour < 15 ? 'Selamat Siang' : 'Selamat Sore') }}, {{ Auth::user()->nama_lengkap }}! 👋"
        subtitle="{{ Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y') }}" />

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 md:gap-6 mb-8">
        
        <x-stat-card label="Total Pegawai" value="{{ $totalPegawai }}" color="indigo">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
        </x-stat-card>
        <x-stat-card label="Hadir Hari Ini" value="{{ $rekap['hadir'] }}" color="emerald">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </x-stat-card>

        <x-stat-card label="Sakit" value="{{ $rekap['sakit'] }}" color="blue">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </x-stat-card>

        <x-stat-card label="Izin / Cuti" value="{{ $rekap['izin'] }}" color="orange">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </x-stat-card>

        <x-stat-card label="Terlambat" value="{{ $rekap['terlambat'] }}" color="red">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </x-stat-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-bold text-slate-800 mb-4">Tren Kehadiran (7 Hari Terakhir)</h3>
            <div class="relative h-64">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-orange-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        Menunggu Persetujuan Izin/Cuti
                    </h3>
                    <a href="{{ route('izin.index') }}" class="text-xs text-primary hover:underline font-medium">Lihat
                        Semua</a>
                </div>
                <div class="p-0">
                    @forelse($pengajuanPending['izin'] as $izin)
                        <div
                            class="flex items-center justify-between p-4 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-sm">
                                    {{ substr($izin->user->nama_lengkap ?? 'U', 0, 2) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-800">{{ $izin->user->nama_lengkap }}</h4>
                                    <p class="text-xs text-slate-500">
                                        {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M') }} -
                                        {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d M Y') }}
                                        <span class="mx-1">•</span> {{ $izin->jenisIzin->nama_izin ?? 'Izin' }}
                                    </p>
                                </div>
                            </div>
                            <span
                                class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-amber-100 text-amber-700">Pending</span>
                        </div>
                    @empty
                        <div class="text-center text-slate-400 py-6 text-sm">
                            Tidak ada pengajuan izin/cuti yang pending.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden mt-6">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-teal-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Menunggu Persetujuan Surat Izin
                    </h3>
                    <a href="{{ route('surat-izin.index') }}" class="text-xs text-primary hover:underline font-medium">Lihat Semua</a>
                </div>
                <div class="p-0">
                    @forelse($pengajuanPending['surat_izin'] ?? [] as $surat)
                        <div class="flex items-center justify-between p-4 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center font-bold text-sm">
                                    {{ substr($surat->user->nama_lengkap ?? 'U', 0, 2) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-800">{{ $surat->user->nama_lengkap }}</h4>
                                    <p class="text-xs text-slate-500">
                                        Tanggal: {{ \Carbon\Carbon::parse($surat->tanggal)->translatedFormat('d F Y') }}
                                        <span class="mx-1">•</span> Keperluan: <span class="truncate max-w-[150px] inline-block align-bottom" title="{{ $surat->keperluan }}">{{ $surat->keperluan }}</span>
                                    </p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-amber-100 text-amber-700">Pending</span>
                        </div>
                    @empty
                        <div class="text-center text-slate-400 py-6 text-sm">
                            Tidak ada pengajuan surat izin yang pending.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-blue-50/50">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Lembur
                        </h3>
                        <a href="{{ route('lembur.index') }}"
                            class="text-xs text-primary hover:underline font-medium">Lihat</a>
                    </div>
                    <div class="p-0">
                        @forelse($pengajuanPending['lembur'] as $lembur)
                            <div class="flex flex-col p-4 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition">
                                <h4 class="text-sm font-semibold text-slate-800">{{ $lembur->user->nama_lengkap }}</h4>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ \Carbon\Carbon::parse($lembur->tanggal_lembur)->format('d M Y') }}
                                    ({{ $lembur->durasi_menit }} menit)
                                </p>
                            </div>
                        @empty
                            <div class="text-center text-slate-400 py-6 text-sm">Tidak ada lembur pending.</div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-purple-50/50">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                            Dispensasi Poin
                        </h3>
                        <a href="{{ route('penggunaan-poin.index') }}"
                            class="text-xs text-primary hover:underline font-medium">Lihat</a>
                    </div>
                    <div class="p-0">
                        @forelse($pengajuanPending['poin'] as $poin)
                            <div class="flex flex-col p-4 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition">
                                <h4 class="text-sm font-semibold text-slate-800">{{ $poin->user->nama_lengkap }}</h4>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ \Carbon\Carbon::parse($poin->tanggal_penggunaan)->format('d M Y') }}
                                    (-{{ $poin->jumlah_poin }} poin)
                                </p>
                            </div>
                        @empty
                            <div class="text-center text-slate-400 py-6 text-sm">Tidak ada dispensasi poin.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            @if($pengajuanPending['presensi']->count() > 0)
                <div class="bg-red-50 rounded-xl shadow-sm border border-red-100 overflow-hidden">
                    <div class="p-4 border-b border-red-100 flex justify-between items-center">
                        <h3 class="font-bold text-red-700 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                            Butuh Validasi Absen (Luar Radius)
                        </h3>
                        <a href="{{ route('presensi.index') }}" class="text-xs text-red-600 hover:underline font-medium">Proses
                            Sekarang</a>
                    </div>
                    <div class="p-0">
                        @foreach($pengajuanPending['presensi'] as $presensi)
                            <div class="flex items-center justify-between p-4 border-b border-red-100/50 last:border-0">
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-800">{{ $presensi->user->nama_lengkap }}</h4>
                                    <p class="text-xs text-red-600 mt-1">
                                        {{ \Carbon\Carbon::parse($presensi->tanggal)->format('d M Y') }} •
                                        {{ \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Jumlah Hadir',
                    data: @json($chartData),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
@endsection
