@extends('layouts.app')

@section('title', 'Data Kantor')

@section('style')
    <style>
        #map-create,
        #map-edit,
        #map-detail {
            height: 300px !important;
            width: 100% !important;
            border-radius: 0.75rem;
            z-index: 1;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .map-search-wrapper {
            position: relative;
            margin-bottom: 10px;
        }
        .map-search-wrapper svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: #94a3b8;
            pointer-events: none;
            z-index: 2;
        }
        .map-search-input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 14px;
            outline: none;
            transition: all 0.25s ease;
            background: #f8fafc;
            color: #334155;
        }
        .map-search-input::placeholder {
            color: #94a3b8;
        }
        .map-search-input:focus {
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .pac-container {
            z-index: 99999 !important;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            margin-top: 4px;
            overflow: hidden;
            font-family: inherit;
        }
        .pac-item {
            padding: 10px 14px;
            cursor: pointer;
            border-top: 1px solid #f1f5f9;
            font-size: 13px;
            line-height: 1.5;
        }
        .pac-item:first-child {
            border-top: none;
        }
        .pac-item:hover {
            background: #f0f9ff;
        }
        .pac-item-selected {
            background: #eff6ff;
        }
        .pac-icon {
            margin-right: 8px;
        }
        .pac-item-query {
            font-size: 13px;
            color: #1e293b;
            font-weight: 500;
        }
        .map-section-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 10px;
        }
        .map-section-label svg {
            width: 16px;
            height: 16px;
            color: #3b82f6;
        }
        .coord-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-family: 'JetBrains Mono', 'Fira Code', ui-monospace, monospace;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 8px;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border: 1px solid #e2e8f0;
            color: #475569;
        }
        .coord-chip svg {
            width: 12px;
            height: 12px;
            color: #3b82f6;
        }
        .detail-info-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 14px 16px;
            border: 1px solid #f1f5f9;
        }
        .detail-info-label {
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .detail-info-value {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }
    </style>
@endsection

@section('script')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places"></script>
    <script>
        window.kantorUpdateUrl = "{{ route('kantor.update', ':id') }}";
    </script>
    <script src="{{ asset('js/kantor-map.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <div class="space-y-6">
        <x-page-header title="Data Kantor" subtitle="Kelola lokasi kantor, koordinat, dan radius absensi.">
            <button x-data @click="$dispatch('open-modal', 'create-kantor')"
                class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary/90 transition shadow-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Kantor
            </button>
        </x-page-header>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <x-table>
                <x-slot:header>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-left">Nama Kantor</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-left">Koordinat</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Radius</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
                </x-slot:header>

                @forelse($kantor as $k)
                    <tr class="hover:bg-slate-50 border-b border-slate-50 last:border-b-0">
                        <td class="px-6 py-4 text-left">
                            <div class="font-bold text-slate-800">{{ $k->nama_kantor }}</div>
                            <div class="mt-1">
                                <x-badge color="{{ $k->tipe == 'Pusat' ? 'navy' : 'gray' }}">
                                    {{ $k->tipe }}
                                </x-badge>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-left">
                            <span class="coord-chip">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                {{ number_format($k->latitude, 5) }}, {{ number_format($k->longitude, 5) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <x-badge color="blue">
                                {{ $k->radius }} m
                            </x-badge>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="openDetailModal(this)" 
                                    data-id="{{ $k->id_kantor }}"
                                    data-nama="{{ $k->nama_kantor }}" 
                                    data-tipe="{{ $k->tipe }}" 
                                    data-alamat="{{ $k->alamat }}"
                                    data-lat="{{ $k->latitude }}" 
                                    data-long="{{ $k->longitude }}"
                                    data-radius="{{ $k->radius }}"
                                    class="p-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition"
                                    title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <x-button-edit onclick="openEditModal(this)" data-id="{{ $k->id_kantor }}"
                                    data-nama="{{ $k->nama_kantor }}" data-tipe="{{ $k->tipe }}" data-alamat="{{ $k->alamat }}"
                                    data-lat="{{ $k->latitude }}" data-long="{{ $k->longitude }}"
                                    data-radius="{{ $k->radius }}" />

                                <form id="delete-form-{{ $k->id_kantor }}" action="{{ route('kantor.destroy', $k->id_kantor) }}" method="POST" class="hidden">
                                    @csrf 
                                    @method('DELETE')
                                </form>
                                <x-delete-button :id="$k->id_kantor" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state colspan="4" message="Belum ada data kantor" />
                @endforelse
            </x-table>
            <x-pagination :paginator="$kantor" />
        </div>
    </div>

    {{-- Modal Tambah Kantor --}}
    <x-modal name="create-kantor" title="Tambah Lokasi Kantor">
        <form action="{{ route('kantor.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <div class="map-section-label">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    Tentukan Lokasi
                </div>
                <div class="map-search-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" id="search-create" class="map-search-input" placeholder="Cari lokasi... (contoh: Jl. Merdeka, Malang)" autocomplete="off">
                </div>
                <div id="map-create"></div>
            </div>
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1"><x-input label="Nama Kantor" name="nama_kantor" placeholder="Cth: Cabang Malang" required /></div>
                <div class="flex-1"><x-select label="Tipe" name="tipe" required>
                    <option value="Cabang">Cabang</option>
                    <option value="Pusat">Pusat</option>
                </x-select></div>
            </div>
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1"><x-input label="Latitude" name="latitude" id="create-lat" readonly required /></div>
                <div class="flex-1"><x-input label="Longitude" name="longitude" id="create-long" readonly required /></div>
                <div class="w-full md:w-32"><x-input type="number" label="Radius (m)" name="radius" id="create-radius" value="50" required /></div>
            </div>
            <x-textarea label="Alamat" name="alamat" rows="2" />
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 mt-2">
                <button type="button" x-data @click="$dispatch('close-modal', 'create-kantor')"
                    class="px-4 py-2 text-sm border rounded-xl hover:bg-slate-50">Batal</button>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-primary text-white rounded-xl hover:bg-primary/90">Simpan</button>
            </div>
        </form>
    </x-modal>

    {{-- Modal Edit Kantor --}}
    <x-modal name="edit-kantor" title="Edit Data Kantor">
        <form id="form-edit" action="#" method="POST">
            @csrf @method('PUT')
            <div class="mb-4">
                <div class="map-search-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" id="search-edit" class="map-search-input" placeholder="Cari lokasi..." autocomplete="off">
                </div>
                <div id="map-edit"></div>
            </div>
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1"><x-input label="Nama" name="nama_kantor" id="edit-nama" required /></div>
                <div class="flex-1"><x-select label="Tipe" name="tipe" id="edit-tipe" required>
                    <option value="Cabang">Cabang</option>
                    <option value="Pusat">Pusat</option>
                </x-select></div>
            </div>
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1"><x-input label="Latitude" name="latitude" id="edit-lat" readonly required /></div>
                <div class="flex-1"><x-input label="Longitude" name="longitude" id="edit-long" readonly required /></div>
                <div class="w-full md:w-32"><x-input type="number" label="Radius (m)" name="radius" id="edit-radius" required /></div>
            </div>
            <x-textarea label="Alamat" name="alamat" id="edit-alamat" rows="2" />
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 mt-2">
                <button type="button" x-data @click="$dispatch('close-modal', 'edit-kantor')"
                    class="px-4 py-2 text-sm border rounded-xl hover:bg-slate-50">Batal</button>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-amber-500 text-white rounded-xl hover:bg-amber-600">Update</button>
            </div>
        </form>
    </x-modal>

    {{-- Modal Detail Kantor --}}
    <x-modal name="detail-kantor" title="Detail Lokasi Kantor">
        <div class="space-y-4">
            <div id="map-detail" class="rounded-xl border border-slate-200"></div>

            <div class="grid grid-cols-2 gap-3">
                <div class="detail-info-card">
                    <div class="detail-info-label">Nama Kantor</div>
                    <p id="detail-nama" class="detail-info-value"></p>
                </div>
                <div class="detail-info-card">
                    <div class="detail-info-label">Tipe Kantor</div>
                    <p id="detail-tipe" class="detail-info-value"></p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="detail-info-card">
                    <div class="detail-info-label">Latitude</div>
                    <p id="detail-lat" class="detail-info-value font-mono text-xs"></p>
                </div>
                <div class="detail-info-card">
                    <div class="detail-info-label">Longitude</div>
                    <p id="detail-long" class="detail-info-value font-mono text-xs"></p>
                </div>
                <div class="detail-info-card">
                    <div class="detail-info-label">Radius</div>
                    <p id="detail-radius" class="detail-info-value text-blue-600"></p>
                </div>
            </div>

            <div class="detail-info-card">
                <div class="detail-info-label">Alamat Lengkap</div>
                <p id="detail-alamat" class="text-slate-700 text-sm leading-relaxed mt-1"></p>
            </div>

            <div class="flex justify-end pt-3 border-t border-slate-100">
                <button type="button" x-data @click="$dispatch('close-modal', 'detail-kantor')"
                    class="px-5 py-2 text-sm bg-slate-800 text-white rounded-xl hover:bg-slate-700 transition shadow-sm">Tutup</button>
            </div>
        </div>
    </x-modal>
@endsection
