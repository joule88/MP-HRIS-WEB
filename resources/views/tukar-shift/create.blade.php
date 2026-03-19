@extends('layouts.app')

@section('title', 'Tukar Shift Kerja')

@section('content')
    <div class="space-y-6">

        <div class="flex justify-between items-center sm:hidden mb-4">
            <h1 class="text-2xl font-bold text-slate-800">Tukar Shift Kerja</h1>
        </div>

        <x-page-header title="Tukar Shift Kerja" subtitle="Tukar jadwal shift antara dua pegawai secara langsung.">
            <div class="flex gap-2">
                <x-back-button href="{{ route('tukar-shift.index') }}" />
            </div>
        </x-page-header>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden max-w-4xl mx-auto">
            <form action="{{ route('tukar-shift.store') }}" method="POST" class="p-6 md:p-8" id="form-tukar-shift">
                @csrf

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 text-red-600 p-4 rounded-xl border border-red-200 text-sm">
                        <div class="font-bold flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Terdapat kesalahan:
                        </div>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="mb-6">
                    <x-select id="id_kantor" name="id_kantor" label="Pilih Kantor" required>
                        <option value="">-- Pilih Kantor Terlebih Dahulu --</option>
                        @foreach($kantor as $k)
                            <option value="{{ $k->id_kantor }}">{{ $k->nama_kantor }}</option>
                        @endforeach
                    </x-select>
                    <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Pilih kantor untuk menampilkan daftar pegawai yang tersedia.
                    </p>
                </div>

                <div id="columns-wrapper" class="relative pt-6 overflow-visible">
                    <div id="columns-overlay" class="absolute -inset-2 bg-slate-50/50 backdrop-blur-[3px] z-20 rounded-2xl flex items-center justify-center cursor-not-allowed transition-all duration-300">
                        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 px-8 py-5 flex items-center gap-4 transform translate-y-4">
                            <div class="w-10 h-10 bg-amber-50 rounded-full flex items-center justify-center text-amber-500 shadow-sm border border-amber-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-800">Akses Terkunci</span>
                                <span class="text-xs text-slate-500">Pilih kantor terlebih dahulu untuk melanjutkan</span>
                            </div>
                        </div>
                    </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative">

                    <div class="space-y-5 bg-slate-50 p-6 rounded-2xl border border-slate-200 relative">
                        <div
                            class="absolute -top-3 left-6 px-3 bg-blue-100 text-blue-700 text-xs font-bold rounded-full py-1 border border-blue-200 uppercase tracking-wide">
                            Pihak Pertama</div>

                        <div>
                            <x-select id="id_user_1" name="id_user_1" label="Pegawai 1" required>
                                <option value="">-- Pilih Pegawai Pertama --</option>
                                @foreach($pegawai as $p)
                                    <option value="{{ $p->id }}" data-kantor="{{ $p->id_kantor }}" {{ old('id_user_1') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_lengkap }} ({{ optional($p->kantor)->nama_kantor ?? 'Tanpa Kantor' }})
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div>
                            <label for="id_jadwal_1" class="block text-sm font-semibold text-slate-700 mb-2">Jadwal yang
                                Ditukar <span class="text-red-500">*</span></label>
                            <div class="relative group">
                                <select id="id_jadwal_1" name="id_jadwal_1"
                                    class="w-full pl-4 pr-12 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl text-sm appearance-none outline-none focus:ring-4 focus:ring-[#130F26]/10 focus:border-[#130F26] transition-all duration-300 cursor-pointer truncate block h-11 disabled:opacity-60 disabled:cursor-not-allowed"
                                    required disabled>
                                    <option value="">Pilih pegawai terlebih dahulu</option>
                                </select>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#130F26] transition-colors" id="arrow_jadwal_1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none hidden" id="loading_jadwal_1">
                                    <svg class="animate-spin h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="hidden md:flex absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-10 h-10 bg-white border border-slate-200 rounded-full items-center justify-center text-slate-400 z-0 shadow-sm transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>

                    <div class="space-y-5 bg-slate-50 p-6 rounded-2xl border border-slate-200 relative">
                        <div
                            class="absolute -top-3 left-6 px-3 bg-fuchsia-100 text-fuchsia-700 text-xs font-bold rounded-full py-1 border border-fuchsia-200 uppercase tracking-wide">
                            Pihak Kedua</div>

                        <div>
                            <x-select id="id_user_2" name="id_user_2" label="Pegawai 2" required>
                                <option value="">-- Pilih Pegawai Kedua --</option>
                                @foreach($pegawai as $p)
                                    <option value="{{ $p->id }}" data-kantor="{{ $p->id_kantor }}" {{ old('id_user_2') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_lengkap }} ({{ optional($p->kantor)->nama_kantor ?? 'Tanpa Kantor' }})
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div>
                            <label for="id_jadwal_2" class="block text-sm font-semibold text-slate-700 mb-2">Jadwal yang
                                Ditukar <span class="text-red-500">*</span></label>
                            <div class="relative group">
                                <select id="id_jadwal_2" name="id_jadwal_2"
                                    class="w-full pl-4 pr-12 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl text-sm appearance-none outline-none focus:ring-4 focus:ring-[#130F26]/10 focus:border-[#130F26] transition-all duration-300 cursor-pointer truncate block h-11 disabled:opacity-60 disabled:cursor-not-allowed"
                                    required disabled>
                                    <option value="">Pilih pegawai terlebih dahulu</option>
                                </select>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#130F26] transition-colors" id="arrow_jadwal_2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none hidden" id="loading_jadwal_2">
                                    <svg class="animate-spin h-4 w-4 text-fuchsia-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                </div> 
                <div class="mt-8 border-t border-slate-100 pt-6">
                    <x-textarea id="keterangan" name="keterangan" label="Catatan Eksekusi (Opsional)" rows="3"
                        placeholder="Contoh: Mengganti hari kerja dikarenakan izin keluarga dll...">{{ old('keterangan') }}</x-textarea>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3">
                    <x-button type="link" href="{{ route('tukar-shift.index') }}" variant="secondary">Batal</x-button>
                    <x-button type="submit" id="submitBtn" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Eksekusi Tukar Shift
                    </x-button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function initTukarShiftForm() {
            const kantorSelect = document.getElementById('id_kantor');
            const columnsOverlay = document.getElementById('columns-overlay');
            const user1Select = document.getElementById('id_user_1');
            const user2Select = document.getElementById('id_user_2');
            const jadwal1Select = document.getElementById('id_jadwal_1');
            const jadwal2Select = document.getElementById('id_jadwal_2');
            const loading1 = document.getElementById('loading_jadwal_1');
            const loading2 = document.getElementById('loading_jadwal_2');
            const arrow1 = document.getElementById('arrow_jadwal_1');
            const arrow2 = document.getElementById('arrow_jadwal_2');

            if (!user1Select || !user2Select) return;

            const form = document.getElementById('form-tukar-shift');
            if (form) {
                form.addEventListener('submit', function (e) {
                    if (user1Select.value === user2Select.value && user1Select.value !== '') {
                        e.preventDefault();
                        alert('Pegawai pertama dan kedua tidak boleh orang yang sama!');
                    }
                });
            }

            function filterAllPegawaiByKantor() {
                const kantorId = kantorSelect ? kantorSelect.value : '';

                if (!kantorId) {
                    if (columnsOverlay) columnsOverlay.classList.remove('hidden');
                    return;
                }

                if (columnsOverlay) columnsOverlay.classList.add('hidden');

                [user1Select, user2Select].forEach(userSelect => {
                    userSelect.value = '';
                    const options = userSelect.querySelectorAll('option[data-kantor]');
                    options.forEach(opt => {
                        if (opt.getAttribute('data-kantor') === kantorId) {
                            opt.hidden = false;
                            opt.disabled = false;
                        } else {
                            opt.hidden = true;
                            opt.disabled = true;
                        }
                    });
                });

                jadwal1Select.innerHTML = '<option value="">Pilih pegawai terlebih dahulu</option>';
                jadwal1Select.disabled = true;
                jadwal2Select.innerHTML = '<option value="">Pilih pegawai terlebih dahulu</option>';
                jadwal2Select.disabled = true;
            }

            if (kantorSelect) {
                kantorSelect.addEventListener('change', filterAllPegawaiByKantor);
            }

            const fetchJadwal = async (userId, targetSelect, loadingEl, arrowEl, oldJadwalId = null) => {
                if (!userId) {
                    targetSelect.innerHTML = '<option value="">Pilih pegawai terlebih dahulu</option>';
                    targetSelect.disabled = true;
                    return;
                }

                if (loadingEl) { loadingEl.classList.remove('hidden'); }
                if (arrowEl) { arrowEl.classList.add('hidden'); }
                targetSelect.disabled = true;

                try {
                    const response = await fetch(`{{ route('tukar-shift.jadwal-user') }}?id_user=${userId}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!response.ok) throw new Error('Network error');

                    const data = await response.json();

                    targetSelect.innerHTML = '<option value="">-- Pilih Jadwal Tersedia --</option>';

                    if (data.length === 0) {
                        targetSelect.innerHTML = '<option value="">Tidak ada jadwal aktif (Bulan ini & depan)</option>';
                    } else {
                        data.forEach(j => {
                            const shiftName = j.shift ? j.shift.nama_shift : 'Shift ?';
                            let timeRange = '';
                            if (j.shift) {
                                timeRange = `(${j.shift.jam_mulai.substring(0, 5)} - ${j.shift.jam_selesai.substring(0, 5)})`;
                            }

                            const dateObj = new Date(j.tanggal);
                            const dateFormatted = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

                            const option = document.createElement('option');
                            option.value = j.id_jadwal;
                            option.textContent = `${dateFormatted} - ${shiftName} ${timeRange}`;

                            if (oldJadwalId && oldJadwalId == j.id_jadwal) {
                                option.selected = true;
                            }

                            targetSelect.appendChild(option);
                        });
                    }

                    targetSelect.disabled = false;
                } catch (error) {
                    console.error('Error fetching schedules:', error);
                    targetSelect.innerHTML = '<option value="">Gagal mengambil data</option>';
                    alert('Terjadi kesalahan saat mengambil jadwal pegawai: ' + error.message);
                } finally {
                    if (loadingEl) { loadingEl.classList.add('hidden'); }
                    if (arrowEl) { arrowEl.classList.remove('hidden'); }
                }
            };

            user1Select.addEventListener('change', function () {
                fetchJadwal(this.value, jadwal1Select, loading1, arrow1);
            });

            user2Select.addEventListener('change', function () {
                fetchJadwal(this.value, jadwal2Select, loading2, arrow2);
            });

            if (user1Select.value) {
                const oldJadwal1 = "{{ old('id_jadwal_1') }}";
                fetchJadwal(user1Select.value, jadwal1Select, loading1, arrow1, oldJadwal1);
            }

            if (user2Select.value) {
                const oldJadwal2 = "{{ old('id_jadwal_2') }}";
                fetchJadwal(user2Select.value, jadwal2Select, loading2, arrow2, oldJadwal2);
            }
        }

        if (!window.__tukarShiftEventAttached) {
            window.__tukarShiftEventAttached = true;
            document.addEventListener('turbo:load', initTukarShiftForm);
        }

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            if (document.getElementById('id_user_1')) {
                setTimeout(initTukarShiftForm, 50);
            }
        } else {
            document.addEventListener('DOMContentLoaded', initTukarShiftForm);
        }
    </script>
    <script src="{{ asset('js/form-handler.js') }}"></script>
@endsection
