@extends('layouts.app')

@section('title', 'Tambah Pegawai')

@section('content')
    <div class="space-y-6">

        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Tambah Pegawai Baru</h2>
                <p class="text-slate-500 text-sm">Lengkapi data diri dan penempatan kerja pegawai.</p>
            </div>
            <x-back-button href="{{ route('pegawai.index') }}" />
        </div>

        <form action="{{ route('pegawai.store') }}" method="POST" enctype="multipart/form-data"
            class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 block">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 block w-full">Informasi Pribadi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">

                        <div class="w-full">
                            <x-input label="Nomor Induk Kewarganegaraan (NIK)" name="nik"
                                placeholder="Masukkan NIK sesuai KTP" required />
                        </div>
                        <div class="w-full">
                            <x-input label="Nama Lengkap" name="nama_lengkap" placeholder="Masukkan nama sesuai KTP" required />
                        </div>
                        <div class="w-full">
                            <x-input label="Email" name="email" type="email" placeholder="contoh@perusahaan.com"
                                required />
                        </div>
                        <div class="w-full">
                            <x-input label="Nomor Telepon" name="no_telp" placeholder="0812..." />
                        </div>
                        <div class="w-full">
                            <x-input label="Sisa Cuti (Hari)" name="sisa_cuti" type="number" value="12"
                                placeholder="Default: 12" required />
                        </div>

                        <div class="md:col-span-2 w-full mt-2">
                            <x-textarea label="Alamat Lengkap" name="alamat" rows="3"
                                placeholder="Alamat domisili saat ini" />
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Penempatan Kerja</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="w-full">
                            <x-select label="Divisi" name="id_divisi" required>
                                <option value="">Pilih Divisi</option>
                                @foreach ($divisi as $d)
                                    <option value="{{ $d->id_divisi }}">{{ $d->nama_divisi }}</option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="w-full">
                            <x-select label="Jabatan" name="id_jabatan" required>
                                <option value="">Pilih Jabatan</option>
                                @foreach ($jabatan as $j)
                                    <option value="{{ $j->id_jabatan }}">{{ $j->nama_jabatan }}</option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="w-full">
                            <x-select label="Lokasi Kantor" name="id_kantor" required>
                                <option value="">Pilih Kantor</option>
                                @foreach ($kantor as $k)
                                    <option value="{{ $k->id_kantor }}">{{ $k->nama_kantor }}</option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="w-full">
                            <x-select label="Role Akses" name="id_role" required>
                                <option value="">Pilih Role Akses</option>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->id_role }}">{{ $r->nama_role }}</option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="md:col-span-2 w-full mt-2">
                            <x-date-input label="Tanggal Bergabung" name="tgl_bergabung" required />
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="space-y-4 sticky top-6">
                    <x-image-upload label="Foto Profil" name="foto" id="foto" />

                    <div class="space-y-2 mt-2">
                        <x-button type="submit" id="submitBtn"
                            class="w-full justify-center py-3 bg-[#130F26] hover:bg-slate-800">
                            <span id="submitText">Simpan Data Pegawai</span>
                        </x-button>
                        <x-button href="{{ route('pegawai.index') }}" variant="secondary" class="w-full justify-center py-3">
                            Batalkan
                        </x-button>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl mt-2">
                        <p class="text-xs text-amber-800 leading-relaxed">
                            <strong>Informasi:</strong> Kata sandi default untuk pegawai baru adalah <code
                                class="bg-amber-200 px-1 rounded">Mpg123!</code>.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script src="{{ asset('js/form-handler.js') }}"></script>
@endsection
