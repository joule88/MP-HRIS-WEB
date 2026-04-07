@extends('layouts.app')

@section('title', 'Manajemen Izin & Cuti')

@section('content')
    <div class="space-y-6">

        <x-page-header title="Manajemen Pengajuan Izin" subtitle="Kelola izin sakit, cuti, dan keperluan lainnya."
            class="lg:items-end">
            <div class="flex gap-3">
                <x-button type="button" x-data @click="$dispatch('open-modal', 'create-izin')" class="h-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Izin
                </x-button>

                <form action="{{ route('izin.index') }}" method="GET" class="flex gap-2 w-full md:w-auto">
                    <input type="hidden" name="status" value="{{ $statusId }}">
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
                    onchange="window.location.href='{{ route('izin.index') }}?status=' + this.value + '&search={{ request('search') }}'">
                    <option value="">Semua Status</option>
                    <option value="{{ \App\Enums\StatusPengajuan::PENDING }}" {{ $statusId == \App\Enums\StatusPengajuan::PENDING ? 'selected' : '' }}>Pending</option>
                    <option value="{{ \App\Enums\StatusPengajuan::DISETUJUI }}" {{ $statusId == \App\Enums\StatusPengajuan::DISETUJUI ? 'selected' : '' }}>Disetujui</option>
                    <option value="{{ \App\Enums\StatusPengajuan::DITOLAK }}" {{ $statusId == \App\Enums\StatusPengajuan::DITOLAK ? 'selected' : '' }}>Ditolak</option>
                </x-filter-select>
            </div>
        </x-page-header>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <x-table>
                <x-slot:header>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-left">Pegawai</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-left">Jenis Izin</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-left">Tanggal</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
                </x-slot:header>

                @forelse($izin as $item)
                    <tr class="hover:bg-slate-50 border-b border-slate-50 last:border-b-0">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs border border-slate-200">
                                    {{ substr($item->user->nama_lengkap ?? 'U', 0, 2) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-sm">{{ $item->user->nama_lengkap ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->user->nik ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-medium 
                                                                                        {{ $item->id_jenis_izin == \App\Enums\JenisIzin::SAKIT ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-blue-600' }}">
                                {{ $item->jenisIzin->nama_izin ?? 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            <div class="flex flex-col">
                                <span>{{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }}</span>
                                <span class="text-xs text-slate-400">s/d</span>
                                <span>{{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d M Y') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusName = $item->statusPengajuan->nama_status ?? '-';
                                $badgeColor = match ($item->id_status) {
                                    \App\Enums\StatusPengajuan::DISETUJUI => 'green',
                                    \App\Enums\StatusPengajuan::DITOLAK  => 'red',
                                    default => 'yellow'
                                };
                            @endphp
                            @if($item->id_jenis_izin == \App\Enums\JenisIzin::CUTI && $item->id_status == \App\Enums\StatusPengajuan::PENDING)
                                <x-badge color="blue">Via Surat Izin</x-badge>
                            @else
                                <x-badge color="{{ $badgeColor }}">{{ $statusName }}</x-badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button data-item="{{ json_encode([
                                        'id_izin'           => $item->id_izin,
                                        'id_status'         => $item->id_status,
                                        'id_jenis_izin'     => $item->id_jenis_izin,
                                        'tanggal_mulai'     => $item->tanggal_mulai,
                                        'tanggal_selesai'   => $item->tanggal_selesai,
                                        'alasan'            => $item->alasan,
                                        'alasan_penolakan'  => $item->alasan_penolakan,
                                        'bukti_file'        => $item->bukti_file,
                                        'user'              => ['nama_lengkap' => $item->user?->nama_lengkap, 'nik' => $item->user?->nik],
                                        'jenis_izin'        => ['nama_izin' => $item->jenisIzin?->nama_izin],
                                    ]) }}"
                                    onclick="openDetailModal(JSON.parse(this.getAttribute('data-item')))"
                                    class="px-3 py-1.5 text-xs font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-sm">
                                    Detail
                                </button>
                                @if($item->id_jenis_izin == \App\Enums\JenisIzin::CUTI && $item->suratIzin)
                                    <a href="{{ route('surat-izin.show', $item->suratIzin->id_surat) }}"
                                        class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition shadow-sm">
                                        Lihat Surat →
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state colspan="5" message="Belum ada data pengajuan izin." />
                @endforelse
            </x-table>
        </div>

        <x-pagination :paginator="$izin->appends(request()->query())" />
    </div>

    <x-modal name="create-izin" title="Buat Pengajuan Izin">
        <form action="{{ route('izin.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1"><x-select id="filter-kantor-izin" label="Filter Kantor">
                    <option value="">Semua Kantor</option>
                    @foreach($kantor as $k)
                        <option value="{{ $k->id_kantor }}">{{ $k->nama_kantor }}</option>
                    @endforeach
                </x-select></div>

                <div class="flex-1"><x-select id="filter-divisi-izin" label="Filter Divisi">
                    <option value="">Semua Divisi</option>
                    @foreach($divisi as $d)
                        <option value="{{ $d->id_divisi }}">{{ $d->nama_divisi }}</option>
                    @endforeach
                </x-select></div>
            </div>

            <div>
                <x-select id="id_user_izin" label="Pilih Pegawai" name="id_user" required>
                    <option value="">-- Pilih Pegawai --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" data-kantor="{{ $user->id_kantor }}" data-divisi="{{ $user->id_divisi }}" class="user-option">
                            {{ $user->nama_lengkap }} ({{ $user->nik }}) - {{ $user->kantor->nama_kantor ?? 'Kantor ?' }} / {{ $user->divisi->nama_divisi ?? 'Divisi ?' }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-select label="Jenis Izin" name="id_jenis_izin" required>
                    @foreach($jenisIzinList as $j)
                        <option value="{{ $j->id_jenis_izin }}">{{ $j->nama_izin }}</option>
                    @endforeach
                </x-select>
            </div>

            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1"><x-input type="date" label="Tanggal Mulai" name="tanggal_mulai" required /></div>
                <div class="flex-1"><x-input type="date" label="Tanggal Selesai" name="tanggal_selesai" required /></div>
            </div>

            <div>
                <x-textarea label="Alasan" name="alasan" required rows="3" />
            </div>

            <x-input type="file" label="Bukti Dokumen (Gambar/PDF)" name="bukti_file"
                accept=".jpeg,.png,.jpg,.pdf" />

            <div class="pt-4 flex justify-end gap-3">
                <x-button type="button" variant="secondary" x-data
                    @click="$dispatch('close-modal', 'create-izin')">Batal</x-button>
                <x-button type="submit" id="submitBtn">
                    <span id="submitSpinner" class="hidden animate-spin mr-2">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </span>
                    <span id="submitText">Simpan Pengajuan</span>
                </x-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="detail-izin" title="Detail Pengajuan Izin">
        <div class="space-y-4">
            
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex justify-between items-start">
                <div>
                    <h4 class="font-bold text-slate-800" id="detail-nama">Nama Pegawai</h4>
                    <p class="text-xs text-slate-500" id="detail-tgl">Tanggal...</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-200 text-slate-700"
                    id="detail-jenis">Jenis</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Alasan</label>
                <p class="text-sm text-slate-700 bg-slate-50 p-3 rounded-lg border border-slate-100" id="detail-alasan">...
                </p>
            </div>

            <div id="alasan-penolakan-wrapper" class="hidden">
                <label class="block text-xs font-bold text-red-500 uppercase tracking-wider mb-1">Alasan Penolakan</label>
                <p class="text-sm text-red-700 bg-red-50 p-3 rounded-lg border border-red-100" id="detail-alasan-penolakan">-</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Bukti Dokumen</label>
                <div
                    class="bg-slate-100 rounded-lg overflow-hidden border border-slate-200 flex items-center justify-center min-h-[150px] relative">
                    <img id="detail-bukti" src="" class="max-h-[300px] object-contain hidden">
                    <a id="link-download" href="#" target="_blank"
                        class="text-sm text-blue-600 hover:underline hidden">Download File</a>
                    <span id="no-bukti" class="text-xs text-slate-400">Tidak ada lampiran</span>
                </div>
            </div>

            <div class="flex flex-col gap-3 pt-4 border-t border-slate-100 mt-4">
                
                <div id="cuti-info-banner" class="hidden p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700 flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="font-bold">Pengajuan Cuti diproses via Surat Izin</p>
                        <p class="text-xs mt-0.5">Approval cuti dilakukan melalui alur Surat Izin (Manajer → HRD).</p>
                        <a href="{{ route('surat-izin.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 hover:text-blue-900 mt-1.5 underline underline-offset-2">
                            Buka Menu Surat Izin →
                        </a>
                    </div>
                </div>

                <div class="flex justify-end gap-3" id="action-buttons">
                <x-button type="button" variant="secondary" x-data
                    @click="$dispatch('close-modal', 'detail-izin')">Tutup</x-button>

                @auth
                    <form id="form-reject-izin" action="" method="POST" class="hidden">
                        @csrf
                        <input type="hidden" name="alasan_penolakan" id="input-alasan-penolakan">
                    </form>
                    <form id="form-approve-izin" action="" method="POST" class="hidden">@csrf</form>

                    <x-button id="btn-reject" variant="danger" onclick="submitRejectWithAlasan()">
                        Tolak
                    </x-button>
                    <x-button id="btn-approve" variant="primary"
                        onclick="confirmAction(event, 'form-approve-izin', 'Sistem akan update presensi otomatis!', '#10b981', 'Ya, Setujui')">
                        Setujui
                    </x-button>
                @endauth
            </div>
            </div>
        </div>
    </x-modal>

    <input type="hidden" id="current-id-izin">

@endsection

@section('script')
    <script>
        const JENIS_CUTI     = {{ \App\Enums\JenisIzin::CUTI }};
        const STATUS_PENDING  = {{ \App\Enums\StatusPengajuan::PENDING }};
        const STATUS_DITOLAK  = {{ \App\Enums\StatusPengajuan::DITOLAK }};

        function openDetailModal(data) {
            document.getElementById('current-id-izin').value = data.id_izin;
            document.getElementById('detail-nama').innerText = data.user.nama_lengkap;
            document.getElementById('detail-tgl').innerText = 'Mulai: ' + data.tanggal_mulai + ' | Selesai: ' + data.tanggal_selesai;
            document.getElementById('detail-jenis').innerText = data.jenis_izin ? data.jenis_izin.nama_izin : 'Unknown';
            document.getElementById('detail-alasan').innerText = data.alasan;

            const formApprove = document.getElementById('form-approve-izin');
            const formReject = document.getElementById('form-reject-izin');
            if (formApprove && formReject) {
                formApprove.action = `{{ url('izin') }}/${data.id_izin}/approve`;
                formReject.action = `{{ url('izin') }}/${data.id_izin}/reject`;
            }

            const img = document.getElementById('detail-bukti');
            const link = document.getElementById('link-download');
            const noBukti = document.getElementById('no-bukti');

            img.classList.add('hidden');
            link.classList.add('hidden');
            noBukti.classList.add('hidden');

            if (data.bukti_file) {
                const fileUrl = "{{ asset('storage') }}/" + data.bukti_file;
                const isImage = data.bukti_file.match(/\.(jpeg|jpg|png|gif)$/i) || data.bukti_file.includes('placehold.co');

                if (isImage) {
                    img.src = data.bukti_file.startsWith('http') ? data.bukti_file : fileUrl;
                    img.classList.remove('hidden');
                } else {
                    link.href = fileUrl;
                    link.classList.remove('hidden');
                }
            } else {
                noBukti.classList.remove('hidden');
            }

            const btnApprove = document.getElementById('btn-approve');
            const btnReject = document.getElementById('btn-reject');
            const cutiInfo = document.getElementById('cuti-info-banner');
            const alasanPenolakanWrapper = document.getElementById('alasan-penolakan-wrapper');
            
            if (btnApprove) btnApprove.classList.add('hidden');
            if (btnReject) btnReject.classList.add('hidden');
            if (cutiInfo) cutiInfo.classList.add('hidden');
            if (alasanPenolakanWrapper) alasanPenolakanWrapper.classList.add('hidden');

            if (data.id_status == STATUS_DITOLAK && data.alasan_penolakan) {
                document.getElementById('detail-alasan-penolakan').innerText = data.alasan_penolakan;
                if (alasanPenolakanWrapper) alasanPenolakanWrapper.classList.remove('hidden');
            }

            if (data.id_status == STATUS_PENDING) {
                if (data.id_jenis_izin == JENIS_CUTI) {
                    if (cutiInfo) cutiInfo.classList.remove('hidden');
                } else {
                    if (btnApprove) btnApprove.classList.remove('hidden');
                    if (btnReject) btnReject.classList.remove('hidden');
                }
            }

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'detail-izin' }));
        }

        function submitRejectWithAlasan() {
            const alasan = prompt('Masukkan alasan penolakan (opsional):');
            if (alasan === null) return;
            const inputAlasan = document.getElementById('input-alasan-penolakan');
            if (inputAlasan) inputAlasan.value = alasan;
            const form = document.getElementById('form-reject-izin');
            if (form) form.submit();
        }

        function initCreateIzinForm() {
            const createForm = document.querySelector('form[action="{{ route('izin.store') }}"]');
            if (createForm) {
                createForm.addEventListener('submit', function () {
                    const btn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('submitSpinner');
                    const text = document.getElementById('submitText');
                    if (btn) {
                        btn.disabled = true;
                        btn.classList.add('opacity-75', 'cursor-not-allowed');
                    }
                    if (spinner) spinner.classList.remove('hidden');
                    if (text) text.textContent = 'Menyimpan...';
                });
            }

            const filterKantor = document.getElementById('filter-kantor-izin');
            const filterDivisi = document.getElementById('filter-divisi-izin');
            const userSelect = document.getElementById('id_user_izin');

            function filterUsers() {
                if (!filterKantor || !filterDivisi || !userSelect) return;
                
                const kantorId = filterKantor.value;
                const divisiId = filterDivisi.value;
                
                userSelect.value = "";
                
                const options = userSelect.querySelectorAll('.user-option');
                options.forEach(opt => {
                    const optKantor = opt.getAttribute('data-kantor');
                    const optDivisi = opt.getAttribute('data-divisi');
                    
                    let show = true;
                    if (kantorId && optKantor !== kantorId) show = false;
                    if (divisiId && optDivisi !== divisiId) show = false;
                    
                    opt.hidden = !show;
                    opt.disabled = !show;
                });
            }

            if (filterKantor && filterDivisi) {
                filterKantor.removeEventListener('change', filterUsers);
                filterDivisi.removeEventListener('change', filterUsers);

                filterKantor.addEventListener('change', filterUsers);
                filterDivisi.addEventListener('change', filterUsers);
            }
        }

        if (typeof window.__izinFilterAttached === 'undefined') {
            window.__izinFilterAttached = true;
            document.addEventListener('turbo:load', initCreateIzinForm);
        }

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(initCreateIzinForm, 50);
        } else {
            document.addEventListener('DOMContentLoaded', initCreateIzinForm);
        }
    </script>
@endsection
