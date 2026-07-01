@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')

    <x-page-header title="Daftar Pengumuman" subtitle="Kelola informasi untuk karyawan">
        <x-button type="button" variant="danger" id="bulk-delete-btn" onclick="confirmBulkDelete()" class="hidden items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Hapus Terpilih (<span id="selected-count">0</span>)
        </x-button>
        <x-button href="{{ route('pengumuman.create') }}" class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Buat Pengumuman
        </x-button>
    </x-page-header>

    <div class="mt-6">
        <form id="bulk-delete-form" action="{{ route('pengumuman.bulkDelete') }}" method="POST">
            @csrf
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <x-table>
                <x-slot:header>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left w-10">
                        <input type="checkbox" id="select-all" class="w-4 h-4 rounded border-slate-300 text-[#130F26] focus:ring-[#130F26]">
                    </th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left w-14">No</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Judul</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Tanggal</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-left">Dibuat Oleh</th>
                    <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                </x-slot:header>
                @forelse ($pengumuman as $item)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            <input type="checkbox" name="ids[]" value="{{ $item->id_pengumuman }}" class="pengumuman-checkbox w-4 h-4 rounded border-slate-300 text-[#130F26] focus:ring-[#130F26]">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            {{ $loop->iteration + ($pengumuman->currentPage() - 1) * $pengumuman->perPage() }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-slate-900">{{ $item->judul }}</div>
                            <div class="text-sm text-slate-500 truncate max-w-xs">{{ Str::limit($item->isi, 50) }}</div>
                            @if($item->foto || $item->lampiran)
                                <div class="flex gap-2 mt-1">
                                    @if($item->foto)
                                        <span class="inline-flex items-center gap-1 text-xs text-blue-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Foto
                                        </span>
                                    @endif
                                    @if($item->lampiran)
                                        <span class="inline-flex items-center gap-1 text-xs text-green-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            Lampiran
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            {{ $item->tanggal ? $item->tanggal->translatedFormat('d M Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            {{ $item->pembuat->nama_lengkap ?? 'System' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <x-button-edit href="{{ route('pengumuman.edit', $item->id_pengumuman) }}" />
                                <x-delete-button :id="$item->id_pengumuman" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state colspan="6" message="Belum ada pengumuman" hint="Silakan buat pengumuman baru untuk karyawan." />
                @endforelse
            </x-table>
            </div>
        </form>

        <x-pagination :paginator="$pengumuman" />
    </div>

    {{-- Form terpisah untuk hapus per baris guna menghindari nested form --}}
    @foreach ($pengumuman as $item)
        <form id="delete-form-{{ $item->id_pengumuman }}"
            action="{{ route('pengumuman.destroy', $item->id_pengumuman) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

@endsection

@section('script')
<script>
    document.addEventListener('turbo:load', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.pengumuman-checkbox');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        const selectedCount = document.getElementById('selected-count');

        if (!selectAllCheckbox || !bulkDeleteBtn) return;

        function updateBulkDeleteButton() {
            const checkedCount = document.querySelectorAll('.pengumuman-checkbox:checked').length;
            if (checkedCount > 0) {
                selectedCount.textContent = checkedCount;
                bulkDeleteBtn.classList.remove('hidden');
                bulkDeleteBtn.classList.add('flex');
            } else {
                bulkDeleteBtn.classList.remove('flex');
                bulkDeleteBtn.classList.add('hidden');
            }
        }

        selectAllCheckbox.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            updateBulkDeleteButton();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = document.querySelectorAll('.pengumuman-checkbox:checked').length === checkboxes.length;
                selectAllCheckbox.checked = allChecked;
                updateBulkDeleteButton();
            });
        });
    });

    function confirmBulkDelete() {
        const checkedBoxes = document.querySelectorAll('.pengumuman-checkbox:checked');
        if (checkedBoxes.length === 0) return;

        Swal.fire({
            html: `
                <div class="flex flex-col items-center py-2">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4 ring-8 ring-red-50">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Hapus Pengumuman?</h2>
                    <p class="text-sm text-slate-500 text-center px-4 mt-2 leading-relaxed">
                        Sebanyak <b>${checkedBoxes.length}</b> pengumuman yang dipilih akan dihapus secara permanen dari sistem.
                    </p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus Semua',
            cancelButtonText: 'Batalkan',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'px-6 py-2.5 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition shadow-lg shadow-red-200 mr-2',
                cancelButton: 'px-6 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition'
            },
            width: '400px',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    html: `
                        <div class="flex flex-col items-center py-6">
                            <div class="animate-spin rounded-full h-10 w-10 border-4 border-slate-200 border-t-red-600 mb-4"></div>
                            <p class="text-sm font-bold text-slate-700">Menghapus Pengumuman...</p>
                        </div>
                    `,
                    width: '250px',
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                
                document.getElementById('bulk-delete-form').submit();
            }
        });
    }
</script>
@endsection
