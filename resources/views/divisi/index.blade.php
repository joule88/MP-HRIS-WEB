@extends('layouts.app')

@section('title', 'Data Divisi')

@section('content')
    <div class="space-y-6">

        <x-page-header title="Data Divisi" subtitle="Kelola daftar divisi perusahaan.">
            
            <button x-data @click="$dispatch('open-modal', 'create-divisi')"
                class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary/90 transition shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Divisi
            </button>
        </x-page-header>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <x-table>
                <x-slot:header>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left w-10">No</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Nama Divisi
                    </th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                </x-slot:header>

                @forelse($divisi as $key => $d)
                    <tr class="hover:bg-slate-50 transition border-b border-slate-50 last:border-b-0">

                        <td class="px-6 py-4 text-slate-500">
                            {{ $divisi->firstItem() + $key }}
                        </td>

                        <td class="px-6 py-4 font-medium text-slate-800">
                            {{ $d->nama_divisi }}
                        </td>

                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">

                                <x-button-edit onclick="openEditModal(this)" data-id="{{ $d->id_divisi }}"
                                    data-nama="{{ $d->nama_divisi }}" />

                                <form id="delete-form-{{ $d->id_divisi }}" action="{{ route('divisi.destroy', $d->id_divisi) }}"
                                    method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>

                                <x-delete-button :id="$d->id_divisi" />

                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state colspan="3" message="Belum ada data divisi" hint="Silakan tambahkan divisi baru." />
                @endforelse
            </x-table>

            <x-pagination :paginator="$divisi" />
        </div>
    </div>

    <x-modal name="create-divisi" title="Tambah Divisi Baru">
        <form action="{{ route('divisi.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <x-input label="Nama Divisi" name="nama_divisi" placeholder="Contoh: Information Technology" required />
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" x-data @click="$dispatch('close-modal', 'create-divisi')"
                    class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">Batal</button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-xl hover:bg-primary/90">Simpan</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="edit-divisi" title="Edit Divisi">
        <form id="form-edit" action="#" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" id="edit-id" name="id">

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Divisi</label>
                
                <input type="text" id="edit-nama" name="nama_divisi" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-[#130F26] outline-none transition">
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" x-data @click="$dispatch('close-modal', 'edit-divisi')"
                    class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">Batal</button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-amber-500 rounded-xl hover:bg-amber-600">Update</button>
            </div>
        </form>
    </x-modal>

    <script>
        window.openEditModal = function (btn) {
            const id = btn.dataset.id;
            const nama = btn.dataset.nama;

            document.getElementById('edit-id').value = id;
            document.getElementById('edit-nama').value = nama;

            let form = document.getElementById('form-edit');
            let baseUrl = "{{ route('divisi.update', ':id') }}";
            form.action = baseUrl.replace(':id', id);

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-divisi' }));
        }
    </script>

@endsection
