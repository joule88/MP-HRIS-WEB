@php use App\Enums\StatusVerifikasiWajah; @endphp
@extends('layouts.app')

@section('title', 'Verifikasi Wajah')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Verifikasi Wajah Karyawan" subtitle="Monitor dan kelola data Face ID seluruh karyawan." />

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('face.index', ['status' => 'pending']) }}"
                class="bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md transition-all {{ $status === 'pending' ? 'ring-2 ring-amber-400' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-slate-800">{{ $stats['pending'] }}</div>
                        <div class="text-xs font-semibold text-slate-500">Menunggu</div>
                    </div>
                </div>
            </a>
            <a href="{{ route('face.index', ['status' => 'approved']) }}"
                class="bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md transition-all {{ $status === 'approved' ? 'ring-2 ring-emerald-400' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-slate-800">{{ $stats['approved'] }}</div>
                        <div class="text-xs font-semibold text-slate-500">Terverifikasi</div>
                    </div>
                </div>
            </a>
            <a href="{{ route('face.index', ['status' => 'rejected']) }}"
                class="bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md transition-all {{ $status === 'rejected' ? 'ring-2 ring-red-400' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-slate-800">{{ $stats['rejected'] }}</div>
                        <div class="text-xs font-semibold text-slate-500">Ditolak</div>
                    </div>
                </div>
            </a>
            <a href="{{ route('face.index', ['status' => 'unregistered']) }}"
                class="bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md transition-all {{ $status === 'unregistered' ? 'ring-2 ring-slate-400' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-slate-800">{{ $stats['unregistered'] }}</div>
                        <div class="text-xs font-semibold text-slate-500">Belum Daftar</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

            <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                <form action="{{ route('face.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                    <div class="w-full md:w-48">
                        <x-select label="Status" name="status" onchange="this.form.submit()" class="!mb-0">
                            <option value="">Semua (Terdaftar)</option>
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Terverifikasi</option>
                            <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            <option value="unregistered" {{ $status === 'unregistered' ? 'selected' : '' }}>Belum Registrasi</option>
                        </x-select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <x-input type="text" name="search" label="Cari Pegawai" value="{{ request('search') }}" placeholder="Nama/NIK..."
                            class="!mb-0" />
                    </div>
                    <div>
                        <x-button type="submit" variant="secondary" class="h-[44px]">
                            Filter
                        </x-button>
                    </div>
                    @if($status || request('search'))
                    <div>
                        <a href="{{ route('face.index') }}" class="inline-flex items-center h-[44px] px-4 text-sm font-medium text-slate-500 hover:text-slate-800 transition">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Reset Filter
                        </a>
                    </div>
                    @endif
                </form>
                <div class="mt-4 flex justify-end">
                    <form id="reextract-form" action="{{ route('face.reextract_all') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                    <button id="reextractBtn" onclick="confirmAction(event, 'reextract-form', 'Sistem akan mengecek user yang belum punya frame, extract jika perlu, lalu retrain model global. Proses ini memakan waktu beberapa menit.', '#3b82f6', 'Ya, Lanjutkan!')"
                        class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Sync & Retrain All
                    </button>
                </div>

                <div id="trainingProgress" class="mt-3 hidden">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <svg class="w-5 h-5 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="trainingPhase" class="text-sm font-semibold text-blue-800">Memproses...</span>
                        </div>
                        <div class="w-full bg-blue-200 rounded-full h-2.5">
                            <div id="trainingBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: 0%"></div>
                        </div>
                        <p id="trainingMessage" class="text-xs text-blue-600 mt-1.5"></p>
                    </div>
                </div>
            </div>

            <x-table>
                <x-slot:header>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Karyawan</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Divisi / Jabatan</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Preview Foto</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Status</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Aksi</th>
                </x-slot:header>

                @forelse ($users as $user)
                    @php

                        $dw = $user->dataWajah;
                        $faceStatus = 'unregistered';
                        if ($dw) {
                            if ($dw->is_verified == StatusVerifikasiWajah::PENDING)   $faceStatus = 'pending';
                            elseif ($dw->is_verified == StatusVerifikasiWajah::APPROVED) $faceStatus = 'approved';
                            elseif ($dw->is_verified == StatusVerifikasiWajah::REJECTED)  $faceStatus = 'rejected';
                        }
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($user->foto)
                                    <img class="h-10 w-10 rounded-full object-cover mr-3"
                                        src="{{ asset('storage/' . $user->foto) }}" alt="">
                                @else
                                    <x-avatar :name="$user->nama_lengkap" class="mr-3" />
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-slate-900">{{ $user->nama_lengkap }}</div>
                                    <div class="text-xs text-slate-500">{{ $user->nik ?? '-' }} • {{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-slate-900">{{ $user->divisi->nama_divisi ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $user->jabatan->nama_jabatan ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($faceStatus !== 'unregistered')
                            <div class="flex items-center justify-center gap-2">
                                @if(isset($user->face_frames) && $user->face_frame_count > 0)
                                    @php
                                        // Ambil 4 frame representatif (atau kurang jika tidak sampai 4)
                                        $frameCount = $user->face_frame_count;
                                        $step = max(1, floor($frameCount / 4));
                                        $selectedFrames = [];
                                        
                                        for ($i = 0; $i < $frameCount; $i += $step) {
                                            if (count($selectedFrames) < 4) {
                                                // Extract "01" dari "face_datasets/1/frame_01.jpg"
                                                if (preg_match('/frame_(\d+)\.jpg$/', $user->face_frames[$i], $matches)) {
                                                    $selectedFrames[] = $matches[1];
                                                }
                                            }
                                        }
                                    @endphp
                                    
                                    @foreach($selectedFrames as $index => $frameIdx)
                                        <div class="relative group cursor-pointer" onclick="openFacePreview('{{ route('face.frame', [$user->id, $frameIdx]) }}', '{{ $user->nama_lengkap }}', 'Frame {{ $frameIdx }}')">
                                            <img src="{{ route('face.frame', [$user->id, $frameIdx]) }}"
                                                class="w-12 h-12 rounded-lg object-cover border-2 border-slate-200 group-hover:border-primary transition-colors shadow-sm">
                                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 text-[9px] font-bold bg-slate-800 text-white px-1.5 py-0.5 rounded-full leading-none whitespace-nowrap">Fr {{ $frameIdx }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-xs text-slate-400 italic flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        Memproses...
                                    </div>
                                @endif
                            </div>
                            @else
                                <div class="text-center text-xs text-slate-400 italic">Belum ada foto</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($faceStatus === 'pending')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                                    Menunggu Verifikasi
                                </span>
                            @elseif($faceStatus === 'approved')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Terverifikasi
                                </span>
                            @elseif($faceStatus === 'rejected')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    Ditolak
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">
                                    Belum Registrasi
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex justify-center gap-2">
                                @if($faceStatus === 'pending')
                                    <form id="approve-form-{{ $user->id }}" action="{{ route('face.approve', $user->id) }}"
                                        method="POST" class="hidden">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    <button
                                        onclick="confirmAction(event, 'approve-form-{{ $user->id }}', 'Apakah Anda yakin wajah ini sesuai dengan karyawan tersebut?', '#16a34a', 'Ya, Terima!')"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 transition-colors shadow-sm"
                                        title="Terima Wajah">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Terima
                                    </button>

                                    <form id="reject-form-{{ $user->id }}" action="{{ route('face.reject', $user->id) }}"
                                        method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button
                                        onclick="confirmAction(event, 'reject-form-{{ $user->id }}', 'Data wajah akan dihapus dan karyawan harus scan ulang. Lanjutkan?', '#dc2626', 'Ya, Tolak!')"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 transition-colors shadow-sm"
                                        title="Tolak / Reset">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Tolak
                                    </button>
                                @elseif($faceStatus === 'approved')
                                    <form id="reset-form-{{ $user->id }}" action="{{ route('face.reset', $user->id) }}"
                                        method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button
                                        onclick="confirmAction(event, 'reset-form-{{ $user->id }}', 'Data wajah akan dihapus dan karyawan harus melakukan registrasi ulang. Lanjutkan?', '#f59e0b', 'Ya, Reset!')"
                                        class="inline-flex items-center px-3 py-1.5 border border-amber-200 text-xs font-medium rounded-lg text-amber-700 bg-amber-50 hover:bg-amber-100 transition-colors shadow-sm"
                                        title="Reset Face ID">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Reset
                                    </button>
                                    <button onclick="openUploadModal({{ $user->id }}, '{{ addslashes($user->nama_lengkap) }}')"
                                        class="inline-flex items-center px-3 py-1.5 border border-blue-200 text-xs font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors shadow-sm"
                                        title="Upload Ulang Video Wajah">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        Re-Upload
                                    </button>
                                @else
                                    <button onclick="openUploadModal({{ $user->id }}, '{{ addslashes($user->nama_lengkap) }}')"
                                        class="inline-flex items-center px-3 py-1.5 border border-slate-200 text-xs font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-sm"
                                        title="Upload Video Wajah">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        Upload Video
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state colspan="5" message="Tidak ada data karyawan yang sesuai filter." />
                @endforelse
            </x-table>
        </div>
    </div>

    <div id="facePreviewOverlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm" onclick="closeFacePreview()">
        <div class="relative max-w-lg w-full mx-4" onclick="event.stopPropagation()">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm" id="previewNama">Nama</h3>
                        <p class="text-xs text-slate-500" id="previewPose">Pose</p>
                    </div>
                    <button onclick="closeFacePreview()" class="p-1.5 hover:bg-slate-100 rounded-lg transition">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-4 bg-slate-50">
                    <img id="previewImage" src="" class="w-full rounded-xl shadow-sm border border-slate-200">
                </div>
            </div>
        </div>
    </div>
    <div id="uploadVideoOverlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-500/50 backdrop-blur-sm" onclick="closeUploadModal()">
        <div class="relative max-w-md w-full mx-4" onclick="event.stopPropagation()">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-slate-800">Upload Video Wajah</h3>
                        <p class="text-sm text-slate-500 mt-0.5" id="uploadUserName">Nama Karyawan</p>
                    </div>
                    <button onclick="closeUploadModal()" class="p-1.5 hover:bg-slate-100 rounded-lg transition">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="uploadVideoForm" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">File Video</label>
                        <div id="dropZone" class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:border-primary transition-colors cursor-pointer">
                            <svg class="w-10 h-10 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="text-sm text-slate-600 font-medium">Klik atau drag & drop video di sini</p>
                            <p class="text-xs text-slate-400 mt-1">Format: MP4, MOV, AVI (maks. 50MB)</p>
                            <input type="file" name="video_wajah" id="videoFileInput" accept="video/mp4,video/quicktime,video/x-msvideo,.mp4,.mov,.avi" class="hidden" required>
                        </div>
                        <div id="fileInfo" class="hidden mt-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-slate-700" id="fileName"></p>
                                        <p class="text-xs text-slate-400" id="fileSize"></p>
                                    </div>
                                </div>
                                <button type="button" onclick="clearFile()" class="p-1 hover:bg-slate-200 rounded transition">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <p class="text-xs text-amber-700">Video akan diproses untuk ekstraksi frame wajah. Jika user sudah punya data wajah sebelumnya, data lama akan ditimpa.</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeUploadModal()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                            Batal
                        </button>
                        <button type="submit" id="uploadSubmitBtn"
                            class="px-4 py-2 text-sm font-semibold text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <span id="uploadBtnText">Upload & Proses</span>
                            <span id="uploadBtnLoading" class="hidden inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Mengupload...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function openFacePreview(src, nama, pose) {
            document.getElementById('previewImage').src = src;
            document.getElementById('previewNama').innerText = nama;
            document.getElementById('previewPose').innerText = 'Pose: ' + pose;
            const overlay = document.getElementById('facePreviewOverlay');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        }

        function closeFacePreview() {
            const overlay = document.getElementById('facePreviewOverlay');
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeFacePreview();
        });

        let pollingInterval = null;

        // Cek status saat halaman pertama kali dimuat
        document.addEventListener('DOMContentLoaded', function() {
            fetch('{{ route("face.training_status") }}')
                .then(r => r.json())
                .then(data => {
                    const phase = data.phase || 'idle';
                    if (phase === 'extracting' || phase === 'training') {
                        startPolling();
                    }
                })
                .catch(() => {});
        });

        document.getElementById('reextract-form').addEventListener('submit', function() {
            startPolling();
        });

        function startPolling() {
            const progressEl = document.getElementById('trainingProgress');
            const btn = document.getElementById('reextractBtn');
            progressEl.classList.remove('hidden');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');

            pollingInterval = setInterval(checkStatus, 3000);
        }

        function checkStatus() {
            fetch('{{ route("face.training_status") }}')
                .then(r => r.json())
                .then(data => {
                    const phase = data.phase || 'idle';
                    const message = data.message || '';
                    const current = data.current || 0;
                    const total = data.total || 1;

                    document.getElementById('trainingMessage').textContent = message;

                    if (phase === 'extracting') {
                        const pct = Math.round((current / total) * 70);
                        document.getElementById('trainingBar').style.width = pct + '%';
                        document.getElementById('trainingPhase').textContent = 'Extracting Frames...';
                    } else if (phase === 'training') {
                        document.getElementById('trainingBar').style.width = '85%';
                        document.getElementById('trainingPhase').textContent = 'Training SVM...';
                    } else if (phase === 'done') {
                        document.getElementById('trainingBar').style.width = '100%';
                        document.getElementById('trainingPhase').textContent = '✅ Selesai!';
                        document.getElementById('trainingProgress').querySelector('div').className =
                            'bg-emerald-50 border border-emerald-200 rounded-lg p-4';
                        document.getElementById('trainingPhase').className = 'text-sm font-semibold text-emerald-800';
                        document.getElementById('trainingMessage').className = 'text-xs text-emerald-600 mt-1.5';
                        document.getElementById('trainingBar').className = 'bg-emerald-600 h-2.5 rounded-full transition-all duration-500';
                        document.querySelector('#trainingProgress svg').classList.remove('animate-spin');
                        stopPolling();
                    } else if (phase === 'error') {
                        document.getElementById('trainingPhase').textContent = '❌ Gagal';
                        document.getElementById('trainingProgress').querySelector('div').className =
                            'bg-red-50 border border-red-200 rounded-lg p-4';
                        document.getElementById('trainingPhase').className = 'text-sm font-semibold text-red-800';
                        document.getElementById('trainingMessage').className = 'text-xs text-red-600 mt-1.5';
                        stopPolling();
                    }
                })
                .catch(() => {});
        }

        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
            const btn = document.getElementById('reextractBtn');
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        function openUploadModal(userId, userName) {
            const form = document.getElementById('uploadVideoForm');
            form.action = `/face-approval/${userId}/upload-video`;
            document.getElementById('uploadUserName').textContent = userName;
            clearFile();
            const overlay = document.getElementById('uploadVideoOverlay');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        }

        function closeUploadModal() {
            const overlay = document.getElementById('uploadVideoOverlay');
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }

        // Drop zone & file input
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('videoFileInput');

        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-primary', 'bg-primary/5');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-primary', 'bg-primary/5');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-primary', 'bg-primary/5');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                showFileInfo(e.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                showFileInfo(e.target.files[0]);
            }
        });

        function showFileInfo(file) {
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = sizeMB + ' MB';
            document.getElementById('fileInfo').classList.remove('hidden');
            dropZone.classList.add('hidden');
            document.getElementById('uploadSubmitBtn').disabled = false;
        }

        function clearFile() {
            fileInput.value = '';
            document.getElementById('fileInfo').classList.add('hidden');
            dropZone.classList.remove('hidden');
            document.getElementById('uploadSubmitBtn').disabled = true;
        }

        document.getElementById('uploadVideoForm').addEventListener('submit', function() {
            const btn = document.getElementById('uploadSubmitBtn');
            btn.disabled = true;
            document.getElementById('uploadBtnText').classList.add('hidden');
            document.getElementById('uploadBtnLoading').classList.remove('hidden');
        });
    </script>
@endsection
