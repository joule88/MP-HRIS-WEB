@extends('layouts.app')

@section('title', 'Generate Jadwal Kerja')

@section('content')
    <div class="space-y-6">

        <x-page-header title="Generate Jadwal Kerja" subtitle="Buat jadwal massal untuk beberapa pegawai sekaligus.">
            <x-back-button href="{{ route('jadwal.index') }}" />
        </x-page-header>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <form action="{{ route('jadwal.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <x-date-input label="Tanggal Mulai" name="tanggal_mulai"
                        value="{{ old('tanggal_mulai', now()->format('Y-m-d')) }}" required />

                    <x-date-input label="Tanggal Selesai" name="tanggal_selesai"
                        value="{{ old('tanggal_selesai', now()->addDays(6)->format('Y-m-d')) }}" required />

                    <x-select label="Shift" name="id_shift" id="id_shift" class="!mb-0" required>
                        <option value="">-- Pilih Shift --</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id_shift }}" {{ old('id_shift') == $shift->id_shift ? 'selected' : '' }}>
                                {{ $shift->nama_shift }}
                                ({{ \Carbon\Carbon::parse($shift->jam_mulai)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($shift->jam_selesai)->format('H:i') }})
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div class="mb-6">
                    <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-4">
                        <label class="block text-sm font-semibold text-slate-700">Pilih Pegawai</label>

                        <div class="flex gap-2">
                            <x-filter-select id="filter-kantor" class="py-1.5 text-xs">
                                <option value="">Semua Kantor</option>
                                @foreach($kantor as $k)
                                    <option value="{{ $k->id_kantor }}">{{ $k->nama_kantor }}</option>
                                @endforeach
                            </x-filter-select>

                            <x-filter-select id="filter-divisi" class="py-1.5 text-xs">
                                <option value="">Semua Divisi</option>
                                @foreach($divisi as $d)
                                    <option value="{{ $d->id_divisi }}">{{ $d->nama_divisi }}</option>
                                @endforeach
                            </x-filter-select>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mb-4">
                        <label class="flex items-center gap-2 text-sm text-primary font-bold cursor-pointer hover:bg-blue-50 px-3 py-1.5 rounded-lg border border-transparent hover:border-blue-100 transition-all active:scale-95">
                            <x-checkbox id="selectAll" class="pointer-events-none" />
                            <span>Pilih Semua (yang tampil)</span>
                        </label>
                        <span class="text-xs text-slate-400 bg-slate-100 px-2 py-1 rounded-full">(<span id="selectedCount" class="font-bold text-slate-700">0</span> pegawai dipilih)</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-96 overflow-y-auto border border-slate-200 rounded-lg p-4"
                        id="pegawai-container">
                        @foreach($pegawai as $p)
                            <label
                                class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition cursor-pointer pegawai-item"
                                data-kantor="{{ $p->id_kantor }}" data-divisi="{{ $p->id_divisi }}">
                                <x-checkbox name="user_ids[]" value="{{ $p->id }}" class="user-checkbox" />
                                <div>
                                    <div class="font-medium text-sm text-slate-800">{{ $p->nama_lengkap }}</div>
                                    <div class="text-xs text-slate-500">
                                        {{ $p->kantor->nama_kantor ?? '-' }} • {{ $p->jabatan->nama_jabatan ?? '-' }}
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <div id="empty-state" class="hidden text-center py-8 text-slate-400 text-sm">
                        Tidak ada pegawai yang sesuai filter.
                    </div>
                    @error('user_ids')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('jadwal.index') }}"
                        class="px-4 py-2 text-sm border rounded-xl hover:bg-slate-50">Batal</a>
                    <button type="submit"
                        class="px-6 py-2 text-sm bg-primary text-white rounded-xl hover:bg-primary/90 font-medium">
                        Generate Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            const inputMulai = document.querySelector('input[name="tanggal_mulai"]');
            const inputSelesai = document.querySelector('input[name="tanggal_selesai"]');

            if (inputMulai && inputSelesai) {
                inputMulai.addEventListener('change', function() {
                    if (this.value) {
                        inputSelesai.min = this.value;
                        if (inputSelesai.value && inputSelesai.value < this.value) {
                            inputSelesai.value = this.value;
                        }
                    }
                });
                inputSelesai.addEventListener('change', function() {
                    if (this.value) {
                        inputMulai.max = this.value;
                        if (inputMulai.value && inputMulai.value > this.value) {
                            inputMulai.value = this.value;
                        }
                    }
                });
                if (inputMulai.value) inputSelesai.min = inputMulai.value;
                if (inputSelesai.value) inputMulai.max = inputSelesai.value;
            }
        })();

        (function() {
            const formObj = document.querySelector('form');
            if (formObj) {
                formObj.addEventListener('submit', function (e) {
                    e.preventDefault();

                    let form = this;

                    let selectedUserIds = [];
                    document.querySelectorAll('.user-checkbox:checked').forEach((cb) => {
                        selectedUserIds.push(cb.value);
                    });

                    let payload = {
                        _token: "{{ csrf_token() }}",
                        user_ids: selectedUserIds,
                        tanggal_mulai: document.querySelector('input[name="tanggal_mulai"]').value,
                        tanggal_selesai: document.querySelector('input[name="tanggal_selesai"]').value
                    };

                    if (payload.user_ids.length === 0 || !payload.tanggal_mulai || !payload.tanggal_selesai) {
                        Swal.fire('Peringatan', 'Mohon lengkapi data form dan pilih pegawai.', 'warning');
                        return;
                    }

                    Swal.fire({
                        title: 'Memeriksa Jadwal...',
                        text: 'Sedang mengecek bentrok penggunaan poin',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() }
                    });

                    fetch("{{ route('jadwal.check-conflicts') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify(payload)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.has_conflict) {
                                Swal.fire({
                                    title: '⚠️ Konflik Data Poin!',
                                    html: data.message,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33',
                                    cancelButtonColor: '#3085d6',
                                    confirmButtonText: 'Ya, Ubah & Batalkan Poin',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        form.submit();
                                    }
                                });
                            } else {
                                form.submit();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'Gagal melakukan pengecekan data.', 'error');
                        });
                });
            }

            const selectAllCheckbox = document.getElementById('selectAll');
            const pegawaiContainer = document.getElementById('pegawai-container');
            const emptyState = document.getElementById('empty-state');
            const selectedCountEl = document.getElementById('selectedCount');
            const filterKantor = document.getElementById('filter-kantor');
            const filterDivisi = document.getElementById('filter-divisi');

            if (!selectAllCheckbox) return;

            let userCheckboxes = document.querySelectorAll('.user-checkbox');
            let pegawaiItems = document.querySelectorAll('.pegawai-item');

            function updateCount() {
                const count = document.querySelectorAll('.user-checkbox:checked').length;
                selectedCountEl.textContent = count;
            }

            function filterPegawai() {
                const kantorId = filterKantor.value;
                const divisiId = filterDivisi.value;
                let visibleCount = 0;

                pegawaiItems.forEach(item => {
                    const itemKantor = item.getAttribute('data-kantor');
                    const itemDivisi = item.getAttribute('data-divisi');

                    let show = true;
                    if (kantorId && itemKantor !== kantorId) show = false;
                    if (divisiId && itemDivisi !== divisiId) show = false;

                    if (show) {
                        item.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        item.classList.add('hidden');
                    }
                });

                if (visibleCount === 0) {
                    pegawaiContainer.classList.add('hidden');
                    emptyState.classList.remove('hidden');
                } else {
                    pegawaiContainer.classList.remove('hidden');
                    emptyState.classList.add('hidden');
                }
            }

            selectAllCheckbox.addEventListener('change', function () {
                const visibleCheckboxes = document.querySelectorAll('.pegawai-item:not(.hidden) .user-checkbox');
                visibleCheckboxes.forEach(cb => cb.checked = this.checked);
                updateCount();
            });

            if(filterKantor) filterKantor.addEventListener('change', filterPegawai);
            if(filterDivisi) filterDivisi.addEventListener('change', filterPegawai);

            if(pegawaiContainer) {
                pegawaiContainer.addEventListener('change', function (e) {
                    if (e.target.classList.contains('user-checkbox')) {
                        updateCount();
                    }
                });
            }
        })();
    </script>
@endsection
