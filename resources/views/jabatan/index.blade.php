@extends('layouts.app')

@section('title', 'Data Jabatan')

@section('script')
    <script>
        window.openEditModal = function (btn) {
            const id = btn.dataset.id;
            const nama = btn.dataset.nama;

            document.getElementById('edit-id').value = id;
            document.getElementById('edit-nama').value = nama;

            let form = document.getElementById('form-edit');
            let baseUrl = "{{ route('jabatan.update', ':id') }}";
            form.action = baseUrl.replace(':id', id);

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-jabatan' }));
        }
    </script>
@endsection

@section('content')
    <div class="space-y-6">

        <x-page-header title="Data Jabatan" subtitle="Kelola daftar posisi dan jabatan pegawai.">
            <button x-data @click="$dispatch('open-modal', 'create-jabatan')"
                class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary/90 transition shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Jabatan
            </button>
        </x-page-header>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <x-table>
                <x-slot:header>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left w-10">No</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Nama Jabatan
                    </th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                </x-slot:header>

                @forelse($jabatan as $key => $j)
                    <tr class="hover:bg-slate-50 transition border-b border-slate-50 last:border-b-0">
                        <td class="px-6 py-4 text-slate-500">
                            {{ $jabatan->firstItem() + $key }}
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-800">
                            {{ $j->nama_jabatan }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">

                                <x-button-edit onclick="openEditModal(this)" data-id="{{ $j->id_jabatan }}"
                                    data-nama="{{ $j->nama_jabatan }}" />

                                <form id="delete-form-{{ $j->id_jabatan }}"
                                    action="{{ route('jabatan.destroy', $j->id_jabatan) }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>

                                <x-delete-button :id="$j->id_jabatan" />

                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state colspan="3" message="Belum ada data jabatan" hint="Silakan tambahkan jabatan baru." />
                @endforelse
            </x-table>
            <x-pagination :paginator="$jabatan" />
        </div>
    </div>

    <x-modal name="create-jabatan" title="Tambah Jabatan Baru">
        <form action="{{ route('jabatan.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <x-input label="Nama Jabatan" name="nama_jabatan" placeholder="Contoh: Senior Developer" required />
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" x-data @click="$dispatch('close-modal', 'create-jabatan')"
                    class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">Batal</button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-xl hover:bg-primary/90">Simpan</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="edit-jabatan" title="Edit Jabatan">
        <form id="form-edit" action="#" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" id="edit-id" name="id">

            <x-input label="Nama Jabatan" name="nama_jabatan" id="edit-nama" required />

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" x-data @click="$dispatch('close-modal', 'edit-jabatan')"
                    class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">Batal</button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-amber-500 rounded-xl hover:bg-amber-600">Update</button>
            </div>
        </form>
    </x-modal>



@endsection
