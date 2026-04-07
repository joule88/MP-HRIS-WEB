@extends('layouts.app')

@section('title', 'Manajemen Role')

@section('content')
    <div class="space-y-6">

        <x-page-header title="Daftar Role" subtitle="Kelola hak akses dan peran pengguna.">
            
            <form action="{{ route('role.index') }}" method="GET" class="relative w-full md:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari role..."
                    class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary text-sm transition shadow-sm">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </form>

            <button x-data @click="$dispatch('open-modal', 'create-role')"
                class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition text-sm font-medium shadow-lg shadow-primary/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah
            </button>
        </x-page-header>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase w-16">No</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Nama Role</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase w-48">Jumlah User</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase w-32">Aksi</th>
                </x-slot>

                @forelse($roles as $index => $role)
                    @php
                        $roleKritis = in_array(strtolower($role->nama_role), \App\Http\Controllers\RoleController::ROLE_KRITIS);
                    @endphp
                    <tr class="hover:bg-slate-50 transition border-b border-slate-50 last:border-b-0">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $roles->firstItem() + $index }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <x-badge color="blue">{{ $role->nama_role }}</x-badge>
                                @if($roleKritis)
                                    <span class="text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">Sistem</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-600">{{ $role->users_count }}
                            Orang</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                @if($roleKritis)
                                    <span class="px-3 py-1.5 text-xs text-slate-400 bg-slate-50 border border-slate-200 rounded-lg cursor-not-allowed" title="Role sistem tidak dapat diubah">
                                        Terkunci
                                    </span>
                                @else
                                    <x-button-edit onclick="openEditModal(this)" 
                                        data-id="{{ $role->id_role }}"
                                        data-nama="{{ $role->nama_role }}"
                                        data-permissions="{{ json_encode($role->permissions->pluck('id_permission')) }}" />

                                    <x-delete-button :id="$role->id_role" />
                                    <form id="delete-form-{{ $role->id_role }}" action="{{ route('role.destroy', $role->id_role) }}"
                                        method="POST" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state colspan="4" message="Belum ada data role" hint="Silakan tambahkan role baru." />
                @endforelse
            </x-table>
            <x-pagination :paginator="$roles" />
        </div>
    </div>

    <x-modal name="create-role" title="Tambah Role Baru">
        <form action="{{ route('role.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <x-input label="Nama Role" name="nama_role" placeholder="Contoh: Administrator" required />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Hak Akses (Permissions)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 p-4 bg-slate-50 rounded-xl border border-slate-200 max-h-60 overflow-y-auto custom-scrollbar">
                    @foreach($allPermissions as $permission)
                        <div class="p-2 hover:bg-white rounded-lg transition-colors">
                            <x-checkbox name="id_permissions[]" value="{{ $permission->id_permission }}" label="{{ $permission->nama_permission }}" />
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 mt-2">
                <button type="button" x-data @click="$dispatch('close-modal', 'create-role')"
                    class="px-4 py-2 text-sm border rounded-xl hover:bg-slate-50">Batal</button>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-primary text-white rounded-xl hover:bg-primary/90">Simpan</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="edit-role" title="Edit Role">
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="mb-4">
                <x-input label="Nama Role" name="nama_role" id="edit_nama_role" required />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Hak Akses (Permissions)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 p-4 bg-slate-50 rounded-xl border border-slate-200 max-h-60 overflow-y-auto custom-scrollbar">
                    @foreach($allPermissions as $permission)
                        <div class="p-2 hover:bg-white rounded-lg transition-colors">
                            <x-checkbox name="id_permissions[]" value="{{ $permission->id_permission }}" class="edit-permission-checkbox" label="{{ $permission->nama_permission }}" />
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 mt-2">
                <button type="button" x-data @click="$dispatch('close-modal', 'edit-role')"
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
            const permissions = JSON.parse(button.getAttribute('data-permissions') || '[]');
            
            document.getElementById('edit_nama_role').value = nama;
            
            const checkboxes = document.querySelectorAll('.edit-permission-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = permissions.includes(parseInt(cb.value));
            });

            const form = document.getElementById('editForm');
            form.action = "{{ url('role') }}/" + id;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-role' }));
        }
    </script>
@endsection
