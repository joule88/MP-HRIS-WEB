@extends('layouts.app')

@section('title', 'Data Kantor')

@section('style')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css" />
    <style>
        #map-create,
        #map-edit {
            height: 300px !important;
            width: 100% !important;
            border-radius: 0.75rem;
            z-index: 1;
            border: 1px solid #e2e8f0;
        }
    </style>
@endsection

@section('script')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.js"></script>
    <script>
        window.kantorUpdateUrl = "{{ route('kantor.update', ':id') }}";
    </script>
    <script src="{{ asset('js/kantor-map.js') }}"></script>
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
                            <code
                                class="text-[11px] font-mono bg-slate-100 px-2 py-1 rounded border border-slate-200 text-slate-600">
                                        {{ number_format($k->latitude, 5) }}, {{ number_format($k->longitude, 5) }}
                                    </code>
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

    <x-modal name="create-kantor" title="Tambah Lokasi Kantor">
        <form action="{{ route('kantor.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tentukan Lokasi</label>
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
                <div class="flex-1"><x-input label="Lat" name="latitude" id="create-lat" readonly required /></div>
                <div class="flex-1"><x-input label="Long" name="longitude" id="create-long" readonly required /></div>
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

    <x-modal name="edit-kantor" title="Edit Data Kantor">
        <form id="form-edit" action="#" method="POST">
            @csrf @method('PUT')
            <div class="mb-4">
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
                <div class="flex-1"><x-input label="Lat" name="latitude" id="edit-lat" readonly required /></div>
                <div class="flex-1"><x-input label="Long" name="longitude" id="edit-long" readonly required /></div>
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
    <x-modal name="detail-kantor" title="Detail Lokasi Kantor">
        <div class="p-1 space-y-4">
            <div id="map-detail" class="h-64 rounded-xl border border-slate-200 mb-4"></div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Kantor</label>
                    <p id="detail-nama" class="text-slate-800 font-bold"></p>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tipe</label>
                    <p id="detail-tipe" class="text-slate-800"></p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Latitude</label>
                    <p id="detail-lat" class="text-slate-800 font-mono text-xs"></p>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Longitude</label>
                    <p id="detail-long" class="text-slate-800 font-mono text-xs"></p>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Radius</label>
                    <p id="detail-radius" class="text-slate-800"></p>
                </div>
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat</label>
                <p id="detail-alamat" class="text-slate-700 text-sm leading-relaxed"></p>
            </div>
            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="button" x-data @click="$dispatch('close-modal', 'detail-kantor')"
                    class="px-5 py-2 text-sm bg-slate-800 text-white rounded-xl hover:bg-slate-700 transition shadow-sm">Tutup</button>
            </div>
        </div>
    </x-modal>
@endsection
