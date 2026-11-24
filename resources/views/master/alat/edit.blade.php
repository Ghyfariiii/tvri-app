@extends('layouts.backend')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Alat Siaran</h1>
        <a href="{{ route('master.alat.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4 border-0 rounded-3">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-edit me-2"></i>Edit Data Alat</h6>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('master.alat.update', $alat->id) }}" method="POST">
                        @csrf
                        @method('PUT') <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Nama Alat</label>
                            <input type="text" name="nama_alat" class="form-control @error('nama_alat') is-invalid @enderror" 
                                   value="{{ old('nama_alat', $alat->nama_alat) }}" required>
                            @error('nama_alat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Deskripsi / Fungsi (Opsional)</label>
                            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" 
                                      rows="4">{{ old('deskripsi', $alat->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('master.alat.index') }}" class="btn btn-light text-secondary me-2 shadow-sm">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-save me-2"></i>Update Alat
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection