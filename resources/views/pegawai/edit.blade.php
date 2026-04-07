@extends('layouts.app')

@section('title', 'Edit Pegawai')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Edit Pegawai: {{ $pegawai->nama_lengkap }}</h2>
                <p class="text-slate-500 text-sm">Perbarui informasi pribadi atau penempatan kerja pegawai.</p>
            </div>
            <x-back-button href="{{ route('pegawai.index') }}" />
        </div>

        <form action="{{ route('pegawai.update', $pegawai->id) }}" method="POST" enctype="multipart/form-data"
            class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf
            @method('PUT')

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 block">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 block w-full">Informasi Pribadi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
                        <div class="w-full">
                            <x-input label="ID Karyawan" name="nik" value="{{ $pegawai->nik }}"
                                readonly class="bg-slate-50 opacity-70 cursor-not-allowed" />
                        </div>
                        <div class="w-full">
                            <x-input label="Nama Lengkap" name="nama_lengkap" value="{{ $pegawai->nama_lengkap }}" required />
                        </div>
                        <div class="w-full">
                            <x-input label="Email" name="email" type="email" value="{{ $pegawai->email }}" required />
                        </div>
                        <div class="w-full">
                            <x-input label="Nomor Telepon" name="no_telp" type="tel" value="{{ $pegawai->no_telp }}" placeholder="0812-XXXX-XXXX" />
                        </div>
                        <div class="w-full">
                            <x-input label="Sisa Cuti (Hari)" name="sisa_cuti" type="number" value="{{ $pegawai->sisa_cuti }}"
                                required />
                        </div>

                        <div class="md:col-span-2 w-full mt-2">
                            <x-textarea label="Alamat Lengkap" name="alamat" value="{{ $pegawai->alamat }}"
                                rows="3" />
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 block">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 block w-full">Penempatan & Status</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
                        <x-select label="Divisi" name="id_divisi" class="!mb-0" required>
                            @foreach ($divisi as $d)
                                <option value="{{ $d->id_divisi }}"
                                    {{ $pegawai->id_divisi == $d->id_divisi ? 'selected' : '' }}>
                                    {{ $d->nama_divisi }}</option>
                            @endforeach
                        </x-select>

                        <x-select label="Jabatan" name="id_jabatan" class="!mb-0" required>
                            @foreach ($jabatan as $j)
                                <option value="{{ $j->id_jabatan }}"
                                    {{ $pegawai->id_jabatan == $j->id_jabatan ? 'selected' : '' }}>{{ $j->nama_jabatan }}
                                </option>
                            @endforeach
                        </x-select>

                        <x-select label="Lokasi Kantor" name="id_kantor" class="!mb-0" required>
                            @foreach ($kantor as $k)
                                <option value="{{ $k->id_kantor }}"
                                    {{ $pegawai->id_kantor == $k->id_kantor ? 'selected' : '' }}>
                                    {{ $k->nama_kantor }}</option>
                            @endforeach
                        </x-select>

                        <x-select label="Status Pegawai" name="status_aktif" class="!mb-0" required>
                            <option value="1" {{ $pegawai->status_aktif == 1 ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ $pegawai->status_aktif == 0 ? 'selected' : '' }}>Non-Aktif</option>
                        </x-select>

                        <x-select label="Role Akses" name="id_role" class="!mb-0" required>
                            @foreach ($roles as $r)
                                <option value="{{ $r->id_role }}"
                                    {{ $pegawai->roles->contains('id_role', $r->id_role) ? 'selected' : '' }}>
                                    {{ $r->nama_role }}
                                </option>
                            @endforeach
                        </x-select>

                        <div class="md:col-span-2">
                            <x-date-input label="Tanggal Bergabung" name="tgl_bergabung"
                                value="{{ $pegawai->tgl_bergabung }}" required />
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="space-y-4 sticky top-6">
                    <x-image-upload label="Foto Profil" name="foto" id="foto"
                        default="{{ $pegawai->foto ? asset('storage/' . $pegawai->foto) : '' }}" />

                    <div class="space-y-2 mt-2">
                        <x-button type="submit" id="submitBtn"
                            class="w-full justify-center py-3 bg-amber-500 hover:bg-amber-600 shadow-amber-200">
                            <span id="submitText">Perbarui Data Pegawai</span>
                        </x-button>
                        <x-button href="{{ route('pegawai.index') }}" variant="secondary" class="w-full justify-center py-3">
                            Batalkan
                        </x-button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script src="{{ asset('js/form-handler.js') }}"></script>
@endsection
