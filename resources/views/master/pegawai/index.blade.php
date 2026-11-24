@extends('layouts.backend')

@section('content')
<div class="container-fluid">

    {{-- HEADER HALAMAN --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-soft-primary px-3 py-1 rounded-pill small fw-semibold">
                    <i class="fas fa-id-card-alt me-1"></i> Kepegawaian
                </span>
            </div>
            <h1 class="h3 mb-1 mt-2 text-gray-800 fw-bold">Data Pegawai TVRI Gorontalo</h1>
            <p class="text-muted small mb-0">Database kepegawaian, nomor identitas, dan posisi fungsional default dalam penyiaran.</p>
        </div>
        <div class="mt-3 mt-sm-0">
            <a href="{{ route('master.pegawai.create') }}" class="btn btn-gradient-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-user-plus me-2"></i> Tambah Pegawai
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
                    <i class="fas fa-address-book"></i>
                </div>
                <div>
                    <h6 class="m-0 fw-bold text-dark">Daftar Pegawai & Jabatan Fungsional</h6>
                    <small class="text-muted">Total <strong>{{ count($pegawais) }}</strong> pegawai aktif</small>
                </div>
            </div>
            <span class="badge badge-soft-primary rounded-pill px-3 py-2 fw-semibold">
                {{ count($pegawais) }} Pegawai
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="35%">Nama Lengkap</th>
                            <th width="20%">NIP / ID Pegawai</th>
                            <th width="25%">Posisi Fungsional Default</th>
                            <th width="15%" class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pegawais as $index => $p)
                        <tr>
                            <td class="text-center fw-bold text-muted small">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light text-primary d-inline-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="fw-bold text-dark small">{{ $p->nama_lengkap }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace px-3 py-1">
                                    {{ $p->nip ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-soft-info rounded-pill px-3 py-1 fw-semibold">
                                    {{ $p->posisi_default }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <form action="{{ route('master.pegawai.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pegawai {{ $p->nama_lengkap }}?');">
                                    <a href="{{ route('master.pegawai.edit', $p->id) }}" class="btn btn-soft-warning btn-action-icon me-1" title="Edit Data">
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
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3">
                                    <i class="fas fa-users-slash fa-3x text-muted opacity-50"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Belum Ada Data Pegawai</h6>
                                <p class="small text-muted mb-0">Silakan tambahkan data kepegawaian baru ke dalam sistem.</p>
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
