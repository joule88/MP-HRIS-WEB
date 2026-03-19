@extends('layouts.app')

@section('title', 'Verifikasi Wajah')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Verifikasi Wajah Karyawan" subtitle="Persetujuan pendaftaran Face ID karyawan baru" />

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <x-table>
                <x-slot:header>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Karyawan</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Divisi / Jabatan</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Preview Foto</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Status Saat Ini</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Aksi</th>
                </x-slot:header>

                @forelse ($users as $user)
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
                                    <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-slate-900">{{ $user->divisi->nama_divisi ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $user->jabatan->nama_jabatan ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                @php
                                    $poses = ['depan' => 'Depan', 'kanan' => 'Kanan', 'kiri' => 'Kiri', 'bawah' => 'Bawah'];
                                @endphp
                                @foreach($poses as $poseKey => $poseLabel)
                                    @if(isset($user->face_photos[$poseKey]))
                                        <div class="relative group cursor-pointer" onclick="openFacePreview('{{ route('face.photo', [$user->id, $poseKey]) }}', '{{ $user->nama_lengkap }}', '{{ $poseLabel }}')">
                                            <img src="{{ route('face.photo', [$user->id, $poseKey]) }}"
                                                class="w-12 h-12 rounded-lg object-cover border-2 border-slate-200 group-hover:border-primary transition-colors shadow-sm">
                                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 text-[9px] font-bold bg-slate-800 text-white px-1.5 py-0.5 rounded-full leading-none whitespace-nowrap">{{ $poseLabel }}</span>
                                        </div>
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center relative">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 text-[9px] font-bold bg-slate-400 text-white px-1.5 py-0.5 rounded-full leading-none whitespace-nowrap">{{ $poseLabel }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Menunggu Verifikasi
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex justify-center gap-2">
                                <form id="approve-form-{{ $user->id }}" action="{{ route('face.approve', $user->id) }}"
                                    method="POST" class="hidden">
                                    @csrf
                                    @method('PUT')
                                </form>
                                <button
                                    onclick="confirmAction(event, 'approve-form-{{ $user->id }}', 'Apakah Anda yakin wajah ini sesuai dengan karyawan tersebut?', '#16a34a', 'Ya, Terima!')"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors shadow-sm"
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
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors shadow-sm"
                                    title="Tolak / Reset">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Tolak
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state colspan="5" message="Tidak ada permintaan verifikasi wajah baru" />
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
    </script>
@endsection
