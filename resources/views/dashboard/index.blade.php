@extends('layouts.backend')

@section('content')
<div class="container-fluid pb-5 px-0">

    <!-- HERO WELCOME BANNER -->
    <div class="card border-0 rounded-4 mb-4 overflow-hidden shadow-sm" 
         style="background: linear-gradient(135deg, #0f244a 0%, #1a3b70 55%, #2563eb 100%); color: #ffffff;">
        <div class="card-body p-4 p-md-5">

            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-3" 
                         style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25);">
                        <span class="dot-indicator bg-success" style="width: 8px; height: 8px; border-radius: 50%; display: inline-block;"></span>
                        <span class="small fw-semibold text-white">Sistem Informasi Manajemen Studio & Penyiaran</span>
                    </div>
                    <h2 class="fw-bold mb-2 text-white">Selamat Datang, {{ Auth::user()->name }}! 👋</h2>
                    <p class="mb-3 text-white text-opacity-80" style="max-width: 600px; font-size: 0.95rem;">
                        Kelola Surat Perintah Operasional (SPO), pantau kualitas teknis transmisi siaran, dan pantau tugas lapangan secara terintegrasi di LPP TVRI Stasiun Gorontalo.
                    </p>
                    <div class="d-flex flex-wrap gap-2 pt-1">
                        <span class="badge text-white px-3 py-2 rounded-pill font-monospace" 
                              style="background: rgba(255, 255, 255, 0.18); border: 1px solid rgba(255, 255, 255, 0.3); font-size: 0.8rem;">
                            <i class="fas fa-id-badge me-1"></i> NIP: {{ Auth::user()->nip ?? '-' }}
                        </span>
                        <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.8rem;">
                            <i class="fas fa-user-tag me-1"></i> Role: {{ strtoupper(Auth::user()->role) }}
                        </span>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="bg-white bg-opacity-10 rounded-4 p-3 d-inline-flex align-items-center gap-3 border border-white border-opacity-20 shadow-sm text-start">
                        <div class="bg-white rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px;">
                            <img src="{{ asset('images/logo.png') }}" 
                                 alt="Logo TVRI" 
                                 style="max-height: 34px; max-width: 34px; object-fit: contain;"
                                 onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/2/23/TVRI_2023.svg/1200px-TVRI_2023.svg.png';">
                        </div>
                        <div>
                            <small class="text-white text-opacity-75 d-block text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 1px;">Stasiun Penyiaran</small>
                            <div class="fs-6 fw-bold text-white mb-0">TVRI Gorontalo</div>
                            <small class="text-white text-opacity-80" style="font-size: 11px;">
                                <i class="far fa-clock me-1"></i> Zona WITA (UTC+8)
                            </small>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- PENDING REVISION NOTIFICATION (KHUSUS KATIM & ADMIN) -->
    @if(in_array(Auth::user()->role, ['katim', 'admin']) && (($stats['monitoring_revisi_pending'] ?? 0) > 0 || ($stats['spo_revisi_pending'] ?? 0) > 0))
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-warning bg-opacity-10 border-start border-warning border-4">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-warning text-white rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; flex-shrink: 0;">
                    <i class="fas fa-bell fa-lg"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Perhatian: Terdapat Permintaan Revisi Menunggu Keputusan Anda!</h6>
                    <p class="small text-muted mb-0">
                        @if(($stats['monitoring_revisi_pending'] ?? 0) > 0)
                            • <strong>{{ $stats['monitoring_revisi_pending'] }}</strong> laporan monitoring siaran meminta izin revisi.<br>
                        @endif
                        @if(($stats['spo_revisi_pending'] ?? 0) > 0)
                            • <strong>{{ $stats['spo_revisi_pending'] }}</strong> dokumen SPO meminta izin revisi kru.
                        @endif
                    </p>
                </div>
            </div>
            <div class="d-flex gap-2">
                @if(($stats['monitoring_revisi_pending'] ?? 0) > 0)
                <a href="{{ route('monitoring.index') }}?status_revisi=pending" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold shadow-sm">
                    <i class="fas fa-desktop me-1"></i> Tinjau Monitoring
                </a>
                @endif
                @if(($stats['spo_revisi_pending'] ?? 0) > 0)
                <a href="{{ route('spo.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm">
                    <i class="fas fa-envelope-open-text me-1"></i> Tinjau SPO
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- SPO DITOLAK MEMERLUKAN PERBAIKAN (KHUSUS KRU) -->
    @if(Auth::user()->role == 'kru' && count($spoDitolak ?? []) > 0)
    <div class="card border-0 rounded-4 shadow-sm mb-4 border-start border-danger border-4" style="background-color: #fff5f5;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-danger text-white rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 46px; height: 46px; flex-shrink: 0;">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-danger">Perhatian: Terdapat {{ count($spoDitolak) }} Dokumen SPO Anda Ditolak oleh Katim!</h6>
                        <small class="text-muted">Silakan periksa alasan penolakan Katim di bawah, perbaiki data SPO, dan ajukan kembali.</small>
                    </div>
                </div>
            </div>

            <div class="list-group list-group-flush rounded-3 border">
                @foreach($spoDitolak as $sd)
                <div class="list-group-item p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 bg-white">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-danger text-white px-2 py-1"><i class="fas fa-times-circle me-1"></i>Ditolak</span>
                            <span class="fw-bold text-dark">{{ $sd->no_surat }}</span>
                            <span class="text-muted small">&bull; {{ $sd->hari }}, {{ date('d M Y', strtotime($sd->tanggal_pelaksanaan)) }}</span>
                        </div>
                        <div class="p-2 rounded bg-light border-start border-danger border-3 my-2">
                            <div class="small text-danger fw-bold mb-1">
                                <i class="fas fa-comment-dots me-1"></i>Alasan Penolakan oleh Katim ({{ $sd->nama_katim ?? 'Katim' }}):
                            </div>
                            <div class="text-dark small fst-italic">
                                "{{ $sd->alasan_penolakan ?? 'Tidak ada catatan alasan yang dilampirkan.' }}"
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <a href="{{ route('spo.edit', $sd->id) }}" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-sm">
                            <i class="fas fa-edit me-1"></i> Perbaiki SPO
                        </a>
                        <a href="{{ route('spo.verify', $sd->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="fas fa-eye me-1"></i> Detail
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- PERMINTAAN REVISI DITOLAK (UNTUK KRU & PD) -->
    @if(count($revisiDitolak ?? []) > 0)
    <div class="card border-0 rounded-4 shadow-sm mb-4 border-start border-warning border-4" style="background-color: #fffdf5;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="bg-warning text-white rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 46px; height: 46px; flex-shrink: 0;">
                    <i class="fas fa-ban fa-lg"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Pemberitahuan: Permintaan Revisi Anda Ditolak</h6>
                    <small class="text-muted">Katim tidak menyetujui permintaan izin buka revisi untuk pengajuan berikut:</small>
                </div>
            </div>
            <div class="list-group list-group-flush rounded-3 border">
                @foreach($revisiDitolak as $rd)
                <div class="list-group-item p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-secondary text-uppercase">{{ $rd->jenis_dokumen }} (ID: #{{ $rd->dokumen_id }})</span>
                        <small class="text-muted">{{ date('d M Y H:i', strtotime($rd->updated_at)) }} WITA</small>
                    </div>
                    <p class="small text-muted mb-1"><strong>Alasan Pengajuan Anda:</strong> {{ $rd->alasan_revisi ?? '-' }}</p>
                    <div class="p-2 rounded bg-light border-start border-warning border-3">
                        <span class="small text-danger fw-bold"><i class="fas fa-comment-slash me-1"></i>Alasan Penolakan oleh Katim ({{ $rd->nama_katim ?? 'Katim' }}):</span>
                        <div class="small text-dark fst-italic">"{{ $rd->alasan_penolakan ?? 'Permintaan revisi tidak disetujui.' }}"</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- STATISTIC CARDS (NON-KRU) -->
    @if(Auth::user()->role != 'kru')

        @if(Auth::user()->role === 'td' || Auth::user()->role === 'pd')
        {{-- ============================================================== --}}
        {{-- KARTU STATISTIK KHUSUS TD (PENGARAH TEKNIK)                   --}}
        {{-- ============================================================== --}}
        <div class="row g-3 mb-4">
            
            <!-- CARD 1: TUGAS SPO BELUM DILAPORKAN (ACTIVE TASK) -->
            <div class="col-sm-6 col-xl-3">
                <div class="card card-modern card-hover h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="small text-uppercase fw-bold text-muted" style="letter-spacing: 0.5px; font-size: 11px;">Tugas SPO Belum Lapor</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: #f59e0b; color: #ffffff;">
                                <i class="fas fa-clipboard-list fa-lg"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h2 class="fw-bold mb-0 text-dark" style="font-size: 2.2rem;">{{ $tdStats['spo_aktif'] ?? 0 }}</h2>
                            <span class="text-muted small">tugas</span>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">Perlu diinput</small>
                            <a href="{{ route('monitoring.create') }}" class="small fw-bold text-warning text-decoration-none">
                                Input Sekarang <i class="fas fa-plus ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 2: LAPORAN SELESAI DIBUAT -->
            <div class="col-sm-6 col-xl-3">
                <div class="card card-modern card-hover h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #10b981, #34d399);"></div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="small text-uppercase fw-bold text-muted" style="letter-spacing: 0.5px; font-size: 11px;">Laporan Saya Selesai</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: #10b981; color: #ffffff;">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h2 class="fw-bold mb-0 text-dark" style="font-size: 2.2rem;">{{ $tdStats['laporan_selesai'] ?? 0 }}</h2>
                            <span class="text-muted small">selesai</span>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">Laporan dibuat</small>
                            <a href="{{ route('monitoring.index') }}" class="small fw-bold text-success text-decoration-none">
                                Buka Arsip <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 3: KENDALA SIARAN TERCATAT -->
            <div class="col-sm-6 col-xl-3">
                <div class="card card-modern card-hover h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #ef4444, #f87171);"></div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="small text-uppercase fw-bold text-muted" style="letter-spacing: 0.5px; font-size: 11px;">Kendala Siaran Saya</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: #ef4444; color: #ffffff;">
                                <i class="fas fa-exclamation-triangle fa-lg"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h2 class="fw-bold mb-0 text-dark" style="font-size: 2.2rem;">{{ $tdStats['laporan_kendala'] ?? 0 }}</h2>
                            <span class="text-muted small">kejadian</span>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">Evaluasi teknik</small>
                            <a href="{{ route('monitoring.index') }}?status_siaran=kendala" class="small fw-bold text-danger text-decoration-none">
                                Audit Kendala <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 4: REVISI PENDING -->
            <div class="col-sm-6 col-xl-3">
                <div class="card card-modern card-hover h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #3b82f6, #60a5fa);"></div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="small text-uppercase fw-bold text-muted" style="letter-spacing: 0.5px; font-size: 11px;">Revisi Pending</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: #3b82f6; color: #ffffff;">
                                <i class="fas fa-sync-alt fa-lg"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h2 class="fw-bold mb-0 text-dark" style="font-size: 2.2rem;">{{ $tdStats['revisi_pending'] ?? 0 }}</h2>
                            <span class="text-muted small">diajukan</span>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">Menunggu Katim</small>
                            <a href="{{ route('monitoring.index') }}?status_revisi=pending" class="small fw-bold text-primary text-decoration-none">
                                Tinjau <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        @else
        {{-- ============================================================== --}}
        {{-- KARTU STATISTIK ADMIN & KATIM                                  --}}
        {{-- ============================================================== --}}
        <div class="row g-3 mb-4">
            
            <!-- CARD 1: SPO PENDING -->
            <div class="col-sm-6 col-xl-3">
                <div class="card card-modern card-hover h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="small text-uppercase fw-bold text-muted" style="letter-spacing: 0.5px; font-size: 11px;">SPO Menunggu</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: #f59e0b; color: #ffffff;">
                                <i class="fas fa-hourglass-half fa-lg"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h2 class="fw-bold mb-0 text-dark" style="font-size: 2.2rem;">{{ $stats['spo_pending'] }}</h2>
                            <span class="text-muted small">dokumen</span>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">Status persetujuan</small>
                            <a href="{{ route('spo.index') }}" class="small fw-bold text-warning text-decoration-none">
                                Tinjau <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 2: SPO APPROVED -->
            <div class="col-sm-6 col-xl-3">
                <div class="card card-modern card-hover h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #10b981, #34d399);"></div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="small text-uppercase fw-bold text-muted" style="letter-spacing: 0.5px; font-size: 11px;">SPO Disetujui</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: #10b981; color: #ffffff;">
                                <i class="fas fa-check-double fa-lg"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h2 class="fw-bold mb-0 text-dark" style="font-size: 2.2rem;">{{ $stats['spo_approved'] }}</h2>
                            <span class="text-muted small">berlaku</span>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">Siap operasional</small>
                            <a href="{{ route('spo.index') }}" class="small fw-bold text-success text-decoration-none">
                                Lihat Arsip <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 3: REVISI MONITORING -->
            <div class="col-sm-6 col-xl-3">
                <div class="card card-modern card-hover h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #3b82f6, #60a5fa);"></div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="small text-uppercase fw-bold text-muted" style="letter-spacing: 0.5px; font-size: 11px;">Revisi Monitoring</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: #3b82f6; color: #ffffff;">
                                <i class="fas fa-sync-alt fa-lg"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h2 class="fw-bold mb-0 text-dark" style="font-size: 2.2rem;">{{ $stats['monitoring_revisi_pending'] }}</h2>
                            <span class="text-muted small">pending</span>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">Menunggu Katim</small>
                            <a href="{{ route('monitoring.index') }}?status_revisi=pending" class="small fw-bold text-primary text-decoration-none">
                                Buka Daftar <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 4: KENDALA TEKNIS -->
            <div class="col-sm-6 col-xl-3">
                <div class="card card-modern card-hover h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #ef4444, #f87171);"></div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="small text-uppercase fw-bold text-muted" style="letter-spacing: 0.5px; font-size: 11px;">Kendala Siaran</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: #ef4444; color: #ffffff;">
                                <i class="fas fa-exclamation-triangle fa-lg"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h2 class="fw-bold mb-0 text-dark" style="font-size: 2.2rem;">{{ $stats['laporan_kendala'] }}</h2>
                            <span class="text-muted small">kejadian</span>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">Log evaluasi teknik</small>
                            <a href="{{ route('monitoring.index') }}?status_siaran=kendala" class="small fw-bold text-danger text-decoration-none">
                                Audit Kendala <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        @endif

    @endif

    @if(Auth::user()->role === 'admin')
    <!-- ============================================================== -->
    <!-- DIAGRAM ANALITIK OPERASIONAL (KHUSUS DASHBOARD ADMIN)          -->
    <!-- ============================================================== -->
    <div class="row g-4 mb-4">

        <!-- DIAGRAM 1: DISTRIBUSI STATUS PERSETUJUAN SPO -->
        <div class="col-lg-6">
            <div class="card card-modern h-100 border-0 shadow-sm">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark m-0">
                            <i class="fas fa-chart-pie text-primary me-2"></i>Distribusi Status Pengesahan SPO
                        </h6>
                        <small class="text-muted">Proporsi berkas dinas yang disetujui, menunggu, & ditolak</small>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1.5 rounded-pill fw-semibold" style="font-size: 0.78rem;">
                        <i class="fas fa-file-alt me-1"></i>Total: {{ $totalSPO }} SPO
                    </span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="position-relative d-flex justify-content-center align-items-center my-2" style="min-height: 250px;">
                        <canvas id="spoStatusChart" style="max-height: 240px; max-width: 240px;"></canvas>
                    </div>
                    <div class="row g-2 pt-3 border-top mt-2 text-center">
                        <div class="col-4">
                            <div class="p-2.5 rounded-3 bg-light border">
                                <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                                    <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background: #10b981;"></span>
                                    <small class="text-muted fw-semibold" style="font-size: 11px;">Disetujui</small>
                                </div>
                                <h5 class="fw-bold text-success mb-0">{{ $chartSPO['approved'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2.5 rounded-3 bg-light border">
                                <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                                    <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background: #f59e0b;"></span>
                                    <small class="text-muted fw-semibold" style="font-size: 11px;">Menunggu</small>
                                </div>
                                <h5 class="fw-bold text-warning mb-0">{{ $chartSPO['pending'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2.5 rounded-3 bg-light border">
                                <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                                    <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background: #ef4444;"></span>
                                    <small class="text-muted fw-semibold" style="font-size: 11px;">Ditolak</small>
                                </div>
                                <h5 class="fw-bold text-danger mb-0">{{ $chartSPO['rejected'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DIAGRAM 2: EVALUASI KUALITAS KETERLAKSANAAN SIARAN -->
        <div class="col-lg-6">
            <div class="card card-modern h-100 border-0 shadow-sm">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark m-0">
                            <i class="fas fa-broadcast-tower text-success me-2"></i>Evaluasi Keterlaksanaan Siaran Harian
                        </h6>
                        <small class="text-muted">Kualitas transmisi siaran dari laporan Pengarah Teknik (TD)</small>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 rounded-pill fw-semibold" style="font-size: 0.78rem;">
                        <i class="fas fa-video me-1"></i>Total: {{ $totalLaporan }} Laporan
                    </span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="position-relative d-flex justify-content-center align-items-center my-2" style="min-height: 250px;">
                        <canvas id="monitoringStatusChart" style="max-height: 240px; max-width: 240px;"></canvas>
                    </div>
                    <div class="row g-2 pt-3 border-top mt-2 text-center">
                        <div class="col-4">
                            <div class="p-2.5 rounded-3 bg-light border">
                                <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                                    <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background: #10b981;"></span>
                                    <small class="text-muted fw-semibold" style="font-size: 11px;">Siaran Sukses</small>
                                </div>
                                <h5 class="fw-bold text-success mb-0">{{ $chartMonitoring['sukses'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2.5 rounded-3 bg-light border">
                                <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                                    <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background: #f59e0b;"></span>
                                    <small class="text-muted fw-semibold" style="font-size: 11px;">Ada Kendala</small>
                                </div>
                                <h5 class="fw-bold text-warning mb-0">{{ $chartMonitoring['kendala'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2.5 rounded-3 bg-light border">
                                <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                                    <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background: #ef4444;"></span>
                                    <small class="text-muted fw-semibold" style="font-size: 11px;">Gagal Siar</small>
                                </div>
                                <h5 class="fw-bold text-danger mb-0">{{ $chartMonitoring['gagal'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @endif

    <!-- QUICK ACTIONS & SYSTEM SHORTCUTS -->
    <div class="row g-4 mb-4">
        
        <div class="col-lg-8">
            <div class="card card-modern h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark m-0">
                        <i class="fas fa-bolt text-warning me-2"></i>Aksi Cepat Sistem (Quick Actions)
                    </h6>
                    <span class="badge bg-light text-muted border">Navigasi Utama</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        
                        @if(Auth::user()->role === 'td' || Auth::user()->role === 'pd')
                        <div class="col-sm-6">
                            <a href="{{ route('monitoring.create') }}" class="card card-modern card-hover text-decoration-none p-3 h-100 d-flex flex-row align-items-center gap-3 border">
                                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px; background: #2563eb; color: #ffffff;">
                                    <i class="fas fa-plus fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Input Laporan Siaran</h6>
                                    <small class="text-muted">Buat log monitoring harian Pengarah Teknik</small>
                                </div>
                            </a>
                        </div>
                        @endif

                        @if(Auth::user()->role === 'kru')
                        <div class="col-sm-6">
                            <a href="{{ route('spo.create') }}" class="card card-modern card-hover text-decoration-none p-3 h-100 d-flex flex-row align-items-center gap-3 border">
                                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px; background: #2563eb; color: #ffffff;">
                                    <i class="fas fa-file-contract fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Buat Pengajuan SPO</h6>
                                    <small class="text-muted">Ajukan jadwal & kru penugasan</small>
                                </div>
                            </a>
                        </div>
                        @endif

                        @if(in_array(Auth::user()->role, ['admin', 'katim']))
                        <div class="col-sm-6">
                            <a href="{{ route('spo.index') }}" class="card card-modern card-hover text-decoration-none p-3 h-100 d-flex flex-row align-items-center gap-3 border">
                                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px; background: #2563eb; color: #ffffff;">
                                    <i class="fas fa-tasks fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Tinjau Pengajuan SPO</h6>
                                    <small class="text-muted">Verifikasi dokumen operasional</small>
                                </div>
                            </a>
                        </div>
                        @endif

                        <div class="col-sm-6">
                            <a href="{{ route('monitoring.index') }}" class="card card-modern card-hover text-decoration-none p-3 h-100 d-flex flex-row align-items-center gap-3 border">
                                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px; background: #0891b2; color: #ffffff;">
                                    <i class="fas fa-desktop fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Riwayat Monitoring</h6>
                                    <small class="text-muted">Lihat seluruh arsip siaran</small>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6">
                            <a href="{{ route('spo.index') }}" class="card card-modern card-hover text-decoration-none p-3 h-100 d-flex flex-row align-items-center gap-3 border">
                                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px; background: #059669; color: #ffffff;">
                                    <i class="fas fa-clipboard-check fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Arsip Seluruh SPO</h6>
                                    <small class="text-muted">Periksa status & unduh PDF</small>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6">
                            <a href="{{ route('monitoring.export') }}" class="card card-modern card-hover text-decoration-none p-3 h-100 d-flex flex-row align-items-center gap-3 border">
                                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px; background: #16a34a; color: #ffffff;">
                                    <i class="fas fa-file-excel fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Export Laporan Excel</h6>
                                    <small class="text-muted">Unduh rekap operasional</small>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6">
                            <a href="{{ route('monitoring.export.pdf') }}" class="card card-modern card-hover text-decoration-none p-3 h-100 d-flex flex-row align-items-center gap-3 border">
                                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px; background: #dc2626; color: #ffffff;">
                                    <i class="fas fa-file-pdf fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Cetak Rekap PDF</h6>
                                    <small class="text-muted">Unduh rekap evaluasi siaran PDF</small>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6">
                            <a href="{{ route('profile.edit') }}" class="card card-modern card-hover text-decoration-none p-3 h-100 d-flex flex-row align-items-center gap-3 border">
                                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px; background: #3b82f6; color: #ffffff;">
                                    <i class="fab fa-telegram-plane fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Integrasi Telegram</h6>
                                    <small class="text-muted">Hubungkan notifikasi bot</small>
                                </div>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT WIDGET: SYSTEM INFO & BOT STATUS -->
        <div class="col-lg-4">
            <div class="card card-modern h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold text-dark m-0">
                        <i class="fas fa-server text-primary me-2"></i>Status Sistem & Notifikasi
                    </h6>
                </div>
                <div class="card-body p-4">
                    
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-4 bg-light mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; background: #2563eb; color: #ffffff;">
                                <i class="fab fa-telegram-plane"></i>
                            </div>
                            <div>
                                <strong class="d-block text-dark small">Telegram Bot TVRI</strong>
                                <small class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i>Aktif & Terhubung</small>
                            </div>
                        </div>
                        <a href="https://t.me/TvriGorontalo_Bot" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                            Buka Bot
                        </a>
                    </div>

                    <div class="p-3 rounded-4 border bg-white mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted fw-semibold">Akun Telegram Anda:</span>
                            @if(Auth::user()->telegram_chat_id)
                                <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3);">
                                    <i class="fas fa-link me-1"></i> Terhubung
                                </span>
                            @else
                                <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: rgba(239, 68, 68, 0.15); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.3);">
                                    <i class="fas fa-unlink me-1"></i> Belum Terhubung
                                </span>
                            @endif
                        </div>
                        @if(!Auth::user()->telegram_chat_id)
                            <small class="text-muted d-block mt-2" style="line-height: 1.5;">
                                Anda belum menghubungkan akun Telegram. Buka profil untuk mendapatkan token binding agar menerima notifikasi langsung di HP.
                            </small>
                            <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-warning w-100 mt-2 fw-bold rounded-pill">
                                <i class="fas fa-key me-1"></i>Hubungkan Sekarang
                            </a>
                        @else
                            <small class="text-muted d-block mt-1">
                                ID Chat: <code>{{ Auth::user()->telegram_chat_id }}</code> (Notifikasi instan aktif)
                            </small>
                        @endif
                    </div>

                    <div class="text-center pt-2">
                        <small class="text-muted font-monospace" style="font-size: 11px;">
                            SIMS TVRI Gorontalo &bull; Server Operasional Normal
                        </small>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- TAMPILAN KHUSUS TD (PENUGASAN PENGARAH TEKNIK & LAPORAN TERAKHIR SAYA) -->
    @if(Auth::user()->role == 'td' || Auth::user()->role == 'pd')
    <div class="row g-4 mb-4" id="jadwal-penugasan-td">
        <!-- WIDGET 1: PENUGASAN SPO PENGARAH TEKNIK -->
        <div class="col-lg-6">
            <div class="card card-modern h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark m-0">
                            <i class="fas fa-clipboard-check text-primary me-2"></i>Jadwal Tugas Pengarah Teknik (SPO)
                        </h6>
                        <small class="text-muted">Daftar SPO Approved yang menugaskan Anda sebagai TD.</small>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                        {{ count($tugasTD ?? []) }} Penugasan
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse(array_slice($tugasTD ?? [], 0, 5) as $tugas)
                            <div class="list-group-item p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-4 d-flex flex-column align-items-center justify-content-center me-3 shadow-sm" style="width: 58px; height: 58px; flex-shrink: 0;">
                                        <span class="fw-bold fs-5 leading-none mb-0">{{ date('d', strtotime($tugas->tanggal_pelaksanaan)) }}</span>
                                        <small class="text-uppercase fw-bold text-white text-opacity-75" style="font-size: 8.5px;">{{ date('M Y', strtotime($tugas->tanggal_pelaksanaan)) }}</small>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="fw-bold text-dark">{{ $tugas->hari }}</span>
                                            @if(!$tugas->has_laporan)
                                                <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-25 px-2 py-0.5" style="font-size: 11px;">
                                                    <i class="fas fa-clock me-1"></i>Belum Dilaporkan
                                                </span>
                                            @else
                                                <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-2 py-0.5" style="font-size: 11px;">
                                                    <i class="fas fa-check-circle me-1"></i>Laporan Selesai
                                                </span>
                                            @endif
                                        </div>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-file-signature me-1"></i>{{ $tugas->no_surat }}
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-shrink-0">
                                    @if(!$tugas->has_laporan)
                                        <a href="{{ route('monitoring.create') }}" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm">
                                            <i class="fas fa-plus me-1"></i> Input Laporan
                                        </a>
                                    @else
                                        <a href="{{ route('monitoring.show', $tugas->monitoring_id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="fas fa-eye me-1"></i> Detail
                                        </a>
                                    @endif
                                    <a href="{{ route('spo.verify', $tugas->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="Lihat SPO">
                                        <i class="fas fa-file-alt"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <div class="bg-light rounded-circle p-3 d-inline-flex mb-3">
                                    <i class="fas fa-calendar-times fa-3x text-muted opacity-50"></i>
                                </div>
                                <h6 class="fw-bold text-muted mb-1">Belum Ada Penugasan SPO</h6>
                                <p class="small text-muted mb-0">Nama Anda belum ditugaskan sebagai Pengarah Teknik pada SPO disetujui.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- WIDGET 2: 5 LAPORAN MONITORING TERAKHIR SAYA -->
        <div class="col-lg-6">
            <div class="card card-modern h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark m-0">
                            <i class="fas fa-desktop text-success me-2"></i>Riwayat Laporan Monitoring Terakhir
                        </h6>
                        <small class="text-muted">5 laporan monitoring siaran terakhir yang Anda buat.</small>
                    </div>
                    <a href="{{ route('monitoring.index') }}" class="small fw-bold text-success text-decoration-none">
                        Semua Arsip <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($laporanTerbaruTD ?? [] as $lap)
                            <div class="list-group-item p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="fw-bold text-dark">
                                            {{ date('d M Y', strtotime($lap->tanggal)) }}
                                        </span>
                                        @if($lap->status_siaran === 'sukses')
                                            <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-2 py-0.5" style="font-size: 11px;">
                                                <i class="fas fa-check-circle me-1"></i>Sukses Berjalan
                                            </span>
                                        @elseif($lap->status_siaran === 'kendala')
                                            <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-25 px-2 py-0.5" style="font-size: 11px;">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Dengan Kendala
                                            </span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 px-2 py-0.5" style="font-size: 11px;">
                                                <i class="fas fa-times-circle me-1"></i>Gagal Tayang
                                            </span>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block">
                                        <i class="fas fa-file-contract me-1"></i>{{ $lap->no_surat ?? 'SPO #' . $lap->spo_id }} ({{ $lap->hari ?? '-' }})
                                    </small>
                                </div>
                                <div class="d-flex gap-2 flex-shrink-0">
                                    <a href="{{ route('monitoring.download.pdf', $lap->id) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Unduh PDF Resmi">
                                        <i class="fas fa-file-pdf me-1"></i> PDF
                                    </a>
                                    <a href="{{ route('monitoring.show', $lap->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <div class="bg-light rounded-circle p-3 d-inline-flex mb-3">
                                    <i class="fas fa-clipboard-list fa-3x text-muted opacity-50"></i>
                                </div>
                                <h6 class="fw-bold text-muted mb-1">Belum Ada Laporan Monitoring</h6>
                                <p class="small text-muted mb-2">Anda belum membuat laporan monitoring siaran.</p>
                                <a href="{{ route('monitoring.create') }}" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                                    <i class="fas fa-plus me-1"></i> Buat Laporan Pertama
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- TAMPILAN KHUSUS KRU (JADWAL PENUGASAN MENDATANG) -->
    @if(Auth::user()->role == 'kru')
    <div class="row">
        <div class="col-12">
            <div class="card card-modern">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-primary m-0"><i class="fas fa-calendar-check me-2"></i>Jadwal Penugasan Saya (SPO Disetujui)</h6>
                        <small class="text-muted">Daftar siaran mendatang di mana Anda ditugaskan bertugas.</small>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                        {{ count($tugasSaya) }} Tugas Terdaftar
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($tugasSaya as $tugas)
                            <div class="list-group-item p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-4 d-flex flex-column align-items-center justify-content-center me-3 shadow-sm border border-primary" style="width: 65px; height: 65px; flex-shrink: 0;">
                                        <span class="fw-bold fs-4 leading-none mb-0">{{ date('d', strtotime($tugas->tanggal_pelaksanaan)) }}</span>
                                        <small class="text-uppercase fw-bold text-white text-opacity-75" style="font-size: 9px;">{{ date('M Y', strtotime($tugas->tanggal_pelaksanaan)) }}</small>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">{{ $tugas->hari }} — Operasional Siaran TVRI Gorontalo</h6>
                                        <div class="d-flex flex-wrap gap-2 align-items-center">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                                <i class="fas fa-user-tag me-1"></i> Posisi: <strong>{{ $tugas->peran_saya }}</strong>
                                            </span>
                                            <span class="small text-muted">
                                                <i class="fas fa-file-alt me-1"></i> No. SPO: <strong>{{ $tugas->no_surat }}</strong>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('spo.verify', $tugas->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">
                                        <i class="fas fa-eye me-1"></i> Lihat Dokumen
                                    </a>
                                    <a href="{{ route('spo.download', $tugas->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm">
                                        <i class="fas fa-download me-1"></i> Unduh PDF
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <div class="bg-light rounded-circle p-3 d-inline-flex mb-3">
                                    <i class="fas fa-calendar-times fa-3x text-muted opacity-50"></i>
                                </div>
                                <h6 class="fw-bold text-muted mb-1">Belum Ada Jadwal Tugas Mendatang</h6>
                                <p class="small text-muted mb-0">Nama Anda belum terdaftar pada SPO yang berstatus disetujui dalam waktu dekat.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@if(Auth::user()->role === 'admin')
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // FONT DEFAULT INTER
    Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";

    // 1. CHART DISTRIBUSI STATUS PENGESAHAN SPO
    const ctxSPO = document.getElementById('spoStatusChart');
    if (ctxSPO) {
        const approvedCount = {{ $chartSPO['approved'] ?? 0 }};
        const pendingCount = {{ $chartSPO['pending'] ?? 0 }};
        const rejectedCount = {{ $chartSPO['rejected'] ?? 0 }};
        const totalSPOVal = approvedCount + pendingCount + rejectedCount;

        const spoData = {
            labels: ['Disetujui', 'Menunggu', 'Ditolak'],
            datasets: [{
                data: [approvedCount, pendingCount, rejectedCount],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                hoverBackgroundColor: ['#059669', '#d97706', '#dc2626'],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        };

        new Chart(ctxSPO, {
            type: 'doughnut',
            data: totalSPOVal > 0 ? spoData : {
                labels: ['Belum Ada Data'],
                datasets: [{
                    data: [1],
                    backgroundColor: ['#e2e8f0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                            font: { size: 12, weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                if (totalSPOVal === 0) return ' Belum ada data pengajuan SPO';
                                const value = context.raw || 0;
                                const percentage = ((value / totalSPOVal) * 100).toFixed(1);
                                return ` ${context.label}: ${value} dokumen (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. CHART EVALUASI KETERLAKSANAAN SIARAN HARIAN
    const ctxMonitoring = document.getElementById('monitoringStatusChart');
    if (ctxMonitoring) {
        const suksesCount = {{ $chartMonitoring['sukses'] ?? 0 }};
        const kendalaCount = {{ $chartMonitoring['kendala'] ?? 0 }};
        const gagalCount = {{ $chartMonitoring['gagal'] ?? 0 }};
        const totalMonVal = suksesCount + kendalaCount + gagalCount;

        const monData = {
            labels: ['Siaran Sukses', 'Ada Kendala', 'Gagal Siar'],
            datasets: [{
                data: [suksesCount, kendalaCount, gagalCount],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                hoverBackgroundColor: ['#059669', '#d97706', '#dc2626'],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        };

        new Chart(ctxMonitoring, {
            type: 'doughnut',
            data: totalMonVal > 0 ? monData : {
                labels: ['Belum Ada Data'],
                datasets: [{
                    data: [1],
                    backgroundColor: ['#e2e8f0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                            font: { size: 12, weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                if (totalMonVal === 0) return ' Belum ada laporan monitoring';
                                const value = context.raw || 0;
                                const percentage = ((value / totalMonVal) * 100).toFixed(1);
                                return ` ${context.label}: ${value} laporan (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endif
