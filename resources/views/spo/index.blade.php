@extends('layouts.backend')

@section('content')
<div class="container-fluid">

    {{-- HEADER HALAMAN --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-soft-primary px-3 py-1 rounded-pill small fw-semibold">
                    <i class="fas fa-file-signature me-1"></i> Operasional Dokumen
                </span>
            </div>
            <h1 class="h3 mb-1 mt-2 text-gray-800 fw-bold">Arsip Surat Perintah Operasional (SPO)</h1>
            <p class="text-muted small mb-0">Daftar penerbitan, penugasan teknisi, dan persetujuan penyiaran TVRI Gorontalo.</p>
        </div>
        
        @if(Auth::user()->role == 'kru')
        <div class="mt-3 mt-sm-0">
            <a href="{{ route('spo.create') }}" class="btn btn-gradient-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-2"></i> Buat SPO Baru
            </a>
        </div>
        @endif
    </div>

    {{-- ========================================== --}}
    {{-- TAMPILAN KHUSUS KRU (1 TABEL)              --}}
    {{-- ========================================== --}}
    @if(Auth::user()->role == 'kru')
    <div class="card card-modern overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-2 me-3" style="background: rgba(26,59,112,0.08); color: var(--primary-navy);">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h6 class="m-0 fw-bold text-dark">Daftar Pengajuan SPO Saya</h6>
                    <small class="text-muted">Total <strong>{{ count($spos) }}</strong> dokumen diterbitkan</small>
                </div>
            </div>
            <span class="badge badge-soft-primary rounded-pill px-3 py-2 fw-semibold">
                {{ count($spos) }} Dokumen
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-modern align-middle mb-0" style="min-width: 800px;">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="25%">Nomor Surat & Pembuat</th>
                            <th width="20%">Jadwal Pelaksanaan</th>
                            <th width="20%">Pengarah Teknik</th>
                            <th width="15%">Status Dokumen</th>
                            <th width="15%" class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($spos as $index => $spo)
                        <tr>
                            <td class="text-center fw-bold text-muted small">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark small">{{ $spo->no_surat }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">
                                        <i class="fas fa-user-circle me-1 text-primary"></i> {{ $spo->nama_pembuat }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-dark small">{{ $spo->hari }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">
                                        <i class="fas fa-calendar-day me-1"></i> {{ date('d M Y', strtotime($spo->tanggal_pelaksanaan)) }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="small fw-semibold text-dark">{{ $spo->pengarah_teknik }}</span>
                            </td>
                            <td>
                                @if(array_key_exists($spo->id, $revisiPending ?? []))
                                    <span class="badge badge-soft-warning rounded-pill px-3 py-1 mb-1 d-inline-block fw-semibold badge-pulse">
                                        <i class="fas fa-sync fa-spin me-1"></i> Revisi Diajukan
                                    </span>
                                    <span class="text-muted d-block" style="font-size: 0.72rem;">
                                        <i class="fas fa-user-shield me-1"></i> Ke: {{ $spo->nama_katim ?? 'Ketua Tim' }}
                                    </span>
                                @elseif(array_key_exists($spo->id, $revisiDisetujui ?? []))
                                    <span class="badge badge-soft-primary rounded-pill px-3 py-1 mb-1 d-inline-block fw-semibold">
                                        <i class="fas fa-edit me-1"></i> Revisi Disetujui
                                    </span>
                                    <span class="text-success d-block fw-bold" style="font-size: 0.72rem;">Silakan Edit Data</span>
                                @elseif($spo->status_persetujuan == 'pending')
                                    <span class="badge badge-soft-warning rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-hourglass-half me-1"></i> Menunggu Katim
                                    </span>
                                @elseif($spo->status_persetujuan == 'approved')
                                    <span class="badge badge-soft-success rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-check-circle me-1"></i> Disetujui
                                    </span>
                                @else
                                    <span class="badge badge-soft-danger rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-times-circle me-1"></i> Ditolak
                                    </span>
                                    @if(!empty($spo->alasan_penolakan))
                                        <div class="mt-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" style="font-size: 0.72rem;" data-bs-toggle="modal" data-bs-target="#modalDetailAlasanKru{{ $spo->id }}" title="Lihat Alasan Penolakan">
                                                <i class="fas fa-comment-dots me-1"></i> Alasan Katim
                                            </button>
                                        </div>

                                        <div class="modal fade" id="modalDetailAlasanKru{{ $spo->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-start">
                                                    <div class="modal-header py-3 px-4 border-0" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-danger text-white rounded-3 p-2 me-3">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="modal-title fw-bold text-dark fs-6">Alasan Penolakan SPO</h5>
                                                                <small class="text-muted">No. Surat: {{ $spo->no_surat }}</small>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4 text-start">
                                                        <div class="p-3 bg-light rounded-3 border-start border-danger border-4 mb-3">
                                                            <label class="small text-danger fw-bold d-block mb-1">
                                                                <i class="fas fa-comment-dots me-1"></i>Catatan Katim ({{ $spo->nama_katim ?? 'Katim' }}):
                                                            </label>
                                                            <p class="mb-0 text-dark small" style="white-space: pre-wrap;">{{ $spo->alasan_penolakan }}</p>
                                                        </div>
                                                        <div class="alert alert-warning border-0 rounded-3 small mb-0">
                                                            <i class="fas fa-info-circle me-1"></i> Silakan klik tombol <strong>Perbaiki SPO</strong> untuk memperbarui data sesuai arahan Katim.
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light border-0 py-2 px-4">
                                                        <a href="{{ route('spo.edit', $spo->id) }}" class="btn btn-warning rounded-pill px-4 btn-sm fw-bold">
                                                            <i class="fas fa-edit me-1"></i> Perbaiki SPO
                                                        </a>
                                                        <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('spo.verify', $spo->id) }}" target="_blank" class="btn btn-soft-primary btn-action-icon" title="Lihat Detail & Lembar SPO">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($spo->status_persetujuan == 'approved')
                                        <a href="{{ route('spo.download', $spo->id) }}" target="_blank" class="btn btn-soft-danger btn-action-icon" title="Download PDF SPO" onclick="loadingPdf(this)">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        
                                        @if(Auth::id() == $spo->created_by)
                                            @if(array_key_exists($spo->id, $revisiPending ?? []))
                                                <button type="button" class="btn btn-light btn-action-icon text-muted border" disabled title="Menunggu Persetujuan Katim">
                                                    <i class="fas fa-clock"></i>
                                                </button>
                                            @elseif(array_key_exists($spo->id, $revisiDisetujui ?? []))
                                                <a href="{{ route('spo.edit', $spo->id) }}" class="btn btn-soft-primary btn-action-icon" title="Ubah Dokumen SPO">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @else
                                                <button type="button" class="btn btn-soft-warning btn-action-icon" data-bs-toggle="modal" data-bs-target="#modalRevisi{{ $spo->id }}" title="Ajukan Revisi">
                                                    <i class="fas fa-redo"></i>
                                                </button>

                                                <div class="modal fade" id="modalRevisi{{ $spo->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                                            <form action="{{ route('spo.request-revision', $spo->id) }}" method="POST">
                                                                @csrf
                                                                <div class="modal-header py-3 px-4 border-0" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="bg-warning text-white rounded-3 p-2 me-3">
                                                                            <i class="fas fa-redo"></i>
                                                                        </div>
                                                                        <div>
                                                                            <h5 class="modal-title fw-bold text-dark fs-6">Ajukan Revisi SPO</h5>
                                                                            <small class="text-muted">No. Surat: {{ $spo->no_surat }}</small>
                                                                        </div>
                                                                    </div>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body p-4 text-start">
                                                                    <div class="alert alert-info border-0 rounded-3 small mb-3" style="background: rgba(6,182,212,0.08); color: #0e7490;">
                                                                        <i class="fas fa-info-circle me-1"></i>
                                                                        Permohonan revisi akan diajukan ke Ketua Tim penanggung jawab (<strong>{{ $spo->nama_katim ?? 'Katim' }}</strong>).
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold text-secondary">Alasan / Bagian yang Direvisi <span class="text-danger">*</span></label>
                                                                        <textarea name="alasan_revisi" class="form-control rounded-3" rows="4" placeholder="Contoh: Perubahan nama kru pencahayaan atau penyesuaian jam siaran..." required maxlength="500"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light border-0 py-3 px-4">
                                                                    <button type="button" class="btn btn-light rounded-pill px-4 border" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">
                                                                        <i class="fas fa-paper-plane me-1"></i> Kirim Permintaan
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    @else
                                        <a href="{{ route('spo.edit', $spo->id) }}" class="btn btn-soft-success btn-action-icon" title="Edit Data SPO">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('spo.destroy', $spo->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus SPO ini? Data yang terhapus tidak dapat dipulihkan.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-soft-danger btn-action-icon" title="Hapus SPO">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3">
                                        <i class="fas fa-folder-open fa-3x text-muted opacity-50"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Belum Ada Data SPO Ditemukan</h6>
                                    <p class="small text-muted mb-3">Anda belum membuat dokumen Surat Perintah Operasional.</p>
                                    <a href="{{ route('spo.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">
                                        <i class="fas fa-plus me-1"></i> Buat SPO Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- TAMPILAN KHUSUS KATIM (2 TABEL)            --}}
    {{-- ========================================== --}}
    @if(Auth::user()->role == 'katim')
    
    {{-- TABEL 1: TUGAS & PERSETUJUAN KATIM --}}
    <div class="card card-modern overflow-hidden mb-5">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-2 me-3" style="background: rgba(26,59,112,0.08); color: var(--primary-navy);">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <h6 class="m-0 fw-bold text-dark">Tugas & Persetujuan SPO Saya</h6>
                    <small class="text-muted">Daftar SPO yang berada di bawah pengawasan dan persetujuan Anda</small>
                </div>
            </div>
            <span class="badge badge-soft-primary rounded-pill px-3 py-2 fw-semibold">
                {{ count($spos) }} Tugas
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-modern align-middle mb-0" style="min-width: 800px;">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="25%">Nomor Surat & Pembuat</th>
                            <th width="20%">Jadwal Pelaksanaan</th>
                            <th width="20%">Pengarah Teknik</th>
                            <th width="15%">Status</th>
                            <th width="15%" class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($spos as $index => $spo)
                        <tr>
                            <td class="text-center fw-bold text-muted small">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark small">{{ $spo->no_surat }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">
                                        <i class="fas fa-user-circle me-1 text-primary"></i> {{ $spo->nama_pembuat }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-dark small">{{ $spo->hari }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">
                                        <i class="fas fa-calendar-day me-1"></i> {{ date('d M Y', strtotime($spo->tanggal_pelaksanaan)) }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="small fw-semibold text-dark">{{ $spo->pengarah_teknik }}</span>
                            </td>
                            <td>
                                @if(array_key_exists($spo->id, $revisiPending ?? []))
                                    <span class="badge badge-soft-warning rounded-pill px-3 py-1 mb-1 d-inline-block fw-semibold badge-pulse">
                                        <i class="fas fa-sync fa-spin me-1"></i> Revisi Diajukan
                                    </span>
                                @elseif(array_key_exists($spo->id, $revisiDisetujui ?? []))
                                    <span class="badge badge-soft-primary rounded-pill px-3 py-1 mb-1 d-inline-block fw-semibold">
                                        <i class="fas fa-user-edit me-1"></i> Sedang Direvisi Kru
                                    </span>
                                @elseif($spo->status_persetujuan == 'pending')
                                    <span class="badge badge-soft-warning rounded-pill px-3 py-1 fw-semibold badge-pulse">
                                        <i class="fas fa-hourglass-half me-1"></i> Menunggu Persetujuan
                                    </span>
                                @elseif($spo->status_persetujuan == 'approved')
                                    <span class="badge badge-soft-success rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-check-circle me-1"></i> Disetujui
                                    </span>
                                @else
                                    <span class="badge badge-soft-danger rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-times-circle me-1"></i> Ditolak
                                    </span>
                                    @if(!empty($spo->alasan_penolakan))
                                        <div class="mt-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" style="font-size: 0.72rem;" data-bs-toggle="modal" data-bs-target="#modalDetailAlasanKatim{{ $spo->id }}" title="Lihat Alasan Penolakan">
                                                <i class="fas fa-comment-dots me-1"></i> Alasan Katim
                                            </button>
                                        </div>

                                        <div class="modal fade" id="modalDetailAlasanKatim{{ $spo->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-start">
                                                    <div class="modal-header py-3 px-4 border-0" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-danger text-white rounded-3 p-2 me-3">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="modal-title fw-bold text-dark fs-6">Alasan Penolakan SPO</h5>
                                                                <small class="text-muted">No. Surat: {{ $spo->no_surat }}</small>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4 text-start">
                                                        <div class="p-3 bg-light rounded-3 border-start border-danger border-4">
                                                            <label class="small text-danger fw-bold d-block mb-1">
                                                                <i class="fas fa-comment-dots me-1"></i>Catatan Katim ({{ $spo->nama_katim ?? 'Katim' }}):
                                                            </label>
                                                            <p class="mb-0 text-dark small" style="white-space: pre-wrap;">{{ $spo->alasan_penolakan }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light border-0 py-2 px-4">
                                                        <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('spo.verify', $spo->id) }}" target="_blank" class="btn btn-soft-primary btn-action-icon" title="Lihat Detail Dokumen">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(array_key_exists($spo->id, $revisiPending ?? []))
                                        <form action="{{ route('spo.approve-revision', $revisiPending[$spo->id]) }}" method="POST" class="d-inline"
                                              onsubmit="if(confirm('Setujui Permintaan Revisi ini?')) { var btn = this.querySelector('button'); btn.disabled = true; btn.innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i>'; return true; } return false;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-soft-success btn-action-icon" title="Setujui Permintaan Revisi">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        
                                        <button type="button" class="btn btn-soft-danger btn-action-icon" data-bs-toggle="modal" data-bs-target="#modalRejectRevisiKatim{{ $revisiPending[$spo->id] }}" title="Tolak Permintaan Revisi">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        
                                        <div class="modal fade" id="modalRejectRevisiKatim{{ $revisiPending[$spo->id] }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-start">
                                                    <form action="{{ route('spo.reject-revision', $revisiPending[$spo->id]) }}" method="POST">
                                                        @csrf @method('PATCH')
                                                        <div class="modal-header py-3 px-4 border-0" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);">
                                                            <div class="d-flex align-items-center">
                                                                <div class="bg-danger text-white rounded-3 p-2 me-3">
                                                                    <i class="fas fa-times"></i>
                                                                </div>
                                                                <div>
                                                                    <h5 class="modal-title fw-bold text-dark fs-6">Tolak Permintaan Revisi SPO</h5>
                                                                    <small class="text-muted">No. Surat: {{ $spo->no_surat }}</small>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body p-4 text-start">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold text-secondary">Alasan Penolakan Revisi <span class="text-danger">*</span></label>
                                                                <textarea name="alasan_penolakan" class="form-control rounded-3" rows="3" placeholder="Masukkan alasan mengapa permohonan revisi ditolak..." required></textarea>
                                                            </div>
                                                            <small class="text-muted"><i class="fab fa-telegram-plane text-primary me-1"></i> Alasan ini akan dikirimkan otomatis ke Kru via Telegram.</small>
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
                                    @elseif(array_key_exists($spo->id, $revisiDisetujui ?? []))
                                        <a href="{{ route('spo.download', $spo->id) }}" target="_blank" class="btn btn-soft-danger btn-action-icon" title="Download PDF" onclick="loadingPdf(this)">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @elseif($spo->status_persetujuan == 'pending')
                                        <form action="{{ route('spo.approve', $spo->id) }}" method="POST" class="d-inline"
                                              onsubmit="if(confirm('Setujui Dokumen ini?')) { var btn = this.querySelector('button'); btn.disabled = true; btn.innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i>'; return true; } return false;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-soft-success btn-action-icon" title="Setujui SPO">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        
                                        <button type="button" class="btn btn-soft-danger btn-action-icon" data-bs-toggle="modal" data-bs-target="#modalRejectSPOKatim{{ $spo->id }}" title="Tolak SPO">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        
                                        <div class="modal fade" id="modalRejectSPOKatim{{ $spo->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-start">
                                                    <form action="{{ route('spo.reject', $spo->id) }}" method="POST">
                                                        @csrf @method('PATCH')
                                                        <div class="modal-header py-3 px-4 border-0" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);">
                                                            <div class="d-flex align-items-center">
                                                                <div class="bg-danger text-white rounded-3 p-2 me-3">
                                                                    <i class="fas fa-ban"></i>
                                                                </div>
                                                                <div>
                                                                    <h5 class="modal-title fw-bold text-dark fs-6">Konfirmasi Penolakan SPO</h5>
                                                                    <small class="text-muted">No. Surat: {{ $spo->no_surat }}</small>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body p-4 text-start">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold text-secondary">Alasan Penolakan <span class="text-danger">*</span></label>
                                                                <textarea name="alasan_penolakan" class="form-control rounded-3" rows="3" placeholder="Jelaskan alasan penolakan dan bagian yang harus diperbaiki kru..." required></textarea>
                                                            </div>
                                                            <small class="text-muted"><i class="fab fa-telegram-plane text-primary me-1"></i> Alasan ini akan tampil di Dashboard Kru dan dikirimkan ke Telegram Kru.</small>
                                                        </div>
                                                        <div class="modal-footer bg-light border-0 py-3 px-4">
                                                            <button type="button" class="btn btn-light rounded-pill px-4 border" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                                                                <i class="fas fa-ban me-1"></i> Tolak SPO
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($spo->status_persetujuan == 'approved')
                                        <a href="{{ route('spo.download', $spo->id) }}" target="_blank" class="btn btn-soft-danger btn-action-icon" title="Download PDF" onclick="loadingPdf(this)">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3">
                                        <i class="fas fa-clipboard-check fa-3x text-muted opacity-50"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Belum Ada Tugas Persetujuan</h6>
                                    <p class="small text-muted mb-0">Semua dokumen SPO yang membutuhkan persetujuan Anda telah selesai diproses.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TABEL 2: ARSIP GLOBAL (TELAH DISETUJUI) --}}
    <div class="card card-modern overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-2 me-3" style="background: rgba(16,185,129,0.1); color: #047857;">
                    <i class="fas fa-archive"></i>
                </div>
                <div>
                    <h6 class="m-0 fw-bold text-dark">Arsip Global SPO (Telah Disetujui)</h6>
                    <small class="text-muted">Seluruh dokumen SPO stasiun yang telah disetujui secara resmi</small>
                </div>
            </div>
            <span class="badge badge-soft-success rounded-pill px-3 py-2 fw-semibold">
                {{ count($sposGlobal) }} Arsip
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-modern align-middle mb-0" style="min-width: 800px;">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="20%">Nomor Surat & Pembuat</th>
                            <th width="20%">Penanggung Jawab (Katim)</th>
                            <th width="20%">Jadwal Pelaksanaan</th>
                            <th width="20%">Pengarah Teknik</th>
                            <th width="15%" class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sposGlobal as $index => $spo)
                        <tr>
                            <td class="text-center fw-bold text-muted small">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark small">{{ $spo->no_surat }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">
                                        <i class="fas fa-user-circle me-1 text-primary"></i> {{ $spo->nama_pembuat }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-soft-primary px-3 py-1 rounded-pill fw-semibold">
                                    <i class="fas fa-user-shield me-1"></i> {{ $spo->nama_katim ?? 'Tidak Diketahui' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-dark small">{{ $spo->hari }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">
                                        <i class="fas fa-calendar-day me-1"></i> {{ date('d M Y', strtotime($spo->tanggal_pelaksanaan)) }}
                                    </span>
                                </div>
                            </td>
                            <td><span class="small fw-semibold text-dark">{{ $spo->pengarah_teknik }}</span></td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('spo.verify', $spo->id) }}" target="_blank" class="btn btn-soft-primary btn-action-icon" title="Lihat Detail"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('spo.download', $spo->id) }}" target="_blank" class="btn btn-soft-danger btn-action-icon" title="Download PDF" onclick="loadingPdf(this)"><i class="fas fa-file-pdf"></i></a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3">
                                        <i class="fas fa-archive fa-3x text-muted opacity-50"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Belum Ada Arsip Global</h6>
                                    <p class="small text-muted mb-0">Belum ada dokumen SPO yang berstatus disetujui dalam arsip global.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- TAMPILAN KHUSUS ADMIN & TD (HANYA MONITOR) --}}
    {{-- ========================================== --}}
    @if(Auth::user()->role == 'admin' || Auth::user()->role == 'td' || Auth::user()->role == 'pd')
    <div class="card card-modern overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-2 me-3" style="background: rgba(26,59,112,0.08); color: var(--primary-navy);">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h6 class="m-0 fw-bold text-dark">Data Pengawasan Arsip SPO</h6>
                    <small class="text-muted">Monitoring seluruh dokumen SPO stasiun lintas kru dan divisi</small>
                </div>
            </div>
            <span class="badge badge-soft-primary rounded-pill px-3 py-2 fw-semibold">
                {{ count($sposGlobal) }} Dokumen
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-modern align-middle mb-0" style="min-width: 800px;">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="24%">Nomor Surat & Pembuat</th>
                            <th width="20%">Penanggung Jawab (Katim)</th>
                            <th width="16%">Jadwal Pelaksanaan</th>
                            <th width="18%">Status Dokumen</th>
                            <th width="17%" class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sposGlobal as $index => $spo)
                        <tr>
                            <td class="text-center fw-bold text-muted small">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark small">{{ $spo->no_surat }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">
                                        <i class="fas fa-user-circle me-1 text-primary"></i> {{ $spo->nama_pembuat }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-soft-primary px-3 py-1 rounded-pill fw-semibold">
                                    <i class="fas fa-user-shield me-1"></i> {{ $spo->nama_katim ?? 'Tidak Diketahui' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-dark small">{{ $spo->hari }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">
                                        <i class="fas fa-calendar-day me-1"></i> {{ date('d M Y', strtotime($spo->tanggal_pelaksanaan)) }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if(array_key_exists($spo->id, $revisiPending ?? []))
                                    <span class="badge badge-soft-warning rounded-pill px-3 py-1 mb-1 d-inline-block fw-semibold badge-pulse">
                                        <i class="fas fa-sync fa-spin me-1"></i> Revisi Diajukan
                                    </span>
                                @elseif(array_key_exists($spo->id, $revisiDisetujui ?? []))
                                    <span class="badge badge-soft-primary rounded-pill px-3 py-1 mb-1 d-inline-block fw-semibold">
                                        <i class="fas fa-user-edit me-1"></i> Sedang Direvisi Kru
                                    </span>
                                @elseif($spo->status_persetujuan == 'pending')
                                    <span class="badge badge-soft-warning rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-hourglass-half me-1"></i> Menunggu
                                    </span>
                                @elseif($spo->status_persetujuan == 'approved')
                                    <span class="badge badge-soft-success rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-check-circle me-1"></i> Disetujui
                                    </span>
                                @else
                                    <span class="badge badge-soft-danger rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-times-circle me-1"></i> Ditolak
                                    </span>
                                    @if(!empty($spo->alasan_penolakan))
                                        <div class="mt-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" style="font-size: 0.72rem;" data-bs-toggle="modal" data-bs-target="#modalDetailAlasanAdmin{{ $spo->id }}" title="Lihat Alasan Penolakan">
                                                <i class="fas fa-comment-dots me-1"></i> Alasan Katim
                                            </button>
                                        </div>

                                        <div class="modal fade" id="modalDetailAlasanAdmin{{ $spo->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-start">
                                                    <div class="modal-header py-3 px-4 border-0" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-danger text-white rounded-3 p-2 me-3">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="modal-title fw-bold text-dark fs-6">Alasan Penolakan SPO</h5>
                                                                <small class="text-muted">No. Surat: {{ $spo->no_surat }}</small>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4 text-start">
                                                        <div class="p-3 bg-light rounded-3 border-start border-danger border-4">
                                                            <label class="small text-danger fw-bold d-block mb-1">
                                                                <i class="fas fa-comment-dots me-1"></i>Catatan Katim ({{ $spo->nama_katim ?? 'Katim' }}):
                                                            </label>
                                                            <p class="mb-0 text-dark small" style="white-space: pre-wrap;">{{ $spo->alasan_penolakan }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light border-0 py-2 px-4">
                                                        <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('spo.verify', $spo->id) }}" target="_blank" class="btn btn-soft-primary btn-action-icon" title="Lihat Lembar SPO"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('spo.download', $spo->id) }}" target="_blank" class="btn btn-soft-danger btn-action-icon" title="Download PDF" onclick="loadingPdf(this)"><i class="fas fa-file-pdf"></i></a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3">
                                        <i class="fas fa-folder-open fa-3x text-muted opacity-50"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Belum Ada Data SPO</h6>
                                    <p class="small text-muted mb-0">Belum ada riwayat dokumen SPO dalam pangkalan data sistem.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    function loadingPdf(element) {
        if (element.classList.contains('disabled')) return;
        
        var originalIcon = element.innerHTML;
        
        element.classList.add('disabled');
        element.style.pointerEvents = 'none';
        element.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        setTimeout(function() {
            element.classList.remove('disabled');
            element.style.pointerEvents = 'auto';
            element.innerHTML = originalIcon;
        }, 3000);
    }
</script>
@endpush
