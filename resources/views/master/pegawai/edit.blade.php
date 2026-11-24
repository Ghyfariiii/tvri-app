@extends('layouts.backend')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Data Pegawai TVRI</h1>
        <a href="{{ route('master.pegawai.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4 border-0 rounded-3">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-user-edit me-2"></i>Edit Pegawai: {{ $pegawai->nama_lengkap }}</h6>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('master.pegawai.update', $pegawai->id) }}" method="POST">
                        @csrf
                        @method('PUT') <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" 
                                   value="{{ old('nama_lengkap', $pegawai->nama_lengkap) }}" required>
                            @error('nama_lengkap')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">NIP / ID Pegawai</label>
                                <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" 
                                       value="{{ old('nip', $pegawai->nip) }}">
                                @error('nip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">Posisi Default</label>
                                <select name="posisi_default" class="form-select @error('posisi_default') is-invalid @enderror" required>
                                    <option value="" disabled>-- Pilih Jabatan --</option>
                                    
                                    @php
                                        $jabatans = ['Pengarah Teknik', 'Kamerawan', 'Operator Komputer', 'Maintenance', 'Penata Suara', 'Penata Cahaya', 'Petugas IT'];
                                        $currentPos = old('posisi_default', $pegawai->posisi_default);
                                    @endphp

                                    @foreach($jabatans as $j)
                                        <option value="{{ $j }}" {{ $currentPos == $j ? 'selected' : '' }}>{{ $j }}</option>
                                    @endforeach
                                    
                                    @if(!in_array($currentPos, $jabatans) && !empty($currentPos))
                                        <option value="{{ $currentPos }}" selected>{{ $currentPos }} (Custom)</option>
                                    @endif

                                </select>
                                @error('posisi_default')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('master.pegawai.index') }}" class="btn btn-light text-secondary me-2 shadow-sm">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection