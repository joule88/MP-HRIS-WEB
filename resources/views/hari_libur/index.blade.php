@extends('layouts.app')

@section('title', 'Data Hari Libur')

@section('style')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <style>
        .fc-event {
            cursor: default;
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
            background-color: #ffffff !important;
        }
        .fc-event-title {
            font-weight: 600;
            line-height: 1.2;
        }
        .fc-popover-body {
            max-height: 250px !important;
            overflow-y: auto !important;
        }
        #holiday-calendar {
            min-height: 550px;
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
            color: #ef4444 !important;
            font-weight: 700;
            font-size: 0.7rem;
            padding: 5px 8px;
            background-color: #fef2f2;
            border-radius: 6px;
            display: block;
            text-align: center;
            margin: 4px 6px 8px 6px;
            transition: all 0.2s;
            text-decoration: none !important;
        }
        .fc-daygrid-more-link:hover {
            background-color: #fee2e2;
            color: #dc2626 !important;
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

        <x-page-header title="Data Hari Libur" subtitle="Kelola tanggal-tanggal libur nasional atau perusahaan.">
            <div class="flex gap-2">
                <form method="GET" action="{{ route('hari-libur.index') }}" class="flex items-center gap-2">
                    <x-filter-select name="id_kantor" onchange="this.form.submit()" class="py-1.5 text-xs w-48">
                        <option value="">Semua Kantor (Global)</option>
                        @foreach($kantors as $k)
                            <option value="{{ $k->id_kantor }}" {{ request('id_kantor') == $k->id_kantor ? 'selected' : '' }}>{{ $k->nama_kantor }}</option>
                        @endforeach
                    </x-filter-select>
                </form>
                <form action="{{ route('hari-libur.sync') }}" method="POST">
                    @csrf
                    <x-button type="submit" variant="secondary" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Sinkronisasi ({{ date('Y') }})
                    </x-button>
                </form>
                
                <x-button x-data @click="$dispatch('open-modal', 'create-harilibur')" class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Hari Libur
                </x-button>
            </div>
        </x-page-header>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Kalender Libur Nasional & Perusahaan
            </h3>
            <div id="holiday-calendar" class="min-h-[450px]"></div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <x-table>
                <x-slot:header>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left w-10">No</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Tanggal</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Kantor</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Keterangan</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                </x-slot:header>

                @forelse($hariLiburs as $key => $hl)
                    <tr class="hover:bg-slate-50 transition border-b border-slate-50 last:border-b-0">
                        <td class="px-6 py-4 text-slate-500">
                            {{ $hariLiburs->firstItem() + $key }}
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-800">
                            {{ \Carbon\Carbon::parse($hl->tanggal)->translatedFormat('d F Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            {!! $hl->id_kantor ? '<span class="inline-flex px-2 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700">Khusus: ' . $hl->kantor->nama_kantor . '</span>' : '<span class="inline-flex px-2 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-600">Global</span>' !!}
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-800">
                            {{ $hl->keterangan }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <x-button-edit onclick="openEditModal(this)" data-id="{{ $hl->id }}"
                                    data-tanggal="{{ $hl->tanggal }}" data-keterangan="{{ $hl->keterangan }}" data-kantor="{{ $hl->id_kantor }}" />

                                <form id="delete-form-{{ $hl->id }}" action="{{ route('hari-libur.destroy', $hl->id) }}"
                                    method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>

                                <x-delete-button :id="$hl->id" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state colspan="4" message="Belum ada data hari libur" hint="Silakan tambahkan data baru." />
                @endforelse
            </x-table>

            <x-pagination :paginator="$hariLiburs" />
        </div>
    </div>

    <x-modal name="create-harilibur" title="Tambah Hari Libur">
        <form action="{{ route('hari-libur.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <x-input type="date" label="Tanggal Libur" name="tanggal" required />
            </div>
            <div>
                <x-select label="Kantor (Opsional)" name="id_kantor">
                    <option value="">Semua Kantor (Global)</option>
                    @foreach($kantors as $k)
                        <option value="{{ $k->id_kantor }}">{{ $k->nama_kantor }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input label="Keterangan" name="keterangan" placeholder="Contoh: Idul Fitri" required />
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <x-button type="button" x-data @click="$dispatch('close-modal', 'create-harilibur')" variant="secondary">Batal</x-button>
                <x-button type="submit">Simpan</x-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="edit-harilibur" title="Edit Hari Libur">
        <form id="form-edit" action="#" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" id="edit-id" name="id">

            <div>
                <x-input type="date" id="edit-tanggal" label="Tanggal Libur" name="tanggal" required />
            </div>

            <div>
                <x-select id="edit-kantor" label="Kantor (Opsional)" name="id_kantor">
                    <option value="">Semua Kantor (Global)</option>
                    @foreach($kantors as $k)
                        <option value="{{ $k->id_kantor }}">{{ $k->nama_kantor }}</option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-input id="edit-keterangan" label="Keterangan" name="keterangan" required />
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <x-button type="button" x-data @click="$dispatch('close-modal', 'edit-harilibur')" variant="secondary">Batal</x-button>
                <x-button type="submit" variant="warning">Update</x-button>
            </div>
        </form>
    </x-modal>

@endsection

@section('script')
    <script>
        window.openEditModal = function (btn) {
            const id = btn.dataset.id;
            const tanggal = btn.dataset.tanggal;
            const keterangan = btn.dataset.keterangan;
            const kantorId = btn.dataset.kantor;

            document.getElementById('edit-id').value = id;
            document.getElementById('edit-tanggal').value = tanggal;
            document.getElementById('edit-keterangan').value = keterangan;
            document.getElementById('edit-kantor').value = kantorId || '';

            let form = document.getElementById('form-edit');
            let baseUrl = "{{ route('hari-libur.update', ':id') }}";
            form.action = baseUrl.replace(':id', id);

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-harilibur' }));
        }

        window.holidayCalendar = window.holidayCalendar || null;

        function initHolidayCalendar() {
            var calendarEl = document.getElementById('holiday-calendar');
            if (!calendarEl) return;

            if (window.holidayCalendar) {
                window.holidayCalendar.destroy();
                window.holidayCalendar = null;
            }

            // Paksa destroy jadwal calendar jika masih hidup dari halaman penjadwalan
            if (window.jadwalCalendar) {
                window.jadwalCalendar.destroy();
                window.jadwalCalendar = null;
            }

            const rawData = @json($hariLiburs->items() ?? $hariLiburs);

            const calendarEvents = rawData.map(function(hl) {
                return {
                    title: hl.keterangan,
                    start: hl.tanggal,
                    allDay: true,
                    color: '#ef4444',
                    display: 'block'
                };
            });

            window.holidayCalendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                },
                events: calendarEvents,
                height: 'auto',
                displayEventTime: false,
                dayMaxEvents: 3,
                moreLinkText: 'lainnya',
                moreLinkClick: 'popover',
                eventContent: function(arg) {
                    let contentEl = document.createElement('div');
                    contentEl.innerHTML = `
                        <div class="bg-red-50 text-red-700 px-2 py-1.5 rounded-lg border border-red-200 font-bold text-[10px] flex items-center gap-1.5 w-full truncate shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                            <span class="truncate">${arg.event.title}</span>
                        </div>
                    `;
                    return { domNodes: [contentEl] };
                }
            });

            window.holidayCalendar.render();
        }

        if (!window.__holidayEventAttached) {
            window.__holidayEventAttached = true;

            document.addEventListener('turbo:load', function () {
                if (document.getElementById('holiday-calendar') && typeof initHolidayCalendar === 'function') {
                    initHolidayCalendar();
                }
            });

            document.addEventListener('turbo:before-cache', function () {
                if (window.holidayCalendar) {
                    window.holidayCalendar.destroy();
                    window.holidayCalendar = null;
                }
            });
        }

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            if (document.getElementById('holiday-calendar') && !window.holidayCalendar) {
                setTimeout(initHolidayCalendar, 50);
            }
        } else {
            document.addEventListener('DOMContentLoaded', function () {
                if (document.getElementById('holiday-calendar') && !window.holidayCalendar) {
                    setTimeout(initHolidayCalendar, 50);
                }
            });
        }
    </script>
@endsection
