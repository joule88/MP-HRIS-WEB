@extends('layouts.app')

@section('title', 'Penjadwalan Shift')

@section('style')
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/animations/scale.css" />
    <style>
        .fc-event {
            cursor: pointer;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            background: transparent;
        }

        .fc-daygrid-event {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fc-toolbar-title {
            font-size: 1.25rem !important;
            font-weight: 700;
            color: #1e293b;
        }

        .fc-col-header-cell {
            background-color: #f8fafc;
            padding: 12px 0;
            border-color: #e2e8f0;
        }

        .fc-daygrid-day-number {
            color: #64748b;
            font-weight: 500;
        }

        .fc-day-today {
            background-color: #f0f9ff !important;
        }

        .fc-event-title {
            font-weight: 600;
            line-height: 1.2;
        }

        /* Limit Popover Height */
        .fc-popover-body {
            max-height: 250px !important;
            overflow-y: auto !important;
        }

        /* Tippy Light Border Theme Customization */
        .tippy-box[data-theme~='light-border'] {
            background-color: white;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            font-size: 12px;
            text-align: left;
        }

        /* Modern FullCalendar SaaS Overrides */
        #calendar {
            min-height: 600px;
            font-family: inherit;
        }
        
        .fc-theme-standard td, .fc-theme-standard th, .fc-theme-standard .fc-scrollgrid {
            border-color: #f1f5f9;
        }
        .fc-theme-standard .fc-scrollgrid { border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }

        .fc-col-header-cell {
            background-color: #f8fafc;
            padding: 12px 0 !important;
        }
        .fc-col-header-cell-cushion {
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-decoration: none !important;
        }

        .fc-daygrid-day-top {
            justify-content: center !important;
            padding-top: 8px;
            padding-bottom: 4px;
        }
        .fc-daygrid-day-number {
            color: #475569;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none !important;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            transition: all 0.2s;
        }
        .fc-daygrid-day-number:hover {
            background-color: #f1f5f9;
        }

        .fc-day-today {
            background-color: #ffffff !important;
        }
        .fc-day-today .fc-daygrid-day-number {
            color: #ffffff !important;
            background-color: #3b82f6 !important;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }

        .fc-daygrid-day-events {
            padding: 0 6px !important;
        }
        .fc-daygrid-event-harness {
            margin-bottom: 6px !important;
        }
        .fc-event {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            border-radius: 6px !important;
            overflow: hidden;
        }

        .fc-daygrid-more-link {
            color: #3b82f6 !important;
            font-weight: 700;
            font-size: 0.7rem;
            padding: 5px 8px;
            background-color: #eff6ff;
            border-radius: 6px;
            display: block;
            text-align: center;
            margin: 4px 6px 8px 6px;
            transition: all 0.2s;
            text-decoration: none !important;
        }
        .fc-daygrid-more-link:hover {
            background-color: #dbeafe;
            color: #2563eb !important;
        }

        .fc .fc-toolbar-title {
            font-size: 1.25rem !important;
            font-weight: 800;
            color: #0f172a;
        }
        .fc .fc-button-primary {
            background-color: #ffffff !important;
            color: #475569 !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            font-weight: 600;
            text-transform: capitalize;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            padding: 6px 16px !important;
            transition: all 0.2s;
        }
        .fc .fc-button-primary:hover {
            background-color: #f8fafc !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
            box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05) !important;
        }
        .fc-toolbar.fc-header-toolbar {
            margin-bottom: 24px !important;
        }
    </style>
@endsection

