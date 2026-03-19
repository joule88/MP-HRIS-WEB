@extends('layouts.app')

@section('title', 'Penggunaan Poin')

@section('content')

    <x-page-header title="Persetujuan Penggunaan Poin" subtitle="Kelola persetujuan penukaran poin karyawan" />

    <div class="mt-6">
        <x-table>
            <x-slot:header>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">No</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Karyawan</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Tanggal Penggunaan</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Jenis Pengurangan</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Jumlah Poin</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Tanggal Diajukan</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Status</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
            </x-slot:header>

            @forelse ($penggunaan as $index => $item)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-sm text-gray-700">
                        {{ $penggunaan->firstItem() + $index }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $item->user->nama_lengkap ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $item->user->divisi->nama_divisi ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        {{ $item->tanggal_penggunaan ? $item->tanggal_penggunaan->translatedFormat('d F Y') : '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        {{ $item->jenisPengurangan->nama_pengurangan ?? 'Lainnya' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-bold text-amber-600">{{ $item->jumlah_poin }} Poin</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $item->tanggal_diajukan ? $item->tanggal_diajukan->translatedFormat('d M Y H:i') : '-' }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $idStatus = $item->id_status;
                            $badgeColor = match ($idStatus) {
                                1 => 'yellow',
                                2 => 'green',
                                3 => 'red',
                                default => 'gray'
                            };
                        @endphp
                        <x-badge :color="$badgeColor">
                            {{ $item->status->nama_status ?? '-' }}
                        </x-badge>
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($item->id_status == 1) 
                            <div class="flex justify-end gap-2">
                                
                                <form action="{{ route('penggunaan-poin.update', $item->id_penggunaan) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit"
                                        class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 p-2 rounded-lg transition-all"
                                        title="Setujui">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </form>

                                <button type="button"
                                    onclick="openRejectModal('{{ route('penggunaan-poin.update', $item->id_penggunaan) }}')"
                                    class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-all"
                                    title="Tolak">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        @else
                            <span class="text-xs text-gray-400 italic">Selesai</span>
                        @endif
                    </td>
                </tr>
            @empty
                <x-empty-state colspan="8" message="Tidak ada pengajuan penggunaan poin" />
            @endforelse
        </x-table>

        <x-pagination :paginator="$penggunaan" />
    </div>

    <x-modal name="reject-modal" title="Tolak Pengajuan Penggunaan Poin">
        <form id="rejectForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="action" value="reject">

            <div>
                <x-textarea label="Alasan Penolakan" name="alasan_penolakan"
                    placeholder="Jelaskan alasan kenapa pengajuan ini ditolak..." required rows="3" />
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 mt-4">
                <x-button type="button" variant="secondary" x-data
                    @click="$dispatch('close-modal', 'reject-modal')">Batal</x-button>
                <x-button type="submit" class="bg-red-600 hover:bg-red-700 text-white">Tolak Pengajuan</x-button>
            </div>
        </form>
    </x-modal>

    <script>
        function openRejectModal(url) {
            document.getElementById('rejectForm').action = url;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'reject-modal' }));
        }
    </script>
@endsection
