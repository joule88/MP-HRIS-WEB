@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Manajemen Hak Akses</h1>
        <p class="text-sm text-slate-500">Konfigurasi izin spesifik untuk setiap peran pengguna.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Tambah Izin Baru</h3>
                <form action="{{ route('permission.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Nama Izin</label>
                        <input type="text" name="nama_permission" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-primary/20 outline-none transition-all" placeholder="Contoh: Edit Pegawai" required>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-primary text-white font-bold rounded-xl hover:bg-slate-800 transition-all shadow-md">
                        + Buat Izin
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($roles as $role)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-bold text-slate-800">{{ $role->nama_role }}</h3>
                        <span class="text-[10px] bg-primary/10 text-primary px-2 py-1 rounded-md font-bold uppercase">Role ID: {{ $role->id_role }}</span>
                    </div>
                    <form action="{{ route('permission.sync', $role->id_role) }}" method="POST" class="p-6 flex-1 flex flex-col">
                        @csrf
                        <div class="grid grid-cols-1 gap-3 flex-1 mb-6">
                            @foreach($permissions as $perm)
                            <div class="p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition-all flex items-start">
                                <x-checkbox name="permissions[]" value="{{ $perm->id_permission }}" 
                                    :checked="$role->permissions->contains($perm->id_permission)" />
                                <div class="ml-3 cursor-pointer" onclick="this.previousElementSibling.querySelector('input').click()">
                                    <p class="text-sm font-semibold text-slate-700 hover:text-primary transition-colors">{{ $perm->nama_permission }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">{{ $perm->slug }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="submit" class="w-full py-2 bg-slate-800 text-white text-sm font-bold rounded-xl hover:bg-primary transition-all shadow-sm">
                            Update Hak Akses
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
