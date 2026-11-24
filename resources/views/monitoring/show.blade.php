@extends('layouts.backend')

@section('content')
@php
    $bisaApprove = (Auth::user()->role === 'admin') || (Auth::user()->role === 'katim' && Auth::id() == $laporan->katim_id);
@endphp

<div class="container-fluid">

    {{-- HEADER HALAMAN --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('monitoring.index') }}" class="text-decoration-none text-muted small fw-semibold">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Monitoring
                </a>
                <span class="text-muted small">/</span>
                <span class="badge badge-soft-primary px-3 py-1 rounded-pill small fw-semibold">
                    Detail Laporan #{{ $laporan->id }}
                </span>
            </div>
            <h1 class="h3 mb-1 mt-2 text-gray-800 fw-bold">Detail Laporan Monitoring Siaran</h1>
            <p class="text-muted small mb-0">Informasi teknis operasional penyiaran stasiun TVRI Gorontalo.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3 mt-sm-0">
            <a href="{{ route('monitoring.download.pdf', $laporan->id) }}" class="btn btn-outline-danger rounded-pill px-4 shadow-sm" target="_blank">
                <i class="fas fa-file-pdf me-2"></i> Cetak PDF Laporan Ini
            </a>
            @if((Auth::user()->id == $laporan->created_by || Auth::user()->role === 'admin') && $laporan->status_revisi === 'approved')
            <a href="{{ route('monitoring.edit', $laporan->id) }}" class="btn btn-gradient-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-edit me-2"></i> Edit Laporan Sekarang
            </a>
            @endif
        </div>
    </div>

    {{-- BANNER KHUSUS PERSETUJUAN KATIM / ADMIN --}}
    @if($laporan->status_revisi == 'pending')
        @if($bisaApprove)
        <div class="card border-0 mb-4 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #f59e0b !important;">
            <div class="card-body p-4 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center">
                    <div class="bg-warning text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px; min-width: 50px;">
                        <i class="fas fa-bell fa-lg badge-pulse"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Perhatian Katim: Permintaan Izin Revisi Menunggu Persetujuan</h6>
                        <p class="small text-muted mb-0">
                            Program Director mengajukan permohonan revisi data laporan siaran ini. Silakan telaah alasan di bawah dan ambil keputusan.
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <form action="{{ route('monitoring.approve-revision', $laporan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Setujui permintaan revisi ini?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                            <i class="fas fa-check me-1"></i> Setujui
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRejectRevisiMonitoring">
                        <i class="fas fa-times me-1"></i> Tolak
                    </button>
                </div>
            </div>
        </div>
        @elseif(Auth::user()->role === 'katim')
        <div class="alert alert-secondary border-0 rounded-4 shadow-sm p-3 mb-4 d-flex align-items-center">
            <i class="fas fa-lock text-muted fa-2x me-3"></i>
            <div>
                <h6 class="fw-bold text-dark mb-0">Permintaan Revisi Sedang Menunggu Persetujuan</h6>
                <small class="text-muted">Siaran ini di bawah wewenang <strong>{{ $laporan->nama_katim ?? 'Ketua Tim lain' }}</strong>. Hanya Katim terkait atau Administrator yang berhak menyetujui.</small>
            </div>
        </div>
        @endif
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card card-modern overflow-hidden mb-4">
                
                {{-- CARD HEADER WITH STATUS BADGES --}}
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 p-2 me-3" style="background: rgba(26,59,112,0.08); color: var(--primary-navy);">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <h6 class="m-0 fw-bold text-dark">
                                Siaran: {{ \Carbon\Carbon::parse($laporan->tanggal)->format('l, d F Y') }}
                            </h6>
                            <small class="text-muted">No. SPO: <strong>{{ $laporan->no_surat ?? 'Non-SPO / Regular' }}</strong></small>
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        @if($laporan->status_siaran == 'sukses')
                            <span class="badge badge-soft-success rounded-pill px-3 py-2 fw-semibold">
                                <i class="fas fa-check-circle me-1"></i> SUKSES
                            </span>
                        @elseif($laporan->status_siaran == 'kendala')
                            <span class="badge badge-soft-warning rounded-pill px-3 py-2 fw-semibold">
                                <i class="fas fa-exclamation-triangle me-1"></i> KENDALA
                            </span>
                        @else
                            <span class="badge badge-soft-danger rounded-pill px-3 py-2 fw-semibold">
                                <i class="fas fa-times-circle me-1"></i> GAGAL
                            </span>
                        @endif

                        @if($laporan->status_revisi == 'pending')
                            <span class="badge badge-soft-warning rounded-pill px-3 py-2 fw-semibold badge-pulse">
                                <i class="fas fa-clock me-1"></i> REVISI PENDING
                            </span>
                        @elseif($laporan->status_revisi == 'approved')
                            <span class="badge badge-soft-info rounded-pill px-3 py-2 fw-semibold">
                                <i class="fas fa-check me-1"></i> REVISI DISETUJUI
                            </span>
                        @elseif($laporan->status_revisi == 'rejected')
                            <span class="badge badge-soft-danger rounded-pill px-3 py-2 fw-semibold">
                                <i class="fas fa-times me-1"></i> REVISI DITOLAK
                            </span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-4">
                    
                    {{-- METADATA GRID --}}
                    <div class="row g-4 mb-4">
                        <div class="col-md-7">
                            <div class="p-4 rounded-4 bg-light border border-light-subtle h-100">
                                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                                    <i class="fas fa-info-circle text-primary me-2"></i> Parameter Dokumen
                                </h6>
                                <div class="row g-3 small">
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block mb-1">Nomor Surat SPO</span>
                                        <span class="fw-bold text-dark fs-6">{{ $laporan->no_surat ?? '-' }}</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block mb-1">Ketua Tim (Katim)</span>
                                        <span class="badge badge-soft-primary px-3 py-1 rounded-pill fw-semibold">
                                            <i class="fas fa-user-shield me-1"></i> {{ $laporan->nama_katim ?? '-' }}
                                        </span>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block mb-1">Pengarah Teknik (TD)</span>
                                        <span class="fw-bold text-dark">{{ $laporan->penginput ?? 'Akun Terhapus' }}</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block mb-1">Waktu Pencatatan</span>
                                        <span class="text-dark">{{ \Carbon\Carbon::parse($laporan->created_at)->format('d M Y, H:i') }} WITA</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- KUALITAS SINYAL AUDIO & VIDEO --}}
                        <div class="col-md-5">
                            <div class="p-4 rounded-4 bg-light border border-light-subtle h-100 d-flex flex-column justify-content-between">
                                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                                    <i class="fas fa-sliders-h text-info me-2"></i> Mutu Kualitas Teknis
                                </h6>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-muted"><i class="fas fa-volume-up text-primary me-1"></i> Kualitas Audio</span>
                                        <span class="badge bg-white border text-dark fw-bold px-3 py-1">{{ strtoupper($laporan->kondisi_audio ?? 'BAIK') }}</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ strtolower($laporan->kondisi_audio ?? 'baik') == 'baik' ? '100%' : (strtolower($laporan->kondisi_audio) == 'cukup' ? '65%' : '35%') }};"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-muted"><i class="fas fa-video text-info me-1"></i> Kualitas Video</span>
                                        <span class="badge bg-white border text-dark fw-bold px-3 py-1">{{ strtoupper($laporan->kondisi_video ?? 'BAIK') }}</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ strtolower($laporan->kondisi_video ?? 'baik') == 'baik' ? '100%' : (strtolower($laporan->kondisi_video) == 'cukup' ? '65%' : '35%') }};"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TABEL KRU BERTUGAS --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="fas fa-users text-primary me-2"></i> Daftar Kru Bertugas (Aktual Siaran)
                            </h6>
                            <span class="badge bg-light border text-muted px-3 py-1 rounded-pill small">
                                {{ count($kruBertugas) }} Personel
                            </span>
                        </div>
                        <div class="card border rounded-4 overflow-hidden">
                            <div class="table-responsive">
                                <table class="table table-hover table-modern mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th width="8%" class="text-center">No</th>
                                            <th width="42%">Penugasan / Posisi</th>
                                            <th width="50%">Nama Personel</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($kruBertugas as $index => $kru)
                                        <tr>
                                            <td class="text-center fw-bold text-muted small">{{ $index + 1 }}</td>
                                            <td>
                                                <span class="badge badge-soft-primary px-3 py-1 rounded-pill fw-semibold">
                                                    {{ $kru['jabatan'] ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-light text-secondary d-inline-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <span class="fw-semibold text-dark">{{ $kru['nama'] ?? '-' }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">
                                                <i class="fas fa-user-slash me-1"></i> Tidak ada rincian personil tercatat.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- LOG ACARA & KEGIATAN SIARAN PER-JAM --}}
                    <div class="mb-5">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-dark m-0">
                                <i class="fas fa-list-ul text-primary me-2"></i> Log Kegiatan & Program Acara Siaran Per-Jam
                            </h6>
                            <span class="badge bg-secondary bg-opacity-10 text-dark rounded-pill px-3 py-1 small fw-bold">
                                {{ count($details) }} Baris Acara
                            </span>
                        </div>

                        <div class="card rounded-4 border overflow-hidden shadow-none mb-3" style="border-color: #e2e8f0 !important;">
                            <div class="table-responsive">
                                <table class="table table-modern align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center">No</th>
                                            <th width="12%">Jam</th>
                                            <th width="26%">Program Acara / Kegiatan</th>
                                            <th width="12%" class="text-center">Status</th>
                                            <th width="23%">Kendala & Alat</th>
                                            <th width="22%">Tindakan / Solusi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($details as $idx => $d)
                                        <tr>
                                            <td class="text-center fw-bold text-muted small">{{ $idx + 1 }}</td>
                                            <td>
                                                <span class="font-monospace fw-bold text-dark">
                                                    <i class="far fa-clock text-primary me-1"></i>
                                                    {{ \Carbon\Carbon::parse($d->jam)->format('H:i') }} WITA
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-dark d-block">{{ $d->kegiatan }}</strong>
                                                @if(!empty($d->catatan))
                                                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i>{{ $d->catatan }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($d->status == 'lancar')
                                                    <span class="badge badge-soft-success rounded-pill px-3 py-1 fw-semibold">
                                                        <i class="fas fa-check-circle me-1"></i> Lancar
                                                    </span>
                                                @elseif($d->status == 'kendala')
                                                    <span class="badge badge-soft-warning rounded-pill px-3 py-1 fw-semibold">
                                                        <i class="fas fa-exclamation-triangle me-1"></i> Kendala
                                                    </span>
                                                @else
                                                    <span class="badge badge-soft-danger rounded-pill px-3 py-1 fw-semibold">
                                                        <i class="fas fa-times-circle me-1"></i> Gagal
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($d->status != 'lancar' || !empty($d->jenis_kendala))
                                                    <div class="small">
                                                        <span class="badge bg-light text-dark border mb-1">
                                                            {{ strtoupper(str_replace('_', ' ', $d->jenis_kendala ?? '-')) }}
                                                        </span>
                                                        @if(!empty($d->nama_alat))
                                                            <div class="fw-semibold text-danger">
                                                                <i class="fas fa-tools me-1"></i> {{ $d->nama_alat }}
                                                            </div>
                                                        @endif
                                                        @if(!empty($d->deskripsi_kendala))
                                                            <div class="text-muted fst-italic mt-1">{{ $d->deskripsi_kendala }}</div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted small">- Tidak ada kendala -</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($d->solusi))
                                                    <div class="small text-dark">
                                                        <i class="fas fa-check text-success me-1"></i> {{ $d->solusi }}
                                                    </div>
                                                @else
                                                    <span class="text-muted small">- Sesuai SOP -</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted small">
                                                <i class="fas fa-info-circle me-1"></i> Belum ada data rincian kegiatan acara yang diinputkan.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- CATATAN KHUSUS (LOGBOOK) JIKA ADA --}}
                    @if(!empty($laporan->catatan_khusus))
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="fas fa-book-open text-primary me-2"></i> Catatan Khusus & Log Kejadian
                        </h6>
                        <div class="p-3 rounded-4 border bg-light text-dark" style="min-height: 90px; line-height: 1.6;">
                            <i class="fas fa-quote-left text-muted opacity-50 me-2"></i>
                            {{ $laporan->catatan_khusus }}
                        </div>
                    </div>
                    @endif

                    {{-- HISTORI PERMINTAAN REVISI --}}
                    @if($laporan->status_revisi != 'none' && isset($revisi) && $revisi)
                    <div class="card border-0 mb-4 rounded-4 overflow-hidden shadow-sm" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #f59e0b !important;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning text-white rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-0">Riwayat Pengajuan Permintaan Revisi</h6>
                                </div>
                                <span class="badge {{ $revisi->status === 'disetujui' ? 'badge-soft-success' : ($revisi->status === 'ditolak' ? 'badge-soft-danger' : 'badge-soft-warning') }} text-uppercase px-3 py-1 rounded-pill fw-bold">
                                    {{ $revisi->status }}
                                </span>
                            </div>
                            <div class="p-3 border rounded-3 bg-white text-dark mb-3">
                                <strong class="text-secondary small d-block mb-1">Keterangan / Alasan Permohonan:</strong>
                                <p class="mb-0 fs-6">{{ $revisi->alasan_revisi }}</p>
                            </div>
                            @if($revisi->status === 'ditolak' && !empty($revisi->alasan_penolakan))
                            <div class="p-3 border rounded-3 bg-danger bg-opacity-10 border-danger border-start border-4 text-dark mb-3">
                                <strong class="text-danger small d-block mb-1"><i class="fas fa-comment-dots me-1"></i>Alasan Penolakan dari Katim:</strong>
                                <p class="mb-0 fs-6 fst-italic">"{{ $revisi->alasan_penolakan }}"</p>
                            </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 small text-muted">
                                <div>
                                    <i class="fas fa-user-edit me-1 text-primary"></i> Diajukan oleh: <strong>{{ $revisi->nama_pemohon ?? $laporan->penginput }}</strong>
                                    pada {{ \Carbon\Carbon::parse($revisi->tanggal_pengajuan)->format('d M Y, H:i') }} WITA
                                </div>
                                @if($revisi->status !== 'pending' && $revisi->tanggal_persetujuan)
                                <div>
                                    <i class="fas fa-user-check me-1 text-success"></i> Ditinjau oleh: <strong>{{ $revisi->nama_penyetuju ?? 'Katim' }}</strong>
                                    pada {{ \Carbon\Carbon::parse($revisi->tanggal_persetujuan)->format('d M Y, H:i') }} WITA
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- TOMBOL TINDAKAN FOOTER --}}
                    <hr class="my-4 text-muted opacity-25">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            @if(Auth::user()->role == 'admin' || Auth::user()->id == $laporan->created_by)
                            <form action="{{ route('monitoring.destroy', $laporan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus laporan ini? Data yang terhapus tidak dapat dikembalikan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-soft-danger rounded-pill px-3" {{ $laporan->status_revisi == 'pending' ? 'disabled' : '' }}>
                                    <i class="fas fa-trash-alt me-1"></i> Hapus Laporan
                                </button>
                            </form>
                            @endif
                        </div>

                        <div class="d-flex gap-2">
                            @if(Auth::user()->id == $laporan->created_by && $laporan->status_revisi === 'none')
                            <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRevisi">
                                <i class="fas fa-redo me-2"></i> Ajukan Revisi
                            </button>
                            @elseif(Auth::user()->id == $laporan->created_by && $laporan->status_revisi === 'pending')
                            <span class="badge badge-soft-warning py-2 px-3 rounded-pill fw-semibold badge-pulse">
                                <i class="fas fa-hourglass-half me-1"></i> Menunggu Persetujuan Katim ({{ $laporan->nama_katim ?? 'Katim Terkait' }})
                            </span>
                            @endif

                            @if($laporan->status_revisi == 'pending')
                                @if($bisaApprove)
                                <form action="{{ route('monitoring.approve-revision', $laporan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Setujui revisi laporan ini?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                                        <i class="fas fa-check me-2"></i> Setujui Revisi
                                    </button>
                                </form>
                                <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRejectRevisiMonitoring">
                                    <i class="fas fa-times me-2"></i> Tolak Revisi
                                </button>
                                @endif
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajukan Revisi -->
<div class="modal fade" id="modalRevisi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('monitoring.request-revision', $laporan->id) }}" method="POST">
                @csrf
                <div class="modal-header py-3 px-4 border-0" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning text-white rounded-3 p-2 me-3">
                            <i class="fas fa-redo"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark fs-6">Ajukan Revisi Laporan</h5>
                            <small class="text-muted">No. SPO: {{ $laporan->no_surat ?? '-' }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Pengajuan revisi akan dikirim langsung ke <strong>{{ $laporan->nama_katim ?? 'Ketua Tim (Katim)' }}</strong> melalui sistem dan Telegram Bot. Setelah disetujui, tombol edit laporan akan terbuka.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Alasan Revisi <span class="text-danger">*</span></label>
                        <textarea name="alasan_revisi" class="form-control rounded-3" rows="4" placeholder="Jelaskan secara rinci alasan perbaikan data laporan..." required maxlength="500"></textarea>
                        <small class="text-muted">Maksimal 500 karakter</small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fas fa-paper-plane me-2"></i> Kirim Permintaan Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tolak Revisi Monitoring -->
<div class="modal fade" id="modalRejectRevisiMonitoring" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-start">
            <form action="{{ route('monitoring.reject-revision', $laporan->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header py-3 px-4 border-0" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger text-white rounded-3 p-2 me-3">
                            <i class="fas fa-times"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark fs-6">Tolak Permintaan Revisi Monitoring</h5>
                            <small class="text-muted">No. SPO: {{ $laporan->no_surat ?? '-' }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="alasan_penolakan" class="form-control rounded-3" rows="3" placeholder="Masukkan alasan mengapa permintaan revisi ini ditolak..." required></textarea>
                    </div>
                    <small class="text-muted"><i class="fab fa-telegram-plane text-primary me-1"></i> Alasan ini akan dikirimkan otomatis ke PD via Telegram.</small>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fas fa-times me-1"></i> Tolak Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
