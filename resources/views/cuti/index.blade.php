@extends('layouts.app')

@section('title', 'Manajemen Sisa Cuti')

@section('content')
<div class="space-y-6">

    <x-page-header title="Manajemen Sisa Cuti" subtitle="Atur kuota cuti tahunan pegawai" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form action="{{ route('cuti.index') }}" method="GET" class="w-full md:w-1/3">
                <x-search-input name="search" :value="$search" placeholder="Cari NIK atau Nama..." class="!mb-0 h-10" />
            </form>
            
            <button x-data @click="$dispatch('open-modal', 'reset-cuti')" class="inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-lg text-white bg-slate-800 hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-colors shadow-sm text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Reset Massal (Awal Tahun)
            </button>
        </div>

        <x-table>
            <x-slot:header>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Pegawai</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Sisa Cuti</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
            </x-slot:header>

            @forelse ($pegawai as $p)
            <tr class="hover:bg-slate-50 transition-colors group">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <x-avatar :name="$p->nama_lengkap" class="mr-3" />
                        <div>
                            <div class="font-semibold text-slate-900">{{ $p->nama_lengkap }}</div>
                            <div class="text-xs text-slate-500">{{ $p->nik ?? '-' }} • {{ $p->jabatan->nama_jabatan ?? '-' }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $p->sisa_cuti > 3 ? 'bg-emerald-100 text-emerald-800' : ($p->sisa_cuti > 0 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                        {{ $p->sisa_cuti }} Hari
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <button onclick="openEditModal({{ $p->id }}, '{{ $p->nama_lengkap }}', {{ $p->sisa_cuti }})" class="inline-flex items-center text-primary hover:text-blue-800 bg-blue-50/50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit
                    </button>
                </td>
            </tr>
            @empty
            <x-empty-state colspan="3" message="Pencarian tidak menemukan hasil." />
            @endforelse
        </x-table>
        
        <x-pagination :paginator="$pegawai" />
    </div>
</div>

<x-modal name="edit-cuti" title="Edit Sisa Cuti">
    <form id="formEditCuti" method="POST" action="" class="space-y-4">
        @csrf
        @method('PUT')
        
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Pegawai</label>
            <input type="text" id="editNama" disabled class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-slate-600 font-medium">
        </div>
        
        <div>
            <x-input label="Jumlah Sisa Cuti (Hari)" name="sisa_cuti" type="number" id="editSisa" required min="0" placeholder="0" />
        </div>
        
        <div class="p-3 bg-blue-50 text-blue-800 rounded-lg text-xs leading-relaxed border border-blue-100">
            <span class="font-bold block mb-1">Catatan:</span>
            Perubahan ini langsung memengaruhi jatah cuti pegawai yang dapat diajukan di aplikasi mobile. Pastikan jumlahnya tepat.
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <button type="button" x-data @click="$dispatch('close-modal', 'edit-cuti')"
                class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">Batal</button>
            <button type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-xl hover:bg-primary/90">Simpan Perubahan</button>
        </div>
    </form>
</x-modal>

<x-modal name="reset-cuti" title="Reset Cuti Massal">
    <form method="POST" action="{{ route('cuti.reset') }}" class="space-y-4">
        @csrf
        
        <p class="text-sm text-slate-600">
            Fungsi ini biasanya digunakan di awal tahun untuk memberikan jatah cuti baru ke <strong>seluruh pegawai</strong>. Apakah Anda yakin ingin menetapkan ulang sisa cuti massal?
        </p>
        
        <div>
            <x-input label="Jatah Cuti Baru (Hari)" name="jumlah_hari" type="number" required min="1" value="12" />
        </div>
        
        <div class="p-3 bg-amber-50 text-amber-800 rounded-lg text-xs leading-relaxed border border-amber-200 flex items-start">
            <svg class="w-5 h-5 mr-2 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
                <span class="font-bold block mb-0.5">Peringatan:</span>
                Aksi ini <strong>menimpa dan menghilangkan</strong> semua sisa cuti pegawai sebelumnya tanpa pandang bulu. Aksi ini tidak bisa dibatalkan!
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Ketik <span class="font-mono text-rose-600 bg-rose-50 px-1 rounded">RESET</span> untuk mengkonfirmasi
            </label>
            <input type="text" id="konfirmasiReset" placeholder="Ketik RESET di sini..." 
                oninput="toggleResetBtn(this.value)"
                class="block w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-rose-500 focus:border-rose-500 transition-colors">
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <button type="button" x-data @click="$dispatch('close-modal', 'reset-cuti')"
                class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">Batal</button>
            <button type="submit" id="btnResetSubmit" disabled
                class="px-4 py-2 text-sm font-medium text-white bg-rose-600 rounded-xl hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Ya, Reset Sekarang</button>
        </div>
    </form>
</x-modal>
@endsection

@section('script')
<script>
    function toggleResetBtn(value) {
        const btn = document.getElementById('btnResetSubmit');
        btn.disabled = value !== 'RESET';
    }

    function openEditModal(id, nama, sisa) {
        document.getElementById('editNama').value = nama;
        document.getElementById('editSisa').value = sisa;

        let form = document.getElementById('formEditCuti');
        let baseUrl = "{{ route('cuti.update', ':id') }}";
        form.action = baseUrl.replace(':id', id);

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-cuti' }));
    }
</script>
@endsection
