@extends('layouts.app')

@section('title', 'Input Presensi Manual')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    
    <div class="flex items-center gap-4">
        <a href="{{ route('presensi.index') }}" class="p-2 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors text-slate-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <x-page-header title="Input Presensi Manual" subtitle="Catat kehadiran pegawai secara manual (Admin)" />
    </div>

    @if(session('error'))
    <div class="p-4 bg-rose-50 text-rose-800 border border-rose-200 rounded-xl flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <p class="text-sm font-medium">{{ session('error') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="{{ route('presensi.storeManual') }}" method="POST" class="p-6 md:p-8 space-y-8" id="formPresensi">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="space-y-1 md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700">Pilih Pegawai <span class="text-red-500">*</span></label>
                    <x-select name="id_user" required>
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($pegawai as $p)
                            <option value="{{ $p->id }}" {{ old('id_user') == $p->id ? 'selected' : '' }}>
                                {{ $p->nik }} - {{ $p->nama_lengkap }} ({{ $p->divisi->nama_divisi ?? '-' }})
                            </option>
                        @endforeach
                    </x-select>
                    @error('id_user') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <x-input label="Tanggal" type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required />
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-semibold text-slate-700">Status Kehadiran <span class="text-red-500">*</span></label>
                    <x-select name="id_status" id="statusSelect" required onchange="toggleTimeInputs()">
                        @foreach($statuses as $status)
                            <option value="{{ $status->id_status }}" {{ old('id_status', 1) == $status->id_status ? 'selected' : '' }}>
                                {{ $status->nama_status }}
                            </option>
                        @endforeach
                    </x-select>
                    @error('id_status') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1 hidden" id="wrapJamMasuk">
                    <x-input label="Jam Masuk" type="time" name="jam_masuk" value="{{ old('jam_masuk', '08:00') }}" id="jamMasuk" />
                </div>

                <div class="space-y-1 hidden" id="wrapJamPulang">
                    <x-input label="Jam Pulang" type="time" name="jam_pulang" value="{{ old('jam_pulang', '17:00') }}" id="jamPulang" />
                </div>

                <div class="space-y-1 md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700">Catatan / Alasan Manual</label>
                    <textarea name="alasan_telat" rows="2" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all text-sm" placeholder="Contoh: Pegawai lupa absen, sistem error, dll.">{{ old('alasan_telat') }}</textarea>
                    <p class="text-xs text-slate-500 mt-1">Status Validasi otomatis diset menjadi <strong>Disetujui</strong>.</p>
                </div>
                
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-slate-100 gap-3">
                <a href="{{ route('presensi.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition-colors text-sm">Batal</a>
                <x-button type="submit" variant="primary" class="px-6 py-2.5 shadow-md shadow-primary/20" id="submitBtn">
                    Simpan Presensi
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    function toggleTimeInputs() {
        const select = document.getElementById('statusSelect');
        const jamMasuk = document.getElementById('wrapJamMasuk');
        const jamPulang = document.getElementById('wrapJamPulang');
        
        const showTime = select.value == '1' || select.value == '2';
        
        if (showTime) {
            jamMasuk.classList.remove('hidden');
            jamPulang.classList.remove('hidden');
        } else {
            jamMasuk.classList.add('hidden');
            jamPulang.classList.add('hidden');
            document.getElementById('jamMasuk').value = '';
            document.getElementById('jamPulang').value = '';
        }
    }
    
    document.addEventListener('DOMContentLoaded', toggleTimeInputs);
</script>
<script src="{{ asset('js/form-handler.js') }}"></script>
@endsection
