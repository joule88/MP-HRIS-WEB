@extends('layouts.app')

@section('title', 'Manajemen Sisa Cuti')

@section('content')
<div class="space-y-6">

    <x-page-header title="Manajemen Sisa Cuti" subtitle="Atur kuota cuti tahunan pegawai" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form action="{{ route('cuti.index') }}" method="GET" class="w-full md:w-1/3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari NIK atau Nama..." 
                        class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg focus:ring-primary focus:border-primary sm:text-sm transition-colors">
                </div>
            </form>
            
            <button onclick="toggleModal('modalReset')" class="inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-lg text-white bg-slate-800 hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-colors shadow-sm text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Reset Massal (Awal Tahun)
            </button>
        </div>

        <x-table>
            <x-slot name="header">
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Pegawai</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Sisa Cuti</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
            </x-slot>

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
        
        @if($pegawai->hasPages())
        <div class="border-t border-slate-100 p-4">
            {{ $pegawai->links() }}
        </div>
        @endif
    </div>
</div>

<div id="modalEdit" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500/50 transition-opacity" aria-hidden="true" onclick="toggleModal('modalEdit')"></div>
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md w-full border border-slate-200">
            
            <form id="formEditCuti" method="POST" action="">
                @csrf
                @method('PUT')
                
                <div class="bg-white px-6 pt-6 pb-6">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-xl font-bold text-slate-900" id="modal-title">Edit Sisa Cuti</h3>
                        <button type="button" onclick="toggleModal('modalEdit')" class="text-slate-400 hover:text-slate-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
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
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse border-t border-slate-100 gap-2">
                    <x-button type="submit" variant="primary" id="submitBtnEdit" class="w-full sm:w-auto">
                        Simpan Perubahan
                    </x-button>
                    <button type="button" class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:text-sm" onclick="toggleModal('modalEdit')">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modalReset" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500/50 transition-opacity" aria-hidden="true" onclick="toggleModal('modalReset')"></div>
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full border border-slate-200">
            
            <form method="POST" action="{{ route('cuti.reset') }}">
                @csrf
                
                <div class="bg-white px-6 pt-6 pb-6">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-slate-100 mr-3">
                                <svg class="h-5 w-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900" id="modal-title">Reset Cuti Massal</h3>
                        </div>
                        <button type="button" onclick="toggleModal('modalReset')" class="text-slate-400 hover:text-slate-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
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
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse border-t border-slate-100 gap-2">
                    <button type="submit" id="btnResetSubmit" disabled
                        class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-rose-600 text-base font-medium text-white hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-600 sm:text-sm transition-colors">
                        <span id="submitBtnReset">Ya, Reset Sekarang</span>
                    </button>
                    <button type="button" class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:text-sm" onclick="toggleModal('modalReset')">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function toggleModal(modalID) {
        document.getElementById(modalID).classList.toggle("hidden");

        // Reset field konfirmasi reset saat modal ditutup
        if (modalID === 'modalReset') {
            const konfirmasi = document.getElementById('konfirmasiReset');
            const btn = document.getElementById('btnResetSubmit');
            if (konfirmasi) konfirmasi.value = '';
            if (btn) btn.disabled = true;
        }
    }

    function toggleResetBtn(value) {
        const btn = document.getElementById('btnResetSubmit');
        btn.disabled = value !== 'RESET';
    }

    function openEditModal(id, nama, sisa) {
        document.getElementById('editNama').value = nama;
        document.getElementById('editSisa').value = sisa;
        
        let form = document.getElementById('formEditCuti');
        form.action = `/cuti/${id}`;
        
        toggleModal('modalEdit');
    }
</script>
@endsection
