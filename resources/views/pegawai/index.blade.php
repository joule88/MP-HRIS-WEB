@extends('layouts.app')

@section('title', 'Data Karyawan')

@section('content')
    <div class="max-w-7xl mx-auto space-y-8">

        <div>
            <h1 class="text-2xl font-bold text-slate-800 mb-6">Data Karyawan</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($stats['kantor_list'] as $kantor)
                    <x-stat-card label="Kantor {{ $kantor->nama_kantor }}" value="{{ $kantor->total_pegawai }}" unit="Karyawan"
                        color="{{ $loop->first ? 'purple' : 'blue' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </x-stat-card>
                @endforeach

                <x-stat-card label="Total Pegawai" value="{{ $stats['total'] }}" unit="Karyawan" color="emerald">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </x-stat-card>

                <x-stat-card label="Status Aktif" value="{{ $stats['active'] }}" unit="User" color="orange">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </x-stat-card>
            </div>
        </div>

        <form id="filterForm" action="{{ route('pegawai.index') }}" method="GET"
            class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-4 w-full">

                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                    <div class="w-full md:w-44">
                        <x-select name="filter_jabatan" class="!mb-0 h-10" onchange="this.form.submit()">
                            <option value="">Semua Jabatan</option>
                            @foreach($allJabatan as $j)
                                <option value="{{ $j->id_jabatan }}" {{ request('filter_jabatan') == $j->id_jabatan ? 'selected' : '' }}>
                                    {{ $j->nama_jabatan }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div class="w-full md:w-44">
                        <x-select name="filter_kantor" class="!mb-0 h-10" onchange="this.form.submit()">
                            <option value="">Semua Kantor</option>
                            @foreach($allKantor as $k)
                                <option value="{{ $k->id_kantor }}" {{ request('filter_kantor') == $k->id_kantor ? 'selected' : '' }}>
                                    {{ $k->nama_kantor }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div class="w-full md:w-40">
                        <x-select name="filter_status" class="!mb-0 h-10" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('filter_status') == 'active' ? 'selected' : '' }}>Active
                            </option>
                            <option value="nonactive" {{ request('filter_status') == 'nonactive' ? 'selected' : '' }}>
                                Nonactive</option>
                        </x-select>
                    </div>

                    @if(request()->hasAny(['filter_jabatan', 'filter_kantor', 'filter_status', 'search']))
                        <a href="{{ route('pegawai.index') }}"
                            class="flex h-10 items-center px-4 bg-slate-100 text-slate-600 text-sm font-semibold rounded-lg hover:bg-slate-200 transition-all border border-slate-200">
                            Reset
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-3 w-full lg:w-auto">
                    <x-search-input name="search" :value="request('search')" placeholder="Cari nama/email..."
                        class="!mb-0 h-10" />

                    <x-button href="{{ route('pegawai.create') }}"
                        class="bg-[#130F26] text-white border-transparent h-10 flex items-center px-5 font-bold rounded-lg shadow-sm whitespace-nowrap">
                        <span class="mr-2 text-xl leading-none font-light">+</span> Tambah
                    </x-button>
                </div>

            </div>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <x-table>
                <x-slot:header>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Karyawan</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Posisi & Penempatan</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Status</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Face ID</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-center">Aksi</th>
                </x-slot:header>

                @forelse($pegawai as $p)
                    <tr class="hover:bg-slate-50 transition duration-150 border-b border-slate-50 last:border-b-0">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                @if($p->foto)
                                    <img src="{{ asset('storage/' . $p->foto) }}"
                                        class="w-12 h-12 rounded-full object-cover border border-slate-200 shadow-sm">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 border border-slate-200">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-bold text-slate-800 text-base">{{ $p->nama_lengkap }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                                        <span>{{ $p->email }}</span>
                                        <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                        <span class="font-mono text-slate-400">{{ $p->nik ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-semibold text-slate-700">{{ $p->jabatan->nama_jabatan ?? '-' }}</span>
                                <span class="text-xs text-slate-500">{{ $p->divisi->nama_divisi ?? '-' }}</span>
                                <div class="flex items-center gap-1 mt-1 text-xs text-slate-400">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    </svg>
                                    {{ $p->kantor->nama_kantor ?? '-' }}
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <x-badge color="{{ $p->status_aktif ? 'green' : 'gray' }}">
                                {{ $p->status_aktif ? 'Aktif' : 'Non-Aktif' }}
                            </x-badge>
                        </td>

                        <td class="px-6 py-4 text-center">
                            @if($p->is_face_registered)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    Terdaftar
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    Belum
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('pegawai.show', $p->id) }}"
                                    class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Lihat Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <x-button-edit href="{{ route('pegawai.edit', $p->id) }}" />
                                @if($p->status_aktif)
                                    <button type="button" onclick="confirmAction(event, 'delete-form-{{ $p->id }}', 'Karyawan akan dinonaktifkan (Resigned). Historis data akan tetap tersimpan.', '#f59e0b', 'Ya, Nonaktifkan')" class="p-2 bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200 rounded-lg transition flex items-center justify-center" title="Nonaktifkan (Resigned)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    </button>
                                @else
                                    <button type="button" onclick="confirmAction(event, 'delete-form-{{ $p->id }}', 'Data karyawan non-aktif ini akan dihapus secara PERMANEN.', '#ef4444', 'Ya, Hapus Permanen')" class="p-2 bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 rounded-lg transition flex items-center justify-center" title="Hapus Permanen">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                @endif
                                <form id="delete-form-{{ $p->id }}" action="{{ route('pegawai.destroy', $p->id) }}"
                                    method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state colspan="5" message="Tidak ada data pegawai yang ditemukan." />
                @endforelse
            </x-table>
            <x-pagination :paginator="$pegawai" />
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('js/notifications.js') }}"></script>
@endsection
