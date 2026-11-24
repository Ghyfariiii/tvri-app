@extends('layouts.backend')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold text-dark">Dashboard Overview</h2>
        <p class="text-muted">Selamat datang di Sistem Manajemen Administrasi & Operasional TVRI Gorontalo.</p>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-primary">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Total SPO Bulan Ini</h6>
                    <h2 class="fw-bold text-dark mb-0">24</h2>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                    <i class="fas fa-file-alt fa-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-warning">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Menunggu Approval</h6>
                    <h2 class="fw-bold text-warning mb-0">3</h2>
                </div>
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                    <i class="fas fa-hourglass-half fa-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-danger">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Kendala Dilaporkan</h6>
                    <h2 class="fw-bold text-danger mb-0">5</h2>
                </div>
                <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-success">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Status Siaran</h6>
                    <h5 class="fw-bold text-success mb-0">On Air (Aman)</h5>
                </div>
                <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                    <i class="fas fa-broadcast-tower fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-history me-2"></i>SPO Terkini</h6>
                <a href="#" class="btn btn-sm btn-outline-primary rounded-pill px-3">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">No. Surat</th>
                                <th>Tanggal</th>
                                <th>Petugas</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <tr>
                                <td class="ps-4 fw-bold">SPO/TVRI/001</td>
                                <td>20 Jan 2026</td>
                                <td><small>Budi, Andi...</small></td>
                                <td><span class="badge bg-success bg-opacity-10 text-success px-3 rounded-pill">Approved</span></td>
                                <td><button class="btn btn-sm btn-light text-primary"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-bold">SPO/TVRI/002</td>
                                <td>21 Jan 2026</td>
                                <td><small>Siti, Rina...</small></td>
                                <td><span class="badge bg-warning bg-opacity-10 text-warning px-3 rounded-pill">Pending</span></td>
                                <td><button class="btn btn-sm btn-light text-primary"><i class="fas fa-edit"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 bg-primary text-white h-100">
            <div class="card-body p-4 position-relative overflow-hidden">
                <h4 class="fw-bold mb-3">Buat Laporan Baru?</h4>
                <p class="mb-4 opacity-75">Segera laporkan kondisi peralatan siaran untuk pemantauan realtime.</p>
                <a href="#" class="btn btn-light text-primary fw-bold w-100 shadow-sm py-2">
                    <i class="fas fa-plus-circle me-2"></i>Input Monitoring
                </a>
                <i class="fas fa-clipboard-list position-absolute text-white opacity-25" style="font-size: 8rem; bottom: -20px; right: -20px;"></i>
            </div>
        </div>
    </div>
</div>
@endsection