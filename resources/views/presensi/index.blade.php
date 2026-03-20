@extends('layouts.app')

@section('title', 'Monitoring Presensi')

@section('style')
    <style>
        .modal-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .modal-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .modal-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>
@endsection

@section('content')
    <div class="space-y-6">

        <x-page-header title="Monitoring Presensi" subtitle="Pantau kehadiran pegawai harian.">
                <div class="flex gap-2">
                    @if(Auth::user()->roles->contains(fn($role) => strtolower($role->nama_role) === 'hrd'))
                    <a href="{{ route('presensi.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-lg text-white bg-slate-800 hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-colors shadow-sm text-sm h-[42px]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Input Manual
                    </a>
                    @endif

                    <form action="{{ route('presensi.index') }}" method="GET" class="flex flex-col md:flex-row gap-2 ml-2 border-l border-slate-200 pl-4">
                        <x-date-input name="tanggal" :value="$tanggal" class="!mb-0 w-full md:w-36 h-[42px]" onchange="this.form.submit()" />

                        <x-filter-select name="divisi_id" onchange="this.form.submit()" class="h-[42px]">
                            <option value="">Semua Divisi</option>
                            @foreach($divisi as $d)
                                <option value="{{ $d->id_divisi }}" {{ request('divisi_id') == $d->id_divisi ? 'selected' : '' }}>
                                    {{ $d->nama_divisi }}
                                </option>
                            @endforeach
                        </x-filter-select>

                        <x-filter-select name="filter_status" onchange="this.form.submit()" class="h-[42px]">
                            <option value="">Semua Status</option>
                            <option value="tepat_waktu" {{ request('filter_status') == 'tepat_waktu' ? 'selected' : '' }}>Tepat Waktu</option>
                            <option value="terlambat" {{ request('filter_status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                            <option value="pending" {{ request('filter_status') == 'pending' ? 'selected' : '' }}>Menunggu Validasi</option>
                        </x-filter-select>
                    </form>
                </div>
        </x-page-header>

        @if($pendingDates->count() > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-amber-800 mb-2">
                        <span class="text-lg">{{ $pendingDates->sum('jumlah') }}</span> presensi menunggu validasi
                    </p>
                    <p class="text-xs text-amber-600 mb-3">Klik tanggal di bawah untuk langsung melihat presensi yang perlu ditinjau:</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($pendingDates as $pd)
                            <a href="{{ route('presensi.index', ['tanggal' => $pd->tgl, 'filter_status' => 'pending']) }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all
                                    {{ $tanggal == $pd->tgl ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-amber-700 border border-amber-200 hover:bg-amber-100' }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ \Carbon\Carbon::parse($pd->tgl)->format('d M Y') }}
                                <span class="bg-amber-200 text-amber-800 px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $tanggal == $pd->tgl ? 'bg-white/30 text-white' : '' }}">
                                    {{ $pd->jumlah }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <form id="bulkForm" action="{{ route('presensi.bulkAction') }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="bulkActionType" value="">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative">
            <x-table>
                <x-slot:header>
                    <th class="px-6 py-4 text-left w-12">
                        <x-checkbox id="selectAll" />
                    </th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-left">Pegawai</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-left">Jam Kerja</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Lokasi</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Validasi</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
                </x-slot:header>

                @forelse($presensi as $p)
                    <tr class="hover:bg-slate-50 border-b border-slate-50 last:border-b-0 {{ $p->id_validasi == 2 ? 'bg-amber-50/50' : '' }}">
                        <td class="px-6 py-4">
                            @if($p->id_validasi == 2)
                                <x-checkbox name="presensi_ids[]" value="{{ $p->id_presensi }}" class="presensi-checkbox" />
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($p->foto_wajah_masuk)
                                    <img src="{{ asset('storage/' . $p->foto_wajah_masuk) }}" class="w-10 h-10 rounded-full object-cover border-2 {{ $p->dalam_radius ? 'border-emerald-300' : 'border-rose-300' }} shadow-sm">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs border border-slate-200">
                                        {{ substr($p->user->nama_lengkap ?? 'U', 0, 2) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-bold text-slate-800 text-sm">{{ $p->user->nama_lengkap ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $p->user->nik ?? '-' }} • {{ $p->nama_shift }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col text-sm">
                                <div class="flex items-center gap-2 text-slate-700">
                                    <span class="w-16 text-xs text-slate-500">Masuk:</span>
                                    @if($p->is_adjusted && strpos($p->adjustment_note, 'Datang') !== false)
                                        <div class="flex flex-col">
                                            <span class="text-xs text-slate-400 line-through">{{ substr($p->jam_jadwal_masuk_original, 0, 5) }}</span>
                                            <span class="font-mono font-bold text-amber-600" title="{{ $p->adjustment_note }}">
                                                {{ substr($p->jam_jadwal_masuk, 0, 5) }}*
                                            </span>
                                        </div>
                                    @else
                                        <span class="font-mono font-bold {{ $p->jam_masuk > $p->jam_jadwal_masuk ? 'text-red-600' : 'text-emerald-600' }}">
                                            {{ \Carbon\Carbon::parse($p->jam_masuk)->format('H:i') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-slate-700 mt-1">
                                    <span class="w-16 text-xs text-slate-500">Pulang:</span>
                                    @if($p->is_adjusted && strpos($p->adjustment_note, 'Pulang') !== false)
                                         <div class="flex flex-col">
                                            <span class="text-xs text-slate-400 line-through">{{ $p->jam_jadwal_pulang_original != '-' ? substr($p->jam_jadwal_pulang_original, 0, 5) : '-' }}</span>
                                            <span class="font-mono font-bold text-amber-600" title="{{ $p->adjustment_note }}">
                                                {{ $p->jam_jadwal_pulang != '-' ? substr($p->jam_jadwal_pulang, 0, 5) : '-' }}*
                                            </span>
                                        </div>
                                    @else
                                        <span class="font-mono font-bold">
                                            {{ $p->jam_pulang ? \Carbon\Carbon::parse($p->jam_pulang)->format('H:i') : '--:--' }}
                                        </span>
                                    @endif
                                </div>
                                @if($p->is_adjusted)
                                    <div class="mt-1">
                                         <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">
                                            {{ $p->adjustment_note }}
                                         </span>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($p->jarak_masuk !== null)
                                @if($p->dalam_radius)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Di Kantor
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ round($p->jarak_masuk) }}m</div>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Luar Radius
                                    </span>
                                    <div class="text-[10px] text-rose-400 mt-0.5">{{ round($p->jarak_masuk) }}m</div>
                                @endif
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <x-badge color="{{ $p->badge_color }}">
                                {{ $p->status_keterlambatan }}
                            </x-badge>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <x-badge color="{{ $p->validasi_color }}">
                                {{ $p->validasi_label }}
                            </x-badge>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if ($p->id_validasi == 2)
                                    <button type="button" onclick="inlineApprove({{ $p->id_presensi }})" class="p-1.5 text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors border border-emerald-100" title="Setujui">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                    <button type="button" onclick="inlineReject({{ $p->id_presensi }})" class="p-1.5 text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors border border-rose-100" title="Tolak">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                @endif

                                @if(Auth::user()->roles->contains(fn($role) => strtolower($role->nama_role) === 'hrd'))
                                <a href="{{ route('presensi.edit', $p->id_presensi) }}" class="p-1.5 text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border border-blue-100" title="Koreksi Jam/Status">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                @endif

                                <x-button type="button" variant="secondary" onclick="openDetailModal(event, {{ $p }})" class="px-3 py-1.5 text-xs">
                                    Detail
                                </x-button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <x-empty-state colspan="7" message="Tidak ada data presensi hari ini." />
                    </tr>
                @endforelse
            </x-table>

            <div id="bulkActionBar" class="hidden sticky bottom-0 left-0 right-0 bg-white/80 border-t border-primary/20 p-4 flex items-center justify-between shadow-2xl z-50 backdrop-blur-md">
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-white text-xs font-bold" id="selectedCount">0</span>
                    <span class="text-sm font-semibold text-slate-700">Terpilih</span>
                </div>
                <div class="flex gap-2 relative">
                    <button type="button" id="btnBulkReject" class="px-4 py-2 border border-red-200 bg-white text-red-600 font-medium text-sm rounded-lg hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors shadow-sm">Tolak</button>
                    <button type="button" id="btnBulkApprove" class="px-4 py-2 bg-primary border border-transparent text-white font-medium text-sm rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors shadow-sm">Setujui</button>
                </div>
            </div>
        </div>
        </form>

        <x-pagination :paginator="$presensi->appends(request()->query())" />
    </div>

    <x-modal name="detail-presensi" title="Detail Presensi">
        <div class="space-y-6">

            <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-slate-500 font-bold text-sm border border-slate-200 shadow-sm"
                    id="detail-inisial">
                    NN
                </div>
                <div>
                    <h4 class="font-bold text-slate-800" id="detail-nama">Nama Pegawai</h4>
                    <p class="text-sm text-slate-500" id="detail-info">NIK • Jabatan</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Foto Masuk</label>
                    <div class="aspect-square bg-slate-100 rounded-lg overflow-hidden border border-slate-200 flex items-center justify-center relative">
                        <img id="img-masuk" src="" class="w-full h-full object-cover hidden">
                        <span id="no-img-masuk" class="text-xs text-slate-400">Tidak ada foto</span>
                    </div>
                    <div class="text-center">
                        <span class="text-xs font-mono bg-slate-100 px-2 py-1 rounded text-slate-600" id="coor-masuk">-</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Foto Pulang</label>
                    <div class="aspect-square bg-slate-100 rounded-lg overflow-hidden border border-slate-200 flex items-center justify-center relative">
                        <img id="img-pulang" src="" class="w-full h-full object-cover hidden">
                        <span id="no-img-pulang" class="text-xs text-slate-400">Belum Pulang</span>
                    </div>
                    <div class="text-center">
                        <span class="text-xs font-mono bg-slate-100 px-2 py-1 rounded text-slate-600" id="coor-pulang">-</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <div class="p-3 bg-amber-50 text-amber-700 text-sm rounded-lg flex items-start gap-2 hidden" id="alert-radius">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    <span><b>Peringatan:</b> Jarak Absen Masuk di luar radius (<b><span id="detail-jarak"></span> meter</b>
                        dari kantor). Perlu verifikasi manual.</span>
                </div>

                <a id="btn-maps" href="#" target="_blank"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Lihat Lokasi di Google Maps
                </a>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100" id="action-buttons">
                <x-button type="button" variant="secondary" x-data
                    @click="$dispatch('close-modal', 'detail-presensi')">Tutup</x-button>

                <form id="form-reject-presensi" action="" method="POST" class="hidden">@csrf</form>
                <form id="form-approve-presensi" action="" method="POST" class="hidden">@csrf</form>

                <x-button id="btn-reject" variant="danger"
                    onclick="confirmAction(event, 'form-reject-presensi', 'Status validasi akan diperbarui menjadi Ditolak.', '#ef4444', 'Tolak Absensi')">
                    Tolak
                </x-button>
                <x-button id="btn-approve" variant="primary"
                    onclick="confirmAction(event, 'form-approve-presensi', 'Status validasi akan diperbarui menjadi Disetujui.', '#10b981', 'Setujui Absensi')">
                    Setujui Absensi
                </x-button>
            </div>
        </div>
    </x-modal>

    <input type="hidden" id="current-id">

@endsection

@section('script')
    <script>
        function openDetailModal(event, data) {
            if (event) event.preventDefault();
            document.getElementById('current-id').value = data.id_presensi;

            document.getElementById('detail-inisial').innerText = data.user.nama_lengkap.substring(0, 2).toUpperCase();
            document.getElementById('detail-nama').innerText = data.user.nama_lengkap;
            document.getElementById('detail-info').innerText = (data.user.nik || '-') + ' • ' + (data.user.jabatan ? data.user.jabatan.nama_jabatan : '-');

            const imgMasuk = document.getElementById('img-masuk');
            const noImgMasuk = document.getElementById('no-img-masuk');
            const coorMasuk = document.getElementById('coor-masuk');

            if (data.foto_wajah_masuk) {
                imgMasuk.src = "{{ asset('storage') }}/" + data.foto_wajah_masuk;
                imgMasuk.classList.remove('hidden');
                noImgMasuk.classList.add('hidden');
            } else {
                imgMasuk.classList.add('hidden');
                noImgMasuk.classList.remove('hidden');
            }
            coorMasuk.innerText = data.lat_masuk + ', ' + data.lon_masuk;

            const imgPulang = document.getElementById('img-pulang');
            const noImgPulang = document.getElementById('no-img-pulang');
            const coorPulang = document.getElementById('coor-pulang');

            if (data.foto_wajah_pulang) {
                imgPulang.src = "{{ asset('storage') }}/" + data.foto_wajah_pulang;
                imgPulang.classList.remove('hidden');
                noImgPulang.classList.add('hidden');
                coorPulang.innerText = data.lat_pulang + ', ' + data.lon_pulang;
            } else {
                imgPulang.classList.add('hidden');
                noImgPulang.classList.remove('hidden');
                coorPulang.innerText = '-';
            }

            const btnMaps = document.getElementById('btn-maps');
            btnMaps.href = `https://www.google.com/maps/search/?api=1&query=${data.lat_masuk},${data.lon_masuk}`;

            const radiusAlert = document.getElementById('alert-radius');
            if (data.id_validasi == 2) {
                radiusAlert.classList.remove('hidden');
                document.getElementById('detail-jarak').innerText = data.jarak_masuk ? data.jarak_masuk : 'Tidak diketahui';
            } else {
                radiusAlert.classList.add('hidden');
            }

            const formApprove = document.getElementById('form-approve-presensi');
            const formReject = document.getElementById('form-reject-presensi');
            if (formApprove && formReject) {
                formApprove.action = `{{ url('presensi') }}/${data.id_presensi}/approve`;
                formReject.action = `{{ url('presensi') }}/${data.id_presensi}/reject`;
            }

            const btnApprove = document.getElementById('btn-approve');
            const btnReject = document.getElementById('btn-reject');

            btnApprove.classList.add('hidden');
            btnReject.classList.add('hidden');

            if (data.id_validasi == 2) {
                btnApprove.classList.remove('hidden');
                btnReject.classList.remove('hidden');
            }

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'detail-presensi' }));
        }

        function initPresensiCheckbox() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.presensi-checkbox');
            const bulkActionBar = document.getElementById('bulkActionBar');
            const selectedCountSpan = document.getElementById('selectedCount');
            const btnBulkApprove = document.getElementById('btnBulkApprove');
            const btnBulkReject = document.getElementById('btnBulkReject');
            const bulkForm = document.getElementById('bulkForm');
            const bulkActionType = document.getElementById('bulkActionType');

            if (!selectAll || !bulkActionBar) return;

            function updateBulkActionBar() {
                const checkedCheckboxes = document.querySelectorAll('.presensi-checkbox:checked');
                const count = checkedCheckboxes.length;

                selectedCountSpan.innerText = count;

                if (count > 0) {
                    bulkActionBar.classList.remove('hidden', 'translate-y-full');
                    bulkActionBar.classList.add('translate-y-0');
                    bulkActionBar.parentElement.style.paddingBottom = bulkActionBar.offsetHeight + 'px';
                } else {
                    bulkActionBar.classList.add('translate-y-full');
                    bulkActionBar.parentElement.style.paddingBottom = '0px';
                    setTimeout(() => {
                        if(document.querySelectorAll('.presensi-checkbox:checked').length === 0) {
                            bulkActionBar.classList.add('hidden');
                        }
                    }, 300);
                }

                if (checkboxes.length > 0) {
                    selectAll.checked = count === checkboxes.length;
                }
            }

            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                updateBulkActionBar();
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkActionBar);
            });

            if(btnBulkApprove) {
                btnBulkApprove.addEventListener('click', function() {
                    bulkActionType.value = 'approve';
                    submitBulkWithLoading('Menyetujui...');
                });
            }

            if(btnBulkReject) {
                btnBulkReject.addEventListener('click', function() {
                    const count = document.querySelectorAll('.presensi-checkbox:checked').length;
                    Swal.fire({
                        title: 'Tolak Presensi?',
                        text: `Yakin ingin menolak ${count} data presensi terpilih?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#cbd5e1',
                        confirmButtonText: 'Ya, Tolak',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            bulkActionType.value = 'reject';
                            submitBulkWithLoading('Menolak...');
                        }
                    });
                });
            }

            function submitBulkWithLoading(text) {
                Swal.fire({
                    title: 'Memproses...',
                    text: text,
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                bulkForm.submit();
            }
        }

        document.addEventListener('turbo:load', initPresensiCheckbox);
        document.addEventListener('DOMContentLoaded', function() {
            if (!window.Turbo) initPresensiCheckbox();
        });

        function inlineApprove(id) {
            Swal.fire({
                title: 'Setujui Presensi?',
                text: 'Presensi ini akan disetujui.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#cbd5e1',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                    fetch(`{{ url('presensi') }}/${id}/approve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        redirect: 'follow'
                    }).then(response => {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Presensi berhasil disetujui.', timer: 1500, showConfirmButton: false });
                        setTimeout(() => window.location.reload(), 1500);
                    }).catch(() => {
                        Swal.fire('Error', 'Gagal memproses. Coba lagi.', 'error');
                    });
                }
            });
        }

        function inlineReject(id) {
            Swal.fire({
                title: 'Tolak Presensi?',
                text: 'Status validasi akan diubah menjadi Ditolak.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#cbd5e1',
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                    fetch(`{{ url('presensi') }}/${id}/reject`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        redirect: 'follow'
                    }).then(response => {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Presensi berhasil ditolak.', timer: 1500, showConfirmButton: false });
                        setTimeout(() => window.location.reload(), 1500);
                    }).catch(() => {
                        Swal.fire('Error', 'Gagal memproses. Coba lagi.', 'error');
                    });
                }
            });
        }
    </script>
@endsection
