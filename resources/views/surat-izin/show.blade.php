@extends('layouts.app')

@section('title', 'Detail Surat Izin')

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center sm:hidden mb-4 print:hidden">
            <h1 class="text-2xl font-bold text-slate-800">Detail Surat Izin</h1>
        </div>

        <div class="print:hidden">
        <x-page-header title="Detail Surat Izin" subtitle="No. {{ $surat->nomor_surat }}">
            <div class="flex gap-2">
                <x-back-button href="{{ route('surat-izin.index') }}" />
                <button type="button" onclick="window.print()" class="inline-flex items-center justify-center px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-semibold hover:bg-slate-700 transition-colors h-[42px] print:hidden">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak Surat
                </button>
            </div>
        </x-page-header>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 print:hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Progress Approval</h3>
                @php
                    $badgeColor = match ($surat->status_surat) {
                        'disetujui' => 'green',
                        'ditolak' => 'red',
                        'menunggu_hrd' => 'blue',
                        default => 'yellow'
                    };
                    $statusLabel = match ($surat->status_surat) {
                        'menunggu_manajer' => 'Menunggu Manajer',
                        'menunggu_hrd' => 'Menunggu HRD',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        default => $surat->status_surat
                    };
                @endphp
                <x-badge color="{{ $badgeColor }}">{{ $statusLabel }}</x-badge>
            </div>

            <div class="flex items-center gap-4">
                
                <div class="flex-1 text-center">
                    <div class="w-10 h-10 mx-auto rounded-full bg-green-100 text-green-600 flex items-center justify-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700">Pengaju</p>
                    <p class="text-xs text-slate-500">{{ $surat->user->nama_lengkap ?? '-' }}</p>
                </div>

                <div class="w-12 h-0.5 bg-slate-200"></div>

                @php $approvalManajer = $surat->approvals->firstWhere('tahap', 1); @endphp
                <div class="flex-1 text-center">
                    <div class="w-10 h-10 mx-auto rounded-full flex items-center justify-center mb-2
                        {{ $approvalManajer ? ($approvalManajer->status === 'disetujui' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600') : 'bg-slate-100 text-slate-400' }}">
                        @if($approvalManajer && $approvalManajer->status === 'disetujui')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @elseif($approvalManajer && $approvalManajer->status === 'ditolak')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @endif
                    </div>
                    <p class="text-xs font-bold text-slate-700">Manajer</p>
                    <p class="text-xs text-slate-500">{{ $approvalManajer?->approver?->nama_lengkap ?? 'Menunggu' }}</p>
                </div>

                <div class="w-12 h-0.5 bg-slate-200"></div>

                @php $approvalHrd = $surat->approvals->firstWhere('tahap', 2); @endphp
                <div class="flex-1 text-center">
                    <div class="w-10 h-10 mx-auto rounded-full flex items-center justify-center mb-2
                        {{ $approvalHrd ? ($approvalHrd->status === 'disetujui' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600') : 'bg-slate-100 text-slate-400' }}">
                        @if($approvalHrd && $approvalHrd->status === 'disetujui')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @elseif($approvalHrd && $approvalHrd->status === 'ditolak')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @endif
                    </div>
                    <p class="text-xs font-bold text-slate-700">HRD</p>
                    <p class="text-xs text-slate-500">{{ $approvalHrd?->approver?->nama_lengkap ?? 'Menunggu' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 surat-container print:shadow-none print:border-none print:p-0 print:m-0 print:w-full print:max-w-none">
            <div class="max-w-2xl mx-auto print:max-w-full print:mx-0 print:w-full">
                
                <div class="text-center border-b-2 border-slate-800 pb-4 mb-6 print:border-black print:pb-2 print:mb-4">
                    <h2 class="text-xl font-extrabold text-slate-800 tracking-wide print:text-black print:text-2xl">SURAT IZIN</h2>
                    <p class="text-sm text-slate-500 mt-1 print:text-black print:mt-0">No: {{ $surat->nomor_surat }}</p>
                </div>

                <div class="mb-6 text-sm text-slate-700 leading-relaxed whitespace-pre-line print:text-black print:mb-4 print:text-justify">{{ $surat->isi_surat }}</div>

                @if($surat->pengajuanIzin)
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-6 print:border-none print:shadow-none print:-ml-4 print:bg-transparent print:p-4 print:-mr-4 print:mb-4">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 print:text-black print:underline">Detail Izin</p>
                    <table class="text-sm text-slate-700 print:text-black">
                        <tr><td class="pr-4 py-0.5 font-medium print:py-0">Jenis Izin</td><td class="pr-2">:</td><td>{{ $surat->pengajuanIzin->jenisIzin->nama_izin ?? '-' }}</td></tr>
                        <tr><td class="pr-4 py-0.5 font-medium print:py-0">Tanggal Mulai</td><td class="pr-2">:</td><td>{{ \Carbon\Carbon::parse($surat->pengajuanIzin->tanggal_mulai)->format('d F Y') }}</td></tr>
                        <tr><td class="pr-4 py-0.5 font-medium print:py-0">Tanggal Selesai</td><td class="pr-2">:</td><td>{{ \Carbon\Carbon::parse($surat->pengajuanIzin->tanggal_selesai)->format('d F Y') }}</td></tr>
                        <tr><td class="pr-4 py-0.5 font-medium print:py-0">Alasan</td><td class="pr-2">:</td><td>{{ $surat->pengajuanIzin->alasan ?? '-' }}</td></tr>
                    </table>
                </div>
                @endif

                <p class="text-sm text-slate-600 mb-8">Demikian surat ini dibuat, atas perhatiannya saya ucapkan terima kasih.</p>

                <div class="grid grid-cols-3 gap-6 text-center mt-8 pt-6 border-t border-slate-200 print:border-black print:mt-4 print:pt-4">
                    
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase mb-4 print:text-black print:mb-2">Yang Mengajukan</p>
                        <div class="h-24 flex items-center justify-center">
                            @if($surat->tandaTanganPengaju)
                                <img src="{{ asset('storage/' . $surat->tandaTanganPengaju->file_ttd) }}" alt="TTD" class="max-h-20 object-contain" style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
                            @else
                                <span class="text-xs text-slate-400 italic print:text-black">Tanpa TTD</span>
                            @endif
                        </div>
                        <p class="text-sm font-bold text-slate-800 border-t border-slate-300 pt-2 mt-2 print:text-black print:border-black">{{ $surat->user->nama_lengkap ?? '-' }}</p>
                        <p class="text-xs text-slate-500 print:text-black">{{ $surat->user->jabatan->nama_jabatan ?? '' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase mb-4 print:text-black print:mb-2">Manajer</p>
                        <div class="h-24 flex items-center justify-center">
                            @if($approvalManajer && $approvalManajer->tandaTanganApprover)
                                <img src="{{ asset('storage/' . $approvalManajer->tandaTanganApprover->file_ttd) }}" alt="TTD" class="max-h-20 object-contain" style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
                            @elseif($approvalManajer && $approvalManajer->status === 'ditolak')
                                <span class="text-xs text-red-500 font-bold print:text-black">DITOLAK</span>
                            @else
                                <span class="text-xs text-slate-400 italic print:text-black">Menunggu</span>
                            @endif
                        </div>
                        <p class="text-sm font-bold text-slate-800 border-t border-slate-300 pt-2 mt-2 print:text-black print:border-black">{{ $approvalManajer?->approver?->nama_lengkap ?? '...................' }}</p>
                        <p class="text-xs text-slate-500 print:text-black">{{ $approvalManajer?->approver?->jabatan?->nama_jabatan ?? 'Manajer' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase mb-4 print:text-black print:mb-2">HRD</p>
                        <div class="h-24 flex items-center justify-center">
                            @if($approvalHrd && $approvalHrd->tandaTanganApprover)
                                <img src="{{ asset('storage/' . $approvalHrd->tandaTanganApprover->file_ttd) }}" alt="TTD" class="max-h-20 object-contain" style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
                            @elseif($approvalHrd && $approvalHrd->status === 'ditolak')
                                <span class="text-xs text-red-500 font-bold print:text-black">DITOLAK</span>
                            @else
                                <span class="text-xs text-slate-400 italic print:text-black">Menunggu</span>
                            @endif
                        </div>
                        <p class="text-sm font-bold text-slate-800 border-t border-slate-300 pt-2 mt-2 print:text-black print:border-black">{{ $approvalHrd?->approver?->nama_lengkap ?? '...................' }}</p>
                        <p class="text-xs text-slate-500 print:text-black">{{ $approvalHrd?->approver?->jabatan?->nama_jabatan ?? 'HRD' }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($canApprove)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 print:hidden">
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Tindakan</h3>

            @if(!$ttdApprover)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-amber-700">
                        <strong>Perhatian:</strong> Anda belum memiliki tanda tangan digital.
                        <a href="{{ route('signature.show') }}" class="text-amber-800 underline font-bold">Buat tanda tangan dulu</a> sebelum menyetujui surat.
                    </p>
                </div>
            @endif

            <div class="flex gap-3">
                <form id="form-reject" action="{{ route('surat-izin.reject', $surat->id_surat) }}" method="POST" class="hidden">@csrf</form>
                <form id="form-approve" action="{{ route('surat-izin.approve', $surat->id_surat) }}" method="POST" class="hidden">@csrf</form>

                <x-button type="button" variant="danger"
                    onclick="confirmAction(event, 'form-reject', 'Surat izin akan ditolak.', '#ef4444', 'Ya, Tolak')">
                    Tolak
                </x-button>
                <x-button type="button" variant="primary"
                    onclick="confirmAction(event, 'form-approve', 'Tanda tangan Anda akan ditambahkan ke surat ini.', '#10b981', 'Ya, Setujui')"
                    :disabled="!$ttdApprover">
                    Setujui & Tanda Tangani
                </x-button>
            </div>
        </div>
        @endif
    </div>
@endsection

@section('style')
<style>
    @media print {
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        aside, header {
            display: none !important;
        }
        body, html, #main-scroll-container, main, .h-screen, .overflow-hidden, .overflow-y-auto {
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
            background-color: transparent !important;
            margin: 0 !important;
            padding: 0 !important;
            position: static !important;
        }
        @page { margin: 1.5cm; }
    }
</style>
@endsection
