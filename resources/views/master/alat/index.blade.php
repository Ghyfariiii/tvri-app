@extends('layouts.backend')

@section('content')
<div class="container-fluid">

    {{-- HEADER HALAMAN --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-soft-primary px-3 py-1 rounded-pill small fw-semibold">
                    <i class="fas fa-boxes me-1"></i> Logistik Penyiaran
                </span>
            </div>
            <h1 class="h3 mb-1 mt-2 text-gray-800 fw-bold">Manajemen Alat Siaran</h1>
            <p class="text-muted small mb-0">Inventarisasi dan pemeliharaan perangkat operasional penyiaran studio TVRI Gorontalo.</p>
        </div>
        <div class="mt-3 mt-sm-0">
            <a href="{{ route('master.alat.create') }}" class="btn btn-gradient-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-2"></i> Tambah Alat Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center p-3" style="background: rgba(16,185,129,0.1); color: #047857;" role="alert">
            <i class="fas fa-check-circle fa-lg me-3"></i>
            <div>
                <h6 class="fw-bold mb-0">Sukses</h6>
                <span class="small">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-modern overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-2 me-3" style="background: rgba(26,59,112,0.08); color: var(--primary-navy);">
                    <i class="fas fa-tools"></i>
                </div>
                <div>
                    <h6 class="m-0 fw-bold text-dark">Inventaris Peralatan Siaran</h6>
                    <small class="text-muted">Total <strong>{{ count($alats) }}</strong> unit perangkat terdaftar</small>
                </div>
            </div>
            <span class="badge badge-soft-primary rounded-pill px-3 py-2 fw-semibold">
                {{ count($alats) }} Perangkat
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="32%">Nama Perangkat / Alat</th>
                            <th width="48%">Deskripsi & Fungsi Operasional</th>
                            <th width="15%" class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alats as $i => $a)
                        <tr>
                            <td class="text-center fw-bold text-muted small">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-3 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(26,59,112,0.08); color: var(--primary-navy);">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="fw-bold text-dark small">{{ $a->nama_alat }}</div>
                                </div>
                            </td>
                            <td><span class="text-muted small">{{ $a->deskripsi ?? 'Tidak ada deskripsi rinci.' }}</span></td>
                            <td class="text-end pe-4">
                                <form action="{{ route('master.alat.destroy', $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus alat {{ $a->nama_alat }}?');">
                                    <a href="{{ route('master.alat.edit', $a->id) }}" class="btn btn-soft-warning btn-action-icon me-1" title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-action-icon" title="Hapus Data">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3">
                                    <i class="fas fa-box-open fa-3x text-muted opacity-50"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Belum Ada Data Alat</h6>
                                <p class="small text-muted mb-0">Silakan tambahkan perangkat atau perlengkapan siaran baru.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
