<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 w-72 bg-white border-r border-slate-200 flex flex-col h-full transition-transform duration-300 font-sans z-[100] shadow-xl lg:shadow-sm lg:static lg:flex-shrink-0">

    <div class="flex items-center justify-center h-28 border-b border-slate-100 bg-white">
        <div class="text-center">
            <span class="block text-3xl font-extrabold tracking-tight text-[#130F26]">
                MPG<span class="text-blue-600">HRIS</span>
            </span>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1 block">Enterprise System</span>
        </div>
    </div>

    @php
        $authUser     = Auth::user();
        $roleNama    = strtolower($authUser->roles->first()?->nama_role ?? '');
        $isHrd        = $roleNama === 'hrd';
        $isManager    = $roleNama === 'manager';
        $isSupervisor = $roleNama === 'supervisor';
        $isStaff      = $roleNama === 'staff';
        $isGlobalAdmin = $authUser->isGlobalAdmin();
        $userKantor   = $authUser->id_kantor;

        // Alias untuk kondisi gabungan
        $canManage        = $isHrd;
        $canViewPegawai   = $isHrd || $isManager;
        $canViewPresensi  = $isHrd || $isManager || $isSupervisor;
        $canApproveIzin   = $isHrd || $isManager;
        $canViewLaporan   = $isHrd || $isManager || $isSupervisor;
        $canViewLaporanIzin = $isHrd || $isManager;
        $canViewJadwal    = $isHrd || $isManager || $isSupervisor;
        $canAccessWeb     = $isHrd || $isManager || $isSupervisor;
    @endphp

    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto no-scrollbar">

        {{-- ── Dashboard ── --}}
        <div class="space-y-1">
            <a href="{{ route('dashboard') }}"
                class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('dashboard') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                    </path>
                </svg>
                <span class="font-semibold text-sm">Dashboard</span>
            </a>
        </div>

        {{-- ════════════════════════════════ SEKSI: MANAJEMEN ════════════════════════════════ --}}
        @if($canManage || $canViewPegawai)
        <div class="pt-6 pb-3 px-4 flex items-center gap-3">
            <div class="h-px bg-slate-200 flex-1"></div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Manajemen</span>
            <div class="h-px bg-slate-200 flex-1"></div>
        </div>

        <div class="space-y-1">

            {{-- Verifikasi Wajah – HRD only --}}
            @if($isHrd)
            <a href="{{ route('face.index') }}"
                class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('face.*') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('face.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-semibold text-sm">Verifikasi Wajah</span>
                @php $pendingFace = \App\Models\User::where('is_face_registered', 0)->whereHas('dataWajah')->count(); @endphp
                @if($pendingFace > 0)
                    <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingFace }}</span>
                @endif
            </a>
            @endif

            {{-- Data Karyawan – HRD & Manager --}}
            @if($canViewPegawai)
            <a href="{{ route('pegawai.index') }}"
                class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('pegawai.*') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('pegawai.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
                <span class="font-semibold text-sm">Data Karyawan</span>
            </a>
            @endif

            {{-- Penjadwalan – HRD, Manager, Supervisor --}}
            @if($canViewJadwal)
            <a href="{{ route('jadwal.index') }}"
                class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('jadwal.*') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('jadwal.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="font-semibold text-sm">Penjadwalan</span>
            </a>

            {{-- Tukar Shift – HRD, Manager, Supervisor --}}
            <a href="{{ route('tukar-shift.index') }}"
                class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('tukar-shift.*') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('tukar-shift.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"></path>
                </svg>
                <span class="font-semibold text-sm">Tukar Shift</span>
            </a>
            @endif

        </div>
        @endif

        {{-- ════════════════════════════════ SEKSI: ABSENSI ════════════════════════════════ --}}
        @if($canViewLaporan || $canViewPresensi)
        <div class="pt-6 pb-3 px-4 flex items-center gap-3">
            <div class="h-px bg-slate-200 flex-1"></div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Absensi</span>
            <div class="h-px bg-slate-200 flex-1"></div>
        </div>

        <div class="space-y-1">

            {{-- Laporan Kehadiran – HRD, Manager, Supervisor --}}
            @if($canViewLaporan)
            <a href="{{ route('laporan.index') }}"
                class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('laporan.index') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('laporan.index') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="font-semibold text-sm">Laporan Kehadiran</span>
            </a>
            @endif

            {{-- Laporan Lembur – HRD only --}}
            @if($isHrd)
            <a href="{{ route('laporan-lembur.index') }}"
                class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('laporan-lembur.*') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('laporan-lembur.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-semibold text-sm">Laporan Lembur</span>
            </a>
            @endif

            {{-- Laporan Izin/Cuti – HRD & Manager --}}
            @if($canViewLaporanIzin)
            <a href="{{ route('laporan.izin') }}"
                class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('laporan.izin') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('laporan.izin') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="font-semibold text-sm">Laporan Izin/Cuti</span>
            </a>
            @endif

            {{-- Monitoring Presensi – HRD, Manager, Supervisor --}}
            @if($canViewPresensi)
            <a href="{{ route('presensi.index') }}"
                class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('presensi.*') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('presensi.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="flex-1 font-semibold text-sm">Monitoring Presensi</span>
                @if(isset($pendingPresensi) && $pendingPresensi > 0)
                    <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingPresensi }}</span>
                @endif
            </a>
            @endif

            {{-- Manajemen Izin – HRD & Manager --}}
            @if($canApproveIzin)
            <a href="{{ route('izin.index') }}"
                class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('izin.*') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('izin.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <span class="flex-1 font-semibold text-sm">Manajemen Izin</span>
                @php
                    $izinQuery = \App\Models\PengajuanIzin::where('id_status', \App\Enums\StatusPengajuan::PENDING);
                    if (!$isGlobalAdmin) { $izinQuery->whereHas('user', fn($q) => $q->where('id_kantor', $userKantor)); }
                    $pendingIzin = $izinQuery->count();
                @endphp
                @if($pendingIzin > 0)
                    <span class="ml-auto bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingIzin }}</span>
                @endif
            </a>

            {{-- Surat Izin – HRD & Manager --}}
            <a href="{{ route('surat-izin.index') }}"
                class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('surat-izin.*') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('surat-izin.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                    </path>
                </svg>
                <span class="flex-1 font-semibold text-sm">Surat Izin</span>
                @php
                    $suratQuery = \App\Models\SuratIzin::whereIn('status_surat', ['menunggu_manajer', 'menunggu_hrd']);
                    if (!$isGlobalAdmin) { $suratQuery->whereHas('user', fn($q) => $q->where('id_kantor', $userKantor)); }
                    $pendingSurat = $suratQuery->count();
                @endphp
                @if($pendingSurat > 0)
                    <span class="ml-auto bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingSurat }}</span>
                @endif
            </a>
            @endif

            {{-- Lembur & Poin – HRD only --}}
            @if($isHrd)
            <a href="{{ route('lembur.index') }}"
                class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('lembur.*') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('lembur.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
                <span class="flex-1 font-semibold text-sm">Lembur & Poin</span>
                @php
                    $lemburQuery = \App\Models\Lembur::where('id_status', \App\Enums\StatusPengajuan::PENDING);
                    if (!$isGlobalAdmin) { $lemburQuery->whereHas('user', fn($q) => $q->where('id_kantor', $userKantor)); }
                    $pendingLembur = $lemburQuery->count();
                @endphp
                @if($pendingLembur > 0)
                    <span class="ml-auto bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingLembur }}</span>
                @endif
            </a>

            <a href="{{ route('penggunaan-poin.index') }}"
                class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('penggunaan-poin.*') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('penggunaan-poin.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                    </path>
                </svg>
                <span class="flex-1 font-semibold text-sm">Persetujuan Poin</span>
                @php
                    $poinQuery = \App\Models\PenggunaanPoin::where('id_status', \App\Enums\StatusPengajuan::PENDING);
                    if (!$isGlobalAdmin) { $poinQuery->whereHas('user', fn($q) => $q->where('id_kantor', $userKantor)); }
                    $pendingPoin = $poinQuery->count();
                @endphp
                @if($pendingPoin > 0)
                    <span class="ml-auto bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingPoin }}</span>
                @endif
            </a>

            {{-- Pengumuman – HRD only --}}
            <a href="{{ route('pengumuman.index') }}"
                class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('pengumuman.*') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('pengumuman.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z">
                    </path>
                </svg>
                <span class="font-semibold text-sm">Pengumuman</span>
            </a>
            @endif

        </div>
        @endif

        {{-- ════════════════════════════════ SEKSI: DATA MASTER (HRD only) ════════════════════════════════ --}}
        @if($isHrd)
        <div x-data="{ open: {{ request()->routeIs('divisi.*', 'jabatan.*', 'kantor.*', 'role.*', 'shift.*', 'hari-libur.*', 'cuti.*') ? 'true' : 'false' }} }"
            class="space-y-1">

            <button @click="open = !open"
                class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 group text-slate-500 hover:bg-slate-50 hover:text-slate-900">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-slate-600" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4">
                        </path>
                    </svg>
                    <span class="font-semibold text-sm">Data Master</span>
                </div>
                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200 text-slate-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                class="pl-11 pr-2 space-y-1 pt-1">

                <a href="{{ route('kantor.index') }}"
                    class="block py-2.5 px-4 text-sm rounded-lg transition-all {{ request()->routeIs('kantor.*') ? 'bg-slate-100 text-[#130F26] font-bold border-l-4 border-[#130F26]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                    Data Kantor
                </a>

                <a href="{{ route('divisi.index') }}"
                    class="block py-2.5 px-4 text-sm rounded-lg transition-all {{ request()->routeIs('divisi.*') ? 'bg-slate-100 text-[#130F26] font-bold border-l-4 border-[#130F26]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                    Data Divisi
                </a>

                <a href="{{ route('jabatan.index') }}"
                    class="block py-2.5 px-4 text-sm rounded-lg transition-all {{ request()->routeIs('jabatan.*') ? 'bg-slate-100 text-[#130F26] font-bold border-l-4 border-[#130F26]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                    Data Jabatan
                </a>

                <a href="{{ route('role.index') }}"
                    class="block py-2.5 px-4 text-sm rounded-lg transition-all {{ request()->routeIs('role.*') ? 'bg-slate-100 text-[#130F26] font-bold border-l-4 border-[#130F26]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                    Data Role
                </a>

                <a href="{{ route('shift.index') }}"
                    class="block py-2.5 px-4 text-sm rounded-lg transition-all {{ request()->routeIs('shift.*') ? 'bg-slate-100 text-[#130F26] font-bold border-l-4 border-[#130F26]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                    Shift Kerja
                </a>

                <a href="{{ route('hari-libur.index') }}"
                    class="block py-2.5 px-4 text-sm rounded-lg transition-all {{ request()->routeIs('hari-libur.*') ? 'bg-slate-100 text-[#130F26] font-bold border-l-4 border-[#130F26]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                    Hari Libur
                </a>

                <a href="{{ route('cuti.index') }}"
                    class="block py-2.5 px-4 text-sm rounded-lg transition-all {{ request()->routeIs('cuti.*') ? 'bg-slate-100 text-[#130F26] font-bold border-l-4 border-[#130F26]' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                    Sisa Cuti Pegawai
                </a>
            </div>
        </div>
        @endif

    </nav>

    {{-- Footer: Pengaturan Akun & Logout --}}
    <div class="px-8 py-4 mt-auto bg-slate-50/50">
        <a href="{{ route('profile.edit') }}"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('profile.edit') ? 'bg-[#130F26] text-white shadow-lg shadow-[#130F26]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('profile.edit') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }} group-hover:scale-110 transition-transform duration-300"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                </path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span class="font-semibold text-sm">Pengaturan Akun</span>
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                class="flex w-full items-center px-4 py-3 rounded-xl text-red-500 hover:bg-red-50 hover:text-red-600 transition-all duration-200 group">
                <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                </svg>
                <span class="font-semibold text-sm">Logout</span>
            </button>
        </form>
    </div>
</aside>

<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
