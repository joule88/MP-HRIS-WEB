@extends('layouts.app')

@section('title', 'Koreksi Presensi')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    
    <div class="flex items-center gap-4">
        <a href="{{ route('presensi.index', ['tanggal' => $presensi->tanggal]) }}" class="p-2 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors text-slate-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <x-page-header title="Koreksi Presensi" subtitle="Ubah data kehadiran pegawai secara manual (Admin)" />
    </div>

    @if(session('error'))
    <div class="p-4 bg-rose-50 text-rose-800 border border-rose-200 rounded-xl flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <p class="text-sm font-medium">{{ session('error') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        
        <div class="p-6 bg-slate-50 border-b border-slate-200 flex flex-col md:flex-row gap-6 justify-between items-start md:items-center">
            <div class="flex items-center gap-4">
                <x-avatar :name="$presensi->user->nama_lengkap" class="w-12 h-12 text-lg" />
                <div>
                    <h3 class="font-bold text-slate-900 text-lg">{{ $presensi->user->nama_lengkap }}</h3>
                    <p class="text-slate-500 text-sm">{{ $presensi->user->nik }} • {{ $presensi->user->divisi->nama_divisi ?? '-' }}</p>
                </div>
            </div>
            <div class="text-left md:text-right bg-white p-3 rounded-xl border border-slate-200 shadow-sm w-full md:w-auto">
                <div class="text-xs text-slate-500 font-semibold mb-1 uppercase tracking-wider">Tanggal Presensi</div>
                <div class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($presensi->tanggal)->translatedFormat('l, d F Y') }}</div>
            </div>
        </div>

        <form action="{{ route('presensi.updateManual', $presensi->id_presensi) }}" method="POST" class="p-6 md:p-8 space-y-8" id="formPresensiEdit">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="space-y-1">
                    <label class="block text-sm font-semibold text-slate-700">Status Kehadiran <span class="text-red-500">*</span></label>
                    <x-select name="id_status" id="statusSelect" required onchange="toggleTimeInputs()">
                        @foreach($statuses as $status)
                            <option value="{{ $status->id_status }}" {{ old('id_status', $presensi->id_status) == $status->id_status ? 'selected' : '' }}>
                                {{ $status->nama_status }}
                            </option>
                        @endforeach
                    </x-select>
                    @error('id_status') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="hidden md:block"></div> 

                <div class="space-y-1" id="wrapJamMasuk">
                    @php $jm = $presensi->jam_masuk ? \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i') : ''; @endphp
                    <x-input label="Jam Masuk" type="time" name="jam_masuk" value="{{ old('jam_masuk', $jm) }}" id="jamMasuk" />
                </div>

                <div class="space-y-1" id="wrapJamPulang">
                    @php $jp = $presensi->jam_pulang ? \Carbon\Carbon::parse($presensi->jam_pulang)->format('H:i') : ''; @endphp
                    <x-input label="Jam Pulang" type="time" name="jam_pulang" value="{{ old('jam_pulang', $jp) }}" id="jamPulang" />
                </div>

                <div class="space-y-1 md:col-span-2 mt-4">
                    <label class="block text-sm font-semibold text-slate-700">Catatan Koreksi (Wajib)</label>
                    <textarea name="alasan_telat" rows="2" required class="w-full rounded-xl border-slate-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all text-sm" placeholder="Contoh: Koreksi jam masuk karena admin salah input opsi cuti, dll.">{{ old('alasan_telat', $presensi->alasan_telat) }}</textarea>
                    <p class="text-xs text-slate-500 mt-1">Status Validasi akan otomatis diset menjadi <strong>Disetujui</strong> setelah dikoreksi.</p>
                </div>
                
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-slate-100 gap-3">
                <a href="{{ route('presensi.index', ['tanggal' => $presensi->tanggal]) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition-colors text-sm">Batal</a>
                <x-button type="submit" variant="primary" class="px-6 py-2.5 shadow-md shadow-primary/20" id="submitBtnEdit">
                    Simpan Perubahan
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
        }
    }
    
    document.addEventListener('DOMContentLoaded', toggleTimeInputs);
</script>
<script src="{{ asset('js/form-handler.js') }}"></script>
@endsection