@section('content')
    <div class="space-y-6">

        <x-page-header title="Penjadwalan Shift" subtitle="Monitor dan atur jadwal kerja pegawai." class="lg:items-end">
            <div class="relative max-w-xs w-full lg:w-48">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>
                <input type="text" id="filter_nama" class="bg-gray-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full pl-10 p-2" placeholder="Cari Pegawai...">
            </div>
            <x-filter-select id="filter_kantor">
                <option value="">Semua Kantor</option>
                @foreach($kantor as $k)
                    <option value="{{ $k->id_kantor }}">{{ $k->nama_kantor }}</option>
                @endforeach
            </x-filter-select>

            <x-filter-select id="filter_divisi">
                <option value="">Semua Divisi</option>
                @foreach($divisi as $d)
                    <option value="{{ $d->id_divisi }}">{{ $d->nama_divisi }}</option>
                @endforeach
            </x-filter-select>

            <div class="flex items-center gap-2">
                <x-button x-data @click="$dispatch('open-modal', 'bulk-delete')" variant="danger" class="flex items-center gap-2 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Hapus Massal
                </x-button>
                <x-button type="link" href="{{ route('tukar-shift.index') }}" variant="warning" class="flex items-center gap-2 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    Tukar Shift
                </x-button>
                <x-button type="link" href="{{ route('jadwal.create') }}" variant="primary" class="flex items-center gap-2 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Generator
                </x-button>
            </div>
        </x-page-header>

        <div class="flex flex-wrap gap-4 text-sm bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-slate-500 font-medium mr-2">Keterangan Shift:</span>
            @foreach($shifts as $shift)
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $shift->color }}"></span>
                    <span class="text-slate-700">{{ $shift->nama_shift }}</span>
                </div>
            @endforeach
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                <span class="text-red-600 font-semibold">Hari Libur</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative min-h-[600px]">
            
            <div id="calendar-shimmer" class="w-full animate-pulse transition-opacity duration-300">
                
            </div>

            <div id="loading" class="absolute inset-0 bg-white/50 backdrop-blur-[2px] z-50 flex items-center justify-center transition-opacity duration-300 opacity-0 pointer-events-none">
                <div class="bg-white rounded-xl shadow-lg border border-slate-100 p-4 flex items-center gap-3">
                    <svg class="animate-spin h-6 w-6 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-slate-700 font-bold text-sm" id="loading-text">Menyinkronkan Jadwal...</span>
                </div>
            </div>

            <div id="calendar-wrapper" class="hidden">
                <div id="calendar" class="w-full"></div>
            </div>
        </div>

        <div id="daily-table-container" class="space-y-4 pt-6 mt-6 border-t border-slate-200 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-800" id="table-date-label">Daftar Jadwal Harian</h3>
                    <p class="text-sm text-slate-500">Daftar pegawai yang bertugas pada tanggal tertentu.</p>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Pegawai</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Shift</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Jam Kerja</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Kantor</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="daily-table-body" class="divide-y divide-slate-100">
                        <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">Silakan klik salah satu tanggal pada kalender untuk melihat daftar jadwal.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-modal name="edit-jadwal" title="Edit Jadwal Kerja">
        <form id="form-edit-jadwal" onsubmit="return updateJadwal(event)">
            @method('PUT')
            <div class="space-y-4">
                <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Pegawai:</span> <span
                            class="font-bold text-slate-800" id="detail-nama"></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Kantor:</span> <span
                            class="text-slate-800" id="detail-kantor"></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Jabatan:</span> <span
                            class="text-slate-800" id="detail-jabatan"></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Tanggal:</span> <span
                            class="font-mono text-slate-800" id="detail-tanggal"></span></div>
                </div>

                <div id="detail-poin-container"
                    class="hidden p-3 bg-orange-50 text-orange-700 text-sm rounded-lg border border-orange-200">
                    <div class="font-bold flex items-center gap-2 mb-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        Penggunaan Poin
                    </div>
                    <ul id="detail-poin-list" class="list-disc list-inside space-y-1 ml-1"></ul>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700">Ganti Shift</label>
                    <select name="id_shift" id="edit-id-shift"
                        class="w-full rounded-lg border-slate-300 focus:border-primary focus:ring-primary text-sm">
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id_shift }}">{{ $shift->nama_shift }}
                                ({{ \Carbon\Carbon::parse($shift->jam_mulai)->format('H:i') }}-{{ \Carbon\Carbon::parse($shift->jam_selesai)->format('H:i') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-between items-center pt-6 border-t border-slate-100 mt-6">
                <button type="button" onclick="deleteJadwal()"
                    class="text-red-500 text-sm hover:underline font-medium">Hapus Jadwal</button>
                <div class="flex gap-3">
                    <x-button type="button" x-data @click="$dispatch('close-modal', 'edit-jadwal')" variant="secondary">Batal</x-button>
                    <x-button type="submit">Simpan Perubahan</x-button>
                </div>
            </div>
            <input type="hidden" id="edit-id-jadwal">
            <input type="hidden" id="edit-tanggal-raw">
            <input type="hidden" id="edit-id-user">
        </form>
    </x-modal>

    <x-modal name="detail-harian" title="Detail Jadwal Harian">
        <div class="mb-4">
            <h3 class="text-lg font-bold text-slate-800" id="detail-harian-tanggal"></h3>
            <p class="text-sm text-slate-500">Daftar lengkap pegawai yang bertugas.</p>
        </div>

        <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2" id="list-detail-harian">
            
        </div>

        <div class="flex justify-end pt-4 border-t border-slate-100 mt-4">
            <x-button type="button" x-data @click="$dispatch('close-modal', 'detail-harian')" variant="secondary">Tutup</x-button>
        </div>
    </x-modal>

    <x-modal name="bulk-delete" title="Hapus Jadwal Massal">
        <form action="{{ route('jadwal.bulk-delete') }}" method="POST" id="form-bulk-delete"
            x-data="{ 
                tglMulai: '', 
                tglSelesai: '',
                selectAll: true, 
                toggleAll() { 
                    let checkboxes = document.querySelectorAll('.user-checkbox');
                    checkboxes.forEach(cb => cb.checked = this.selectAll);
                },
                updateSelectAll() {
                    let checkboxes = document.querySelectorAll('.user-checkbox');
                    let checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
                    this.selectAll = checkboxes.length === checkedBoxes.length;
                }
            }"
        >
            @csrf
            <div class="space-y-4">
                <div class="bg-red-50 border border-red-200 p-3 rounded-lg text-sm text-red-700">
                    <p>Hati-hati! Tindakan ini akan menghapus semua jadwal shift pegawai pada rentang tanggal yang dipilih dan tidak dapat dikembalikan.</p>
                </div>
                
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1"><x-date-input label="Tanggal Mulai" name="tanggal_mulai" required class="mb-0" x-model="tglMulai" x-on:change="if(tglMulai) { $refs.endInput.min = tglMulai; if(tglSelesai && tglSelesai < tglMulai) tglSelesai = tglMulai; }" x-ref="startInput" /></div>
                    <div class="flex-1"><x-date-input label="Tanggal Selesai" name="tanggal_selesai" required class="mb-0" x-model="tglSelesai" x-on:change="if(tglSelesai) { $refs.startInput.max = tglSelesai; if(tglMulai && tglMulai > tglSelesai) tglMulai = tglSelesai; }" x-ref="endInput" /></div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-semibold text-slate-700">Pilih Pegawai <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700 cursor-pointer hover:bg-slate-100 px-3 py-1.5 rounded-lg transition-all active:scale-95">
                                <x-checkbox x-model="selectAll" @change="toggleAll" class="w-5 h-5 cursor-pointer text-primary border-slate-300 rounded focus:ring-primary" />
                                <span>Pilih Semua</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="border border-slate-200 rounded-xl max-h-[300px] overflow-y-auto bg-slate-50/50 p-2 space-y-2 shadow-inner custom-scrollbar">
                        @foreach($pegawai as $p)
                            <label class="relative flex items-center p-3 rounded-lg border-2 border-transparent bg-white shadow-sm cursor-pointer hover:border-slate-300 hover:bg-slate-50 transition-all [&:has(:checked)]:border-primary [&:has(:checked)]:bg-blue-50/50 group">
                                <input type="checkbox" name="user_ids[]" value="{{ $p->id }}" checked @change="updateSelectAll"
                                    class="user-checkbox peer sr-only">
                                
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm mr-3 group-has-[:checked]:bg-primary group-has-[:checked]:text-white transition-colors">
                                    {{ strtoupper(substr($p->nama_lengkap, 0, 2)) }}
                                </div>

                                <div class="flex-1">
                                    <div class="font-bold text-slate-800 text-sm group-has-[:checked]:text-primary">{{ $p->nama_lengkap }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $p->nik }}</div>
                                </div>

                                <div class="flex-shrink-0 ml-3 text-slate-300 group-has-[:checked]:text-primary transition-colors relative flex items-center justify-center">
                                    <div class="w-5 h-5 rounded border border-slate-300 group-has-[:checked]:bg-primary group-has-[:checked]:border-primary transition-all flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-white opacity-0 group-has-[:checked]:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-500 mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Centang pegawai yang ingin dihapus jadwalnya pada rentang tanggal di atas.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-slate-100 mt-6">
                <x-button type="button" x-data @click="$dispatch('close-modal', 'bulk-delete')" variant="secondary">Batal</x-button>
                <x-button type="button" variant="danger" onclick="confirmBulkDelete()">Ya, Hapus Terpilih</x-button>
            </div>
        </form>
    </x-modal>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.jadwalCalendar = window.jadwalCalendar || null;

        function initCalendar() {
            var calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;

            var filterKantor = document.getElementById('filter_kantor');
            var filterDivisi = document.getElementById('filter_divisi');
            var filterNama = document.getElementById('filter_nama');
            var loading = document.getElementById('loading');

            if (window.jadwalCalendar) {
                window.jadwalCalendar.destroy();
                window.jadwalCalendar = null;
            }

            window.jadwalCalendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                initialDate: new Date().toISOString().split('T')[0],
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                    list: 'Minggu'
                },
                eventDidMount: function (info) {
                    if (info.event.extendedProps.description) {
                        let contentHtml = `
                            <div class="font-bold text-slate-800 mb-1 border-b border-slate-100 pb-1">${info.event.extendedProps.nama_user}</div>
                            <div class="text-xs text-slate-500 mb-2">${info.event.extendedProps.kantor} &bull; ${info.event.extendedProps.jabatan}</div>
                            <div class="text-[11px] leading-relaxed">
                                ${info.event.extendedProps.description.replace(/\n/g, '<br>')}
                            </div>
                        `;

                        if (info.event.extendedProps.is_holiday) {
                            contentHtml = `
                                <div class="font-bold text-red-600 border-b border-red-100 pb-1 mb-1">Libur Nasional</div>
                                <div class="text-xs text-red-500">${info.event.extendedProps.keterangan}</div>
                            `;
                        }

                        tippy(info.el, {
                            content: contentHtml,
                            allowHTML: true,
                            animation: 'scale',
                            theme: 'light-border',
                            placement: 'top',
                            zIndex: 9999
                        });
                    }
                },
                eventContent: function (arg) {
                    let contentEl = document.createElement('div');

                    let classes = "bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100"; 
                    let bg = arg.event.backgroundColor;

                    if (bg === '#10b981') classes = "bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100";
                    else if (bg === '#3b82f6') classes = "bg-blue-50 text-blue-800 border-blue-200 hover:bg-blue-100";
                    else if (bg === '#f59e0b') classes = "bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100";
                    else if (bg === '#6b7280') classes = "bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200";
                    else if (bg === '#ef4444') classes = "bg-red-50 text-red-800 border-red-200 hover:bg-red-100";
                    
                    let isPoin = arg.event.extendedProps.is_poin;
                    if (isPoin) classes = "bg-orange-50 text-orange-800 border-orange-200 hover:bg-orange-100";

                    if (arg.event.extendedProps.is_holiday) {
                        contentEl.innerHTML = `
                            <div class="bg-red-50 text-red-700 px-2 py-1.5 rounded-lg border border-red-200 font-bold text-[10px] flex items-center gap-1.5 w-full truncate shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                                <span class="truncate">${arg.event.title}</span>
                            </div>
                        `;
                        return { domNodes: [contentEl] };
                    }

                    let jamMulai = arg.event.extendedProps.jam_mulai ? arg.event.extendedProps.jam_mulai.substring(0, 5) : '??:??';
                    let jamSelesai = arg.event.extendedProps.jam_selesai ? arg.event.extendedProps.jam_selesai.substring(0, 5) : '??:??';

                    if (arg.view.type === 'listWeek') {
                        contentEl.innerHTML = `
                            <div class="flex items-center gap-3 w-full py-2 px-1">
                                <div class="w-3 h-3 rounded-full shadow-sm" style="background-color: ${bg};"></div>
                                <div class="font-bold text-slate-800 text-sm whitespace-normal flex-1">
                                    ${arg.event.extendedProps.nama_user}
                                    <span class="font-normal text-xs text-slate-500 ml-2">(${arg.event.extendedProps.nama_shift})</span>
                                </div>
                                <div class="text-sm font-mono text-slate-600 font-semibold whitespace-nowrap">${jamMulai} - ${jamSelesai} ${isPoin?'⚠️':''}</div>
                                <div class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 whitespace-nowrap">${arg.event.extendedProps.kantor}</div>
                            </div>
                        `;
                    } else {
                        // Month View - Premium Badge Design
                        contentEl.innerHTML = `
                            <div class="${classes} px-2 py-1.5 rounded-lg border text-left transition-all w-full flex flex-col gap-0.5 relative overflow-hidden group">
                                ${isPoin ? '<div class="absolute right-1 top-1 text-[10px]">⚠️</div>' : ''}
                                <div class="font-bold text-[11.5px] truncate leading-tight pr-3 w-full group-hover:opacity-80 transition-opacity">
                                    ${arg.event.extendedProps.nama_user.split(' ')[0]}
                                </div>
                                <div class="text-[10px] font-mono font-medium opacity-80 tracking-tight flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    ${jamMulai} - ${jamSelesai}
                                </div>
                            </div>
                        `;
                    }

                    return { domNodes: [contentEl] };
                },
                events: function (info, successCallback, failureCallback) {
                    const shimmer = document.getElementById('calendar-shimmer');
                    const wrapper = document.getElementById('calendar-wrapper');
                    const loading = document.getElementById('loading');

                    if (wrapper && !wrapper.classList.contains('hidden')) {
                        if (loading) {
                            loading.classList.remove('opacity-0', 'pointer-events-none');
                            loading.classList.add('opacity-100');
                        }
                    }

                    console.log("Fetching Events...", info.startStr, info.endStr);

                    const params = new URLSearchParams({
                        start: info.startStr,
                        end: info.endStr,
                        filter_kantor: filterKantor ? filterKantor.value : '',
                        filter_divisi: filterDivisi ? filterDivisi.value : '',
                        filter_nama: filterNama ? filterNama.value : ''
                    });

                    fetch("{{ route('jadwal.events') }}?" + params.toString())
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (Array.isArray(data)) {
                                successCallback(data);
                            } else {
                                console.error("Data is not an array!", data);
                                failureCallback("Invalid Data Format");
                            }

                            if (shimmer && !shimmer.classList.contains('hidden')) {
                                shimmer.classList.add('hidden');
                                wrapper.classList.remove('hidden');
                                setTimeout(() => { window.jadwalCalendar.updateSize(); }, 50);
                            }

                            if (loading) {
                                loading.classList.remove('opacity-100');
                                loading.classList.add('opacity-0', 'pointer-events-none');
                            }
                        })
                        .catch(error => {
                            if (loading) {
                                loading.classList.remove('opacity-100');
                                loading.classList.add('opacity-0', 'pointer-events-none');
                            }
                            console.error("Error Fetching Events:", error);
                            Swal.fire('Error', 'Gagal memuat jadwal: ' + error.message, 'error');
                            failureCallback(error);
                        });
                },
                eventClick: function (info) {
                    if (info.event.extendedProps.is_holiday) {
                        return;
                    }
                    openEditModal(info.event);
                },
                eventDisplay: 'block',
                displayEventTime: false,
                height: 'auto',
                dayMaxEvents: 2,
                moreLinkClick: function (info) {
                    const events = info.allSegs.map(seg => seg.event);
                    
                    // Alih-alih modal popover, isi tabel harian
                    populateDailyTable(info.date, events);
                    return "function";
                },
                moreLinkText: 'lainnya',
                dateClick: function(info) {
                    const events = window.jadwalCalendar.getEvents().filter(e => {
                        return e.startStr === info.dateStr;
                    });
                    
                    populateDailyTable(info.date, events);
                }
            });

            window.jadwalCalendar.render();

            [filterKantor, filterDivisi].forEach(el => {
                if (el) el.addEventListener('change', () => window.jadwalCalendar.refetchEvents());
            });

            if (filterNama) {
                let debounceTimer;
                filterNama.addEventListener('keyup', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        if (window.jadwalCalendar) window.jadwalCalendar.refetchEvents();
                    }, 500);
                });
            }
        }

        if (!window.__jadwalEventAttached) {
            window.__jadwalEventAttached = true;

            document.addEventListener('turbo:load', function () {
                if (document.getElementById('calendar') && typeof initCalendar === 'function') {
                    initCalendar();
                }
            });

            document.addEventListener('turbo:before-cache', function () {
                if (window.jadwalCalendar) {
                    window.jadwalCalendar.destroy();
                    window.jadwalCalendar = null;
                }
            });
        }

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            if (document.getElementById('calendar') && !window.jadwalCalendar) {
                setTimeout(initCalendar, 50);
            }
        } else {
            document.addEventListener('DOMContentLoaded', function () {
                if (document.getElementById('calendar') && !window.jadwalCalendar) {
                    setTimeout(initCalendar, 50);
                }
            });
        }

        function openDetailHarian(date, events) {
            const container = document.getElementById('list-detail-harian');
            document.getElementById('detail-harian-tanggal').innerText = date.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

            container.innerHTML = '';

            const holidays = events.filter(e => e.extendedProps.is_holiday);
            if (holidays.length > 0) {
                const banner = document.createElement('div');
                banner.className = 'p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-semibold mb-2';
                banner.innerHTML = '🔴 ' + holidays.map(h => h.extendedProps.keterangan).join(', ');
                container.appendChild(banner);
            }

            const scheduleEvents = events.filter(e => !e.extendedProps.is_holiday);

            scheduleEvents.forEach(event => {
                const props = event.extendedProps;

                const item = document.createElement('div');
                item.className = 'p-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between hover:bg-slate-50 cursor-pointer transition';
                item.onclick = () => {
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'detail-harian' }));
                    openEditModal(event);
                };

                let timeHtml = '';
                if (props.is_poin) {
                    const origMulai = props.original_jam_mulai ? props.original_jam_mulai.substring(0, 5) : props.jam_mulai.substring(0, 5);
                    const origSelesai = props.original_jam_selesai ? props.original_jam_selesai.substring(0, 5) : props.jam_selesai.substring(0, 5);
                    timeHtml = `
                                            <div class="text-right">
                                                <div class="text-[10px] font-mono text-slate-400 line-through">${origMulai} - ${origSelesai}</div>
                                                <div class="text-xs font-mono font-bold text-orange-600">${props.jam_mulai.substring(0, 5)} - ${props.jam_selesai.substring(0, 5)} ⚠️</div>
                                            </div>`;
                } else {
                    timeHtml = `<div class="text-xs font-mono font-semibold text-slate-600 bg-slate-100 px-2 py-1 rounded">${props.jam_mulai.substring(0, 5)} - ${props.jam_selesai.substring(0, 5)}</div>`;
                }

                item.innerHTML = `
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full ${props.is_poin ? 'bg-orange-100 border-orange-200 text-orange-600' : 'bg-slate-100 border-slate-200 text-slate-500'} flex items-center justify-center font-bold text-xs border">
                                                ${props.nama_user.substring(0, 2).toUpperCase()}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800 text-sm">${props.nama_user}</div>
                                                <div class="text-xs text-slate-500 flex gap-1">
                                                    <span>${props.nama_shift}</span>
                                                    <span>• ${props.kantor}</span>
                                                </div>
                                            </div>
                                        </div>
                                        ${timeHtml}
                                    `;
                container.appendChild(item);
            });

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'detail-harian' }));
        }

        function populateDailyTable(date, events) {
            const tableContainer = document.getElementById('daily-table-container');
            const tableBody = document.getElementById('daily-table-body');
            const tableDateLabel = document.getElementById('table-date-label');
            
            tableDateLabel.innerText = "Jadwal: " + date.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            tableBody.innerHTML = '';
            
            const scheduleEvents = events.filter(e => !e.extendedProps.is_holiday);
            
            if (scheduleEvents.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">Tidak ada jadwal kerja pada tanggal ini.</td></tr>`;
            } else {
                scheduleEvents.forEach(event => {
                    const props = event.extendedProps;
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-slate-50 transition-colors';
                    
                    let timeHtml = `${props.jam_mulai.substring(0, 5)} - ${props.jam_selesai.substring(0, 5)}`;
                    if (props.is_poin) {
                        timeHtml = `<span class="text-orange-600 font-bold">${timeHtml} ⚠️</span>`;
                    }
                    
                    row.innerHTML = `
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 border border-slate-200">
                                    ${props.nama_user.substring(0, 2).toUpperCase()}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-800">${props.nama_user}</div>
                                    <div class="text-xs text-slate-500">${props.jabatan}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-medium px-2 py-1 rounded bg-blue-50 text-blue-700 border border-blue-100">${props.nama_shift}</span>
                        </td>
                        <td class="px-6 py-4 text-sm font-mono text-slate-600">${timeHtml}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">${props.kantor}</td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="window.jadwalEventId = '${event.id}'; openEditModalFromTable('${event.id}')" class="text-primary hover:text-primary-dark font-medium text-sm">Edit</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            }
            
            tableContainer.classList.remove('hidden');
            tableContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function openEditModalFromTable(eventId) {
            const event = window.jadwalCalendar.getEventById(eventId);
            if (event) openEditModal(event);
        }

        function openEditModal(event) {
            const props = event.extendedProps;

            document.getElementById('edit-id-jadwal').value = event.id;
            document.getElementById('edit-tanggal-raw').value = event.startStr;

            if (props.id_user) {
                document.getElementById('edit-id-user').value = props.id_user;
            } else {
                console.warn("ID User tidak ditemukan di props event!");
            }

            document.getElementById('detail-nama').innerText = props.nama_user;
            document.getElementById('detail-kantor').innerText = props.kantor;
            document.getElementById('detail-jabatan').innerText = props.jabatan;
            document.getElementById('detail-tanggal').innerText = event.start.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

            const poinContainer = document.getElementById('detail-poin-container');
            const poinList = document.getElementById('detail-poin-list');

            if (props.poin_info && props.poin_info.length > 0) {
                poinList.innerHTML = '';
                props.poin_info.forEach(info => {
                    const li = document.createElement('li');
                    li.innerText = info;
                    poinList.appendChild(li);
                });
                poinContainer.classList.remove('hidden');
            } else {
                poinContainer.classList.add('hidden');
                poinList.innerHTML = '';
            }

            document.getElementById('edit-id-shift').value = props.id_shift;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-jadwal' }));
        }

        function updateJadwal(e) {
            e.preventDefault();
            const id = document.getElementById('edit-id-jadwal').value;
            const idShift = document.getElementById('edit-id-shift').value;
            const tanggalRaw = document.getElementById('edit-tanggal-raw').value;
            const userId = document.getElementById('edit-id-user').value;

            const checkPayload = {
                _token: "{{ csrf_token() }}",
                user_ids: [userId],
                tanggal_mulai: tanggalRaw,
                tanggal_selesai: tanggalRaw
            };

            const performUpdate = () => {
                fetch("{{ url('jadwal') }}/" + id, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ id_shift: idShift })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'edit-jadwal' }));
                            if (window.jadwalCalendar) window.jadwalCalendar.refetchEvents();

                            if (data.message.includes('DIBATALKAN')) {
                                Swal.fire('Berhasil', data.message, 'warning');
                            } else {
                                Swal.fire('Berhasil', data.message, 'success');
                            }
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    });
            };

            Swal.fire({
                title: 'Memeriksa...',
                text: 'Sedang validasi poin...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch("{{ route('jadwal.check-conflicts') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify(checkPayload)
            })
                .then(res => res.json())
                .then(data => {
                    Swal.close();

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
                                performUpdate();
                            }
                        });
                    } else {
                        performUpdate();
                    }
                })
                .catch(err => {
                    console.error(err);
                    performUpdate();
                });

            return false;
        }

        function deleteJadwal() {
            const id = document.getElementById('edit-id-jadwal').value;

            Swal.fire({
                title: 'Hapus jadwal ini?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("{{ url('jadwal') }}/" + id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        }
                    })
                        .then(res => res.json())
                        .then(data => {
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'edit-jadwal' }));
                            if (window.jadwalCalendar) window.jadwalCalendar.refetchEvents();
                            Swal.fire('Terhapus!', data.message, 'success');
                        });
                }
            })
        }

        document.addEventListener('DOMContentLoaded', function() {
            const formBulk = document.querySelector('#bulk-delete');
            if (formBulk) {
                const tglMulaiB = formBulk.querySelector('input[name="tanggal_mulai"]');
                const tglSelesaiB = formBulk.querySelector('input[name="tanggal_selesai"]');
                
                if(tglMulaiB && tglSelesaiB) {
                    tglMulaiB.addEventListener('change', function() {
                        tglSelesaiB.min = this.value;
                    });
                    tglSelesaiB.addEventListener('change', function() {
                        tglMulaiB.max = this.value;
                    });
                }
            }
        });

        function confirmBulkDelete() {
            const form = document.getElementById('form-bulk-delete');
            if (!form) return;

            const checkedBoxes = form.querySelectorAll('.user-checkbox:checked');
            const tglMulai = form.querySelector('input[name="tanggal_mulai"]')?.value;
            const tglSelesai = form.querySelector('input[name="tanggal_selesai"]')?.value;

            if (checkedBoxes.length === 0) {
                Swal.fire('Peringatan', 'Pilih minimal 1 pegawai untuk dihapus jadwalnya.', 'warning');
                return;
            }
            if (!tglMulai || !tglSelesai) {
                Swal.fire('Peringatan', 'Tanggal mulai dan selesai wajib diisi.', 'warning');
                return;
            }

            const formatTgl = (d) => {
                const obj = new Date(d);
                return obj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            };

            Swal.fire({
                title: 'Konfirmasi Hapus Massal',
                html: `Anda akan menghapus jadwal <b>${checkedBoxes.length} pegawai</b> pada rentang:<br><b>${formatTgl(tglMulai)} — ${formatTgl(tglSelesai)}</b><br><br><span class="text-red-500 font-semibold">Tindakan ini tidak dapat dibatalkan!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus Sekarang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

    </script>
@endsection
