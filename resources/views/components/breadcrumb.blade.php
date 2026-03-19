@php
    $segments = request()->segments();
    $url = '';

    $titles = [
        'dashboard' => 'Dashboard',
        'presensi' => 'Monitoring Presensi',
        'izin' => 'Manajemen Izin',
        'laporan' => 'Laporan Rekap',
        'pegawai' => 'Data Karyawan',
        'jadwal' => 'Penjadwalan',
        'face-approval' => 'Verifikasi Wajah',
        'lembur' => 'Lembur',
        'penggunaan-poin' => 'Persetujuan Poin',
        'pengumuman' => 'Pengumuman',
        'kantor' => 'Kantor',
        'divisi' => 'Divisi',
        'jabatan' => 'Jabatan',
        'role' => 'Role',
        'shift' => 'Shift Kerja'
    ];
@endphp

@if(count($segments) > 0 && $segments[0] !== 'dashboard')
    <nav class="flex text-sm text-slate-500 mb-6 font-medium print:hidden" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center hover:text-primary transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                        </path>
                    </svg>
                    Dashboard
                </a>
            </li>
            @foreach($segments as $key => $segment)
                @php
                    $url .= '/' . $segment;
                    $isLast = $key == count($segments) - 1;
                    $title = $titles[$segment] ?? ucfirst(str_replace('-', ' ', $segment));

                    if (is_numeric($segment))
                        continue;

                    if ($segment == 'edit' || $segment == 'create') {
                        $title = $segment == 'edit' ? 'Edit' : 'Tambah Baru';
                    }
                @endphp
                <li>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        @if($isLast)
                            <span class="ml-1 md:ml-2 text-slate-800 font-bold" aria-current="page">{{ $title }}</span>
                        @else
                            <a href="{{ url($url) }}" class="ml-1 md:ml-2 hover:text-primary transition-colors">{{ $title }}</a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </nav>
@endif
