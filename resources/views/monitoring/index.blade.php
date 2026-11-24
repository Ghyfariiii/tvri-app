@extends('layouts.backend')

@section('content')
<div class="container-fluid">

    {{-- HEADER HALAMAN --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-soft-primary px-3 py-1 rounded-pill small fw-semibold">
                    <i class="fas fa-broadcast-tower me-1"></i> Monitoring Hub
                </span>
            </div>
            <h1 class="h3 mb-1 mt-2 text-gray-800 fw-bold">Arsip Monitoring Siaran</h1>
            <p class="text-muted small mb-0">Riwayat laporan harian operasional dan kualitas siaran TVRI Gorontalo.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3 mt-sm-0">
            <a href="{{ route('monitoring.export.pdf', request()->query()) }}" class="btn btn-soft-danger shadow-sm rounded-pill px-3">
                <i class="fas fa-file-pdf me-1"></i> Cetak PDF
            </a>
            <a href="{{ route('monitoring.export', request()->query()) }}" class="btn btn-soft-success shadow-sm rounded-pill px-3">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
            @if(in_array(Auth::user()->role, ['td', 'pd']))
                <a href="{{ route('monitoring.create') }}" class="btn btn-gradient-primary shadow-sm rounded-pill px-4">
                    <i class="fas fa-plus me-1"></i> Buat Laporan Baru
                </a>
            @endif
        </div>
    </div>

    {{-- NOTIFIKASI KHUSUS KATIM & ADMIN: PERMINTAAN REVISI PENDING --}}
    @if(in_array(Auth::user()->role, ['katim', 'admin']) && ($revisiPendingCount ?? 0) > 0)
    <div class="card border-0 mb-4 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #f59e0b !important;">
        <div class="card-body p-3 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center">
                <div class="bg-warning text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 46px; height: 46px; min-width: 46px;">
                    <i class="fas fa-bell fa-lg badge-pulse"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Permintaan Revisi Laporan Siaran Menunggu Persetujuan!</h6>
                    <p class="small text-muted mb-0">Terdapat <span class="badge bg-warning text-dark px-2 py-1 rounded-pill fw-bold">{{ $revisiPendingCount }}</span> laporan monitoring siaran yang membutuhkan persetujuan revisi dari Anda.</p>
                </div>
            </div>
            <a href="{{ route('monitoring.index') }}?status_revisi=pending" class="btn btn-warning btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm text-nowrap">
                <i class="fas fa-filter me-1"></i> Tampilkan Yang Pending
            </a>
        </div>
    </div>
    @endif

    {{-- ACCORDION FILTER --}} 
    <div class="card card-modern mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom-0" style="cursor: pointer;" id="accordionFilterHeader" role="button" data-bs-toggle="collapse" data-bs-target="#accordionFilterBody" aria-expanded="true" aria-controls="accordionFilterBody">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 p-2 me-3" style="background: rgba(26,59,112,0.08); color: var(--primary-navy);">
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <h6 class="m-0 fw-bold text-dark">Filter & Pencarian Cepat</h6>
                        <small class="text-muted">Saring arsip monitoring berdasarkan rentang tanggal, status siaran, atau kata kunci</small>
                    </div>
                </div>
                <div class="text-muted">
                    <i class="fas fa-chevron-down transition-transform"></i>
                </div>
            </div>
        </div>
        <div id="accordionFilterBody" class="card-body collapse show px-4 pt-0 pb-4" aria-labelledby="accordionFilterHeader">
            <hr class="mt-0 mb-3 text-muted opacity-25">
            <form method="GET" action="{{ route('monitoring.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-secondary">Tanggal Dari</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" name="tanggal_dari" class="form-control form-control-sm border-start-0" value="{{ request('tanggal_dari') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-secondary">Tanggal Sampai</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" name="tanggal_sampai" class="form-control form-control-sm border-start-0" value="{{ request('tanggal_sampai') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-secondary">Status Siaran</label>
                        <select name="status_siaran" class="form-select form-select-sm">
                            <option value="semua" {{ request('status_siaran') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="sukses" {{ request('status_siaran') == 'sukses' ? 'selected' : '' }}>Sukses</option>
                            <option value="kendala" {{ request('status_siaran') == 'kendala' ? 'selected' : '' }}>Kendala</option>
                            <option value="gagal" {{ request('status_siaran') == 'gagal' ? 'selected' : '' }}>Gagal</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">Status Revisi</label>
                        <select name="status_revisi" class="form-select form-select-sm">
                            <option value="semua" {{ request('status_revisi') == 'semua' ? 'selected' : '' }}>Semua Status Revisi</option>
                            <option value="pending" {{ request('status_revisi') == 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                            <option value="approved" {{ request('status_revisi') == 'approved' ? 'selected' : '' }}>Revisi Disetujui</option>
                            <option value="rejected" {{ request('status_revisi') == 'rejected' ? 'selected' : '' }}>Revisi Ditolak</option>
                            <option value="none" {{ request('status_revisi') == 'none' ? 'selected' : '' }}>Tanpa Revisi (Normal)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">Cari (No. SPO / Pelapor)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control form-control-sm border-start-0" placeholder="Ketik nomor atau nama..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-gradient-primary btn-sm px-4 rounded-pill">
                        <i class="fas fa-search me-1"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('monitoring.index') }}" class="btn btn-light btn-sm px-3 rounded-pill border">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL DATA MONITORING --}}
    <div class="card card-modern overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-2 me-3" style="background: rgba(26,59,112,0.08); color: var(--primary-navy);">
                    <i class="fas fa-desktop"></i>
                </div>
                <div>
                    <h6 class="m-0 fw-bold text-dark">Data Laporan Monitoring</h6>
                    <small class="text-muted">Total <strong>{{ $monitorings->count() }}</strong> laporan ditampilkan</small>
                </div>
            </div>
            <span class="badge badge-soft-primary rounded-pill px-3 py-2 fw-semibold">
                {{ $monitorings->count() }} Data Laporan
            </span>
        </div>
        <div class="card-body p-0">
            @if($monitorings->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-modern mb-0 align-middle">
                    <thead>
                        <tr>
                            <th width="4%" class="text-center">No</th>
                            <th width="12%">Tanggal</th>
                            <th width="18%">No. SPO</th>
                            <th width="16%">Pelapor (PD)</th>
                            <th width="12%">Status Siaran</th>
                            <th width="14%">Status Revisi</th>
                            <th width="14%">Kualitas A / V</th>
                            <th width="10%" class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monitorings as $index => $item)
                        <tr style="{{ $item->status_revisi === 'pending' ? 'background-color: rgba(245, 158, 11, 0.06);' : '' }}">
                            <td class="text-center fw-bold text-muted small">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark small">{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('dddd') }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-dark small">{{ $item->no_surat ?? '-' }}</span>
                                    @if(isset($item->nama_katim))
                                        <span class="text-muted" style="font-size: 0.72rem;">
                                            <i class="fas fa-user-shield me-1"></i> Katim: {{ $item->nama_katim }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light text-primary d-inline-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <span class="small fw-semibold text-dark">{{ $item->pelapor ?? 'Akun Terhapus' }}</span>
                                </div>
                            </td>
                            <td>
                                @if($item->status_siaran === 'sukses')
                                    <span class="badge badge-soft-success rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-circle me-1" style="font-size: 6px;"></i> Sukses
                                    </span>
                                @elseif($item->status_siaran === 'kendala')
                                    <span class="badge badge-soft-warning rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-circle me-1" style="font-size: 6px;"></i> Kendala
                                    </span>
                                @else
                                    <span class="badge badge-soft-danger rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-circle me-1" style="font-size: 6px;"></i> Gagal
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($item->status_revisi === 'pending')
                                    <span class="badge badge-soft-warning rounded-pill px-3 py-1 fw-semibold badge-pulse">
                                        <i class="fas fa-hourglass-half me-1"></i> Revisi Pending
                                    </span>
                                @elseif($item->status_revisi === 'approved')
                                    <span class="badge badge-soft-info rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-check-circle me-1"></i> Revisi Disetujui
                                    </span>
                                @elseif($item->status_revisi === 'rejected')
                                    <span class="badge badge-soft-danger rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-times-circle me-1"></i> Revisi Ditolak
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill px-3 py-1 fw-normal">
                                        Normal
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge bg-light border text-dark text-start py-1 px-2" style="font-size: 0.72rem; font-weight: 500;">
                                        <i class="fas fa-volume-up text-primary me-1"></i> Audio: {{ $item->kondisi_audio ? Str::limit($item->kondisi_audio, 10) : 'Normal' }}
                                    </span>
                                    <span class="badge bg-light border text-dark text-start py-1 px-2" style="font-size: 0.72rem; font-weight: 500;">
                                        <i class="fas fa-video text-info me-1"></i> Video: {{ $item->kondisi_video ? Str::limit($item->kondisi_video, 10) : 'Normal' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Tombol Detail --}}
                                    <a href="{{ route('monitoring.show', $item->id) }}" class="btn btn-soft-primary btn-action-icon" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- Tombol Cetak PDF Laporan Ini --}}
                                    <a href="{{ route('monitoring.download.pdf', $item->id) }}" class="btn btn-soft-danger btn-action-icon" title="Cetak PDF Laporan Ini" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>

                                    {{-- Aksi Khusus Katim / Admin saat Revisi Pending --}}
                                    @if(in_array(Auth::user()->role, ['katim', 'admin']) && $item->status_revisi === 'pending')
                                        @if(Auth::user()->role === 'admin' || Auth::id() == $item->katim_id)
                                        <a href="{{ route('monitoring.show', $item->id) }}" class="btn btn-warning btn-sm rounded-pill fw-bold px-2 py-1 shadow-sm" title="Tinjau Permintaan Revisi">
                                            <i class="fas fa-bell me-1"></i> Tinjau
                                        </a>
                                        @endif
                                    @endif

                                    {{-- Aksi Khusus TD / Admin --}}
                                    @if(in_array(Auth::user()->role, ['td', 'pd', 'admin']) && (Auth::user()->id === $item->created_by || Auth::user()->role === 'admin'))
                                        @if($item->status_revisi === 'none')
                                            <button type="button" class="btn btn-soft-warning btn-action-icon" title="Ajukan Revisi" data-bs-toggle="modal" data-bs-target="#modalRevisi{{ $item->id }}">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        @elseif($item->status_revisi === 'approved')
                                            <a href="{{ route('monitoring.edit', $item->id) }}" class="btn btn-soft-success btn-action-icon" title="Edit Laporan (Revisi Disetujui)">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5 text-muted">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3">
                    <i class="fas fa-inbox fa-3x text-muted opacity-50"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Tidak Ada Data Monitoring Ditemukan</h6>
                <p class="small text-muted mb-3">Tidak ada rekaman laporan siaran yang sesuai dengan kriteria filter saat ini.</p>
                @if(request()->hasAny(['tanggal_dari', 'tanggal_sampai', 'status_siaran', 'status_revisi', 'search']))
                    <a href="{{ route('monitoring.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-4">
                        <i class="fas fa-redo me-1"></i> Reset Seluruh Filter
                    </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL REVISI (DI LUAR TABEL) --}}
@foreach($monitorings as $item)
    @if(in_array(Auth::user()->role, ['td', 'pd', 'admin']) && (Auth::user()->id === $item->created_by || Auth::user()->role === 'admin') && $item->status_revisi === 'none')
    <div class="modal fade" id="modalRevisi{{ $item->id }}" tabindex="-1" aria-labelledby="modalRevisiLabel{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form action="{{ route('monitoring.request-revision', $item->id) }}" method="POST">
                    @csrf
                    <div class="modal-header py-3 px-4 border-0" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning text-white rounded-3 p-2 me-3">
                                <i class="fas fa-redo"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-dark fs-6" id="modalRevisiLabel{{ $item->id }}">
                                    Ajukan Revisi Laporan Siaran
                                </h5>
                                <small class="text-muted">No. SPO: {{ $item->no_surat ?? '-' }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 rounded-3 small mb-3" style="background: rgba(6,182,212,0.08); color: #0e7490;">
                            <i class="fas fa-info-circle me-1"></i>
                            Permintaan revisi akan diteruskan ke <strong>Ketua Tim (Katim)</strong> penanggung jawab melalui website dan Telegram Bot. Setelah disetujui, tombol <strong>Edit</strong> akan aktif.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Alasan Revisi <span class="text-danger">*</span></label>
                            <textarea name="alasan_revisi" class="form-control rounded-3" rows="4" placeholder="Jelaskan secara rinci alasan perbaikan data laporan..." required maxlength="500"></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Maksimal 500 karakter</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4 border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i> Kirim Permintaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var accordionBody = document.getElementById('accordionFilterBody');
        var accordionHeader = document.getElementById('accordionFilterHeader');
        var chevron = accordionHeader ? accordionHeader.querySelector('.fa-chevron-down') : null;
        var lastScrollTop = 0;
        var isOpen = accordionBody ? accordionBody.classList.contains('show') : true;

        function setChevron(open) {
            if (!chevron) return;
            chevron.style.transition = 'transform 0.3s';
            chevron.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
        }

        function hideFilter() {
            if (!isOpen || !accordionBody) return;
            if (window.jQuery && jQuery(accordionBody).collapse) {
                jQuery(accordionBody).collapse('hide');
            } else {
                accordionBody.classList.remove('show');
            }
            if (accordionHeader) accordionHeader.setAttribute('aria-expanded', 'false');
            isOpen = false;
            setChevron(false);
        }

        function showFilter() {
            if (isOpen || !accordionBody) return;
            if (window.jQuery && jQuery(accordionBody).collapse) {
                jQuery(accordionBody).collapse('show');
            } else {
                accordionBody.classList.add('show');
            }
            if (accordionHeader) accordionHeader.setAttribute('aria-expanded', 'true');
            isOpen = true;
            setChevron(true);
        }

        function onScrollPos(st) {
            if (st > lastScrollTop + 5) {
                if (st > 80) hideFilter();
            } else if (st < lastScrollTop - 5) {
                if (st < 80) showFilter();
            }
            lastScrollTop = st <= 0 ? 0 : st;
        }

        window.addEventListener('scroll', function() {
            onScrollPos(window.scrollY || document.documentElement.scrollTop);
        }, { passive: true });

        document.addEventListener('scroll', function(e) {
            var t = e.target;
            if (t === document || t === window) return;
            if (t.scrollTop !== undefined) onScrollPos(t.scrollTop);
        }, true);

        if (accordionHeader) {
            accordionHeader.addEventListener('click', function() {
                setTimeout(function() {
                    isOpen = accordionBody.classList.contains('show');
                    setChevron(isOpen);
                    if (accordionHeader) accordionHeader.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                }, 350);
            });
        }

        setChevron(isOpen);
    });
</script>
@endpush
