@extends('layouts.app')

@section('title', 'Lembur')

@section('content')

    <x-page-header title="Persetujuan Lembur" subtitle="Kelola data pengajuan lembur karyawan">
        <x-slot:actions>
            <x-button as="a" href="{{ route('lembur.create') }}" variant="primary" icon="M12 4v16m8-8H4">
                Tambah Lembur Manual
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="mt-6">
        <x-table>
            <x-slot:header>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">No</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Tanggal Lembur</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Nama Pegawai</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Durasi</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Kompensasi</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Keterangan</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-left">Status</th>
                <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
            </x-slot:header>

            @forelse ($lembur as $index => $item)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 text-sm text-slate-700">
                        {{ $lembur->firstItem() + $index }}
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-700">
                        {{ \Carbon\Carbon::parse($item->tanggal_lembur)->translatedFormat('d F Y') }}
                        <div class="text-xs text-slate-500">
                            {{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }} -
                            {{ \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-slate-900">{{ $item->user->nama_lengkap ?? '-' }}</div>
                        <div class="text-xs text-slate-500">{{ $item->user->jabatan->nama_jabatan ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-700">
                        {{ $item->durasi_menit }} Menit
                    </td>
                    <td class="px-6 py-4">
                        @if($item->id_kompensasi == \App\Enums\JenisKompensasi::TAMBAHAN_POIN)
                            <x-badge color="purple">⭐ Poin</x-badge>
                        @else
                            <x-badge color="green">💵 Uang</x-badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-700">
                        {{ Str::limit($item->keterangan, 30) }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $idStatus = $item->id_status;
                            $badgeColor = match ($idStatus) {
                                \App\Enums\StatusPengajuan::PENDING   => 'yellow',
                                \App\Enums\StatusPengajuan::DISETUJUI => 'green',
                                \App\Enums\StatusPengajuan::DITOLAK   => 'red',
                                default => 'gray'
                            };
                        @endphp
                        <x-badge :color="$badgeColor">
                            {{ $item->status->nama_status ?? '-' }}
                        </x-badge>
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($item->id_status == \App\Enums\StatusPengajuan::PENDING)
                            <div class="flex justify-end gap-2">

                                <form action="{{ route('lembur.update', $item->id_lembur) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit"
                                        class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 p-2 rounded-lg transition-all"
                                        title="Setujui">
                                        <span class="text-xs font-semibold px-1">Setuju</span>
                                    </button>
                                </form>

                                <button type="button" onclick="openRejectModal('{{ route('lembur.update', $item->id_lembur) }}')"
                                    class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-all"
                                    title="Tolak">
                                    <span class="text-xs font-semibold px-1">Tolak</span>
                                </button>
                            </div>
                        @else
                            <span class="text-xs text-slate-400 italic">Selesai</span>
                        @endif
                    </td>
                </tr>
            @empty
                <x-empty-state colspan="8" message="Tidak ada pengajuan lembur" />
            @endforelse
        </x-table>

        <x-pagination :paginator="$lembur" />
    </div>

    <x-modal name="reject-modal" title="Tolak Pengajuan Lembur">
        <form id="rejectForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="action" value="reject">

            <div>
                <x-textarea label="Alasan Penolakan" name="alasan_penolakan"
                    placeholder="Jelaskan alasan kenapa pengajuan ini ditolak..." required rows="3" />
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 mt-4">
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
