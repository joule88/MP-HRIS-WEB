@extends('layouts.app')

@section('title', 'Master Shift Kerja')

@section('content')
    <div class="space-y-6">

        <x-page-header title="Shift Kerja" subtitle="Kelola jam kerja: Shift Pagi, Siang, Malam, dll.">
            
            <form action="{{ route('shift.index') }}" method="GET" class="relative w-full md:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari shift..."
                    class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary text-sm transition shadow-sm">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </form>

            <button x-data @click="$dispatch('open-modal', 'create-shift')"
                class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition text-sm font-medium shadow-lg shadow-primary/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Shift
            </button>
        </x-page-header>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase w-16">No</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Nama Shift</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase">Jam Masuk</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase">Jam Pulang</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase w-32">Terjadwal</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase w-32">Aksi</th>
                </x-slot>

                @forelse($shifts as $index => $shift)
                    <tr class="hover:bg-slate-50 transition border-b border-slate-50 last:border-b-0">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $shifts->firstItem() + $index }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-600 border border-indigo-100">{{ $shift->nama_shift }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <code
                                class="text-sm font-mono bg-green-50 text-green-700 px-2 py-1 rounded">{{ \Carbon\Carbon::parse($shift->jam_mulai)->format('H:i') }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <code
                                class="text-sm font-mono bg-red-50 text-red-700 px-2 py-1 rounded">{{ \Carbon\Carbon::parse($shift->jam_selesai)->format('H:i') }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-600">
                            {{ $shift->jadwal_kerja_count ?? 0 }} Jadwal
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                
                                <x-button-edit onclick="openEditModal(this)" data-id="{{ $shift->id_shift }}"
                                    data-nama="{{ $shift->nama_shift }}"
                                    data-mulai="{{ \Carbon\Carbon::parse($shift->jam_mulai)->format('H:i') }}"
                                    data-selesai="{{ \Carbon\Carbon::parse($shift->jam_selesai)->format('H:i') }}" />

                                <x-delete-button :id="$shift->id_shift" />
                                <form id="delete-form-{{ $shift->id_shift }}"
                                    action="{{ route('shift.destroy', $shift->id_shift) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state colspan="6" message="Belum ada data shift" hint="Silakan tambahkan shift kerja baru." />
                @endforelse
            </x-table>
            <x-pagination :paginator="$shifts" />
        </div>
    </div>

    <x-modal name="create-shift" title="Tambah Shift Baru">
        <form action="{{ route('shift.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <x-input label="Nama Shift" name="nama_shift" placeholder="Contoh: Shift Pagi" required />
                <div class="grid grid-cols-2 gap-4">
                    <x-input type="time" label="Jam Masuk" name="jam_mulai" required />
                    <x-input type="time" label="Jam Pulang" name="jam_selesai" required />
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 mt-4">
                <button type="button" x-data @click="$dispatch('close-modal', 'create-shift')"
                    class="px-4 py-2 text-sm border rounded-xl hover:bg-slate-50">Batal</button>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-primary text-white rounded-xl hover:bg-primary/90">Simpan</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="edit-shift" title="Edit Shift">
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <x-input label="Nama Shift" name="nama_shift" id="edit_nama_shift" required />
                <div class="grid grid-cols-2 gap-4">
                    <x-input type="time" label="Jam Masuk" name="jam_mulai" id="edit_jam_mulai" required />
                    <x-input type="time" label="Jam Pulang" name="jam_selesai" id="edit_jam_selesai" required />
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 mt-4">
                <button type="button" x-data @click="$dispatch('close-modal', 'edit-shift')"
                    class="px-4 py-2 text-sm border rounded-xl hover:bg-slate-50">Batal</button>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-amber-500 text-white rounded-xl hover:bg-amber-600">Update</button>
            </div>
        </form>
    </x-modal>
@endsection

@section('script')
    <script>
        function openEditModal(button) {
            const id = button.getAttribute('data-id');
            const nama = button.getAttribute('data-nama');
            const mulai = button.getAttribute('data-mulai');
            const selesai = button.getAttribute('data-selesai');

            document.getElementById('edit_nama_shift').value = nama;
            document.getElementById('edit_jam_mulai').value = mulai;
            document.getElementById('edit_jam_selesai').value = selesai;

            const form = document.getElementById('editForm');
            form.action = "{{ url('shift') }}/" + id;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-shift' }));
        }
    </script>
@endsection
