@extends('layouts.app')

@section('title', 'Input Lembur Manual')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Input Lembur Manual" subtitle="Buat data lembur untuk pegawai dengan status disetujui otomatis.">
            <x-back-button href="{{ route('lembur.index') }}" />
        </x-page-header>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <form action="{{ route('lembur.store') }}" method="POST" class="p-6 space-y-6" id="formLembur">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-select label="Pilih Pegawai" name="id_user" required>
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($pegawai as $p)
                            <option value="{{ $p->id }}" {{ old('id_user') == $p->id ? 'selected' : '' }}>
                                {{ $p->nik }} - {{ $p->nama_lengkap }}
                            </option>
                        @endforeach
                    </x-select>

                    <x-input label="Tanggal Lembur" type="date" name="tanggal_lembur" value="{{ old('tanggal_lembur', date('Y-m-d')) }}" required />

                    <x-input label="Jam Mulai" type="time" name="jam_mulai" value="{{ old('jam_mulai') }}" required />

                    <x-input label="Jam Selesai" type="time" name="jam_selesai" value="{{ old('jam_selesai') }}" required />

                    <x-select label="Jenis Kompensasi" name="id_kompensasi" required>
                        <option value="">-- Pilih Kompensasi --</option>
                        @foreach($kompensasi as $k)
                            <option value="{{ $k->id_kompensasi }}" {{ old('id_kompensasi') == $k->id_kompensasi ? 'selected' : '' }}>
                                {{ $k->nama_kompensasi }} ({{ $k->id_kompensasi == 2 ? 'Dapat Poin Cut' : 'Uang Lembur' }})
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <x-textarea label="Keterangan / Alasan Lembur" name="keterangan" rows="3" required placeholder="Tuliskan keterangan lembur...">{{ old('keterangan') }}</x-textarea>
                </div>

                <div class="flex justify-end pt-6 border-t border-slate-100">
                    <x-button type="submit" variant="primary" id="submitBtn">Simpan Lembur</x-button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script>
    document.getElementById('formLembur').addEventListener('submit', function(e) {
        const jamMulai = document.querySelector('input[name="jam_mulai"]').value;
        const jamSelesai = document.querySelector('input[name="jam_selesai"]').value;
        const submitBtn = document.getElementById('submitBtn');
        
        if(jamMulai && jamSelesai) {
            if(jamMulai === jamSelesai) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Waktu Tidak Valid',
                    text: 'Jam mulai dan jam selesai tidak boleh sama.'
                });
                return false;
            }
        }

        submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses...';
        submitBtn.disabled = true;
    });
</script>
@endsection
