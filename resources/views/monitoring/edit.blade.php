@extends('layouts.backend')

@section('content')
<div class="container-fluid pb-5">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <div class="d-inline-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill small fw-bold">
                    <i class="fas fa-satellite-dish me-1"></i> MONITORING OPERASIONAL
                </span>
            </div>
            <h3 class="fw-bold text-dark mb-1">Edit Laporan Monitoring Siaran</h3>
            <p class="text-muted small mb-0">Perbarui log acara per-jam, status kendala alat, atau sesuaikan personil kru bertugas.</p>
        </div>
        <div>
            <a href="{{ route('monitoring.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm btn-sm py-2">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Laporan
            </a>
        </div>
    </div>

    {{-- BLOK NOTIFIKASI ERROR VALIDASI --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4 border-0 rounded-4 p-3 d-flex align-items-start gap-3" role="alert">
            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center p-2 mt-1" style="width: 32px; height: 32px; flex-shrink: 0;">
                <i class="fas fa-exclamation-triangle small"></i>
            </div>
            <div class="flex-grow-1">
                <strong class="d-block text-danger mb-1">Pemeriksaan Validasi Gagal:</strong>
                <ul class="mb-0 ps-3 small text-dark">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-12">

            <!-- MAIN FORM CARD -->
            <div class="card card-modern rounded-4 border-0 shadow-sm overflow-hidden">
                
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center" 
                             style="width: 44px; height: 44px; background: linear-gradient(135deg, var(--primary-navy) 0%, var(--primary-navy-light) 100%); color: #fff;">
                            <i class="fas fa-edit fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Formulir Revisi Log Monitoring Siaran</h5>
                            <small class="text-muted">ID Laporan: #{{ $laporan->id }} &bull; No. SPO: <strong>{{ $laporan->no_surat ?? '-' }}</strong></small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('monitoring.update', $laporan->id) }}" method="POST" id="monitoringEditForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- SECTION 1: SPO REFERENSI & TANGGAL -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">1</span>
                                <h6 class="fw-bold text-dark m-0">Referensi SPO & Jadwal Pelaksanaan</h6>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-7 col-lg-8">
                                    <label class="form-label-modern">
                                        <i class="fas fa-file-contract text-primary small"></i> SPO Referensi <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-modern">
                                        <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                                        <select name="spo_id" id="spo_id" class="form-select form-select-modern @error('spo_id') is-invalid @enderror" required>
                                            <option value="">-- Pilih SPO yang Telah Disetujui --</option>
                                            @foreach($spos as $item)
                                                <option value="{{ $item->id }}" 
                                                        data-penyiaran="{{ $item->petugas_penyiaran }}" 
                                                        data-penunjang="{{ $item->petugas_penunjang }}"
                                                        data-tanggal="{{ \Carbon\Carbon::parse($item->tanggal_pelaksanaan)->format('Y-m-d') }}"
                                                        {{ $laporan->spo_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->no_surat }} &bull; {{ \Carbon\Carbon::parse($item->tanggal_pelaksanaan)->format('d M Y') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('spo_id')
                                        <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-5 col-lg-4">
                                    <label class="form-label-modern">
                                        <i class="fas fa-calendar-alt text-primary small"></i> Tanggal Pelaksanaan <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-modern">
                                        <span class="input-group-text"><i class="fas fa-calendar-day text-muted"></i></span>
                                        <input type="date" name="tanggal" id="tanggal" class="form-control form-control-modern bg-light" 
                                               value="{{ $laporan->tanggal }}" required readonly>
                                        <span class="input-group-text bg-light text-muted border-start-0" title="Terkunci"><i class="fas fa-lock small"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: KRU BERTUGAS AKTUAL -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">2</span>
                                    <div>
                                        <h6 class="fw-bold text-dark m-0">Kru Bertugas (Kondisi Aktual Lapangan)</h6>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-soft-primary rounded-pill px-3 fw-bold" id="tambahKru">
                                    <i class="fas fa-plus me-1"></i> Tambah Kru Pengganti
                                </button>
                            </div>

                            <div class="card rounded-4 border overflow-hidden shadow-none mb-2" style="border-color: #e2e8f0 !important;">
                                <div class="table-responsive">
                                    <table class="table table-modern table-mobile-cards align-middle mb-0" id="kruTable">
                                        <thead>
                                            <tr>
                                                <th class="ps-4" style="width: 42%;">Jabatan / Posisi Penugasan</th>
                                                <th style="width: 48%;">Nama Petugas Aktual</th>
                                                <th class="text-center" style="width: 10%;"><i class="fas fa-trash-alt text-muted"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($kruBertugas as $index => $kru)
                                            <tr>
                                                <td class="ps-md-4">
                                                    <span class="mobile-label"><i class="fas fa-briefcase me-1 text-primary"></i>Jabatan / Posisi:</span>
                                                    <input type="text" name="kru_bertugas[{{ $index }}][jabatan]" class="form-control form-control-modern" value="{{ $kru['jabatan'] ?? '' }}" placeholder="Jabatan / Posisi" required>
                                                </td>
                                                <td>
                                                    <span class="mobile-label"><i class="fas fa-user me-1 text-success"></i>Nama Petugas:</span>
                                                    <input type="text" name="kru_bertugas[{{ $index }}][nama]" class="form-control form-control-modern" value="{{ $kru['nama'] ?? '' }}" placeholder="Nama Petugas" required>
                                                </td>
                                                <td class="text-center align-middle td-actions">
                                                    <button type="button" class="btn btn-soft-danger rounded-circle btn-action-icon hapus-baris shadow-sm" title="Hapus Baris">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td class="ps-md-4">
                                                    <span class="mobile-label"><i class="fas fa-briefcase me-1 text-primary"></i>Jabatan / Posisi:</span>
                                                    <input type="text" name="kru_bertugas[0][jabatan]" class="form-control form-control-modern" value="Pengarah Teknik" required>
                                                </td>
                                                <td>
                                                    <span class="mobile-label"><i class="fas fa-user me-1 text-success"></i>Nama Petugas:</span>
                                                    <input type="text" name="kru_bertugas[0][nama]" class="form-control form-control-modern" value="{{ Auth::user()->name }}" required>
                                                </td>
                                                <td class="text-center align-middle td-actions">
                                                    <button type="button" class="btn btn-soft-danger rounded-circle btn-action-icon hapus-baris shadow-sm" title="Hapus Baris">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: LOG KEGIATAN & ACARA SIARAN PER-JAM (DINAMIS) -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">3</span>
                                    <div>
                                        <h6 class="fw-bold text-dark m-0">Log Acara & Kegiatan Siaran Per-Jam</h6>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-gradient-primary rounded-pill px-3 fw-bold shadow-sm" id="btnTambahAcara">
                                    <i class="fas fa-plus me-1"></i> Tambah Acara / Jadwal Siaran
                                </button>
                            </div>

                            <!-- CONTAINER BARIS ACARA DINAMIS -->
                            <div id="acaraContainer" class="d-flex flex-column gap-3">
                                {{-- Dynamically populated via JS --}}
                            </div>
                        </div>

                        <!-- BUTTON ACTIONS -->
                        <div class="pt-4 border-top d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
                            <a href="{{ route('monitoring.index') }}" class="btn btn-light text-secondary rounded-pill px-4 fw-semibold border">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" id="btnSubmitMonitoring" class="btn btn-gradient-primary rounded-pill px-5 py-3 fw-bold shadow-lg">
                                <span id="textNormalMonitoring">
                                    <i class="fas fa-save me-2"></i> SIMPAN PERUBAHAN LAPORAN LENGKAP
                                </span>
                                <span id="textLoadingMonitoring" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Menyimpan Perubahan...
                                </span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .acara-item-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.25rem;
        transition: all 0.2s ease;
        position: relative;
    }
    .acara-item-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .acara-item-card.border-kendala {
        border-left: 4px solid #f59e0b !important;
    }
    .acara-item-card.border-gagal {
        border-left: 4px solid #ef4444 !important;
    }
    .acara-item-card.border-lancar {
        border-left: 4px solid #10b981 !important;
    }
</style>
@endsection

@push('scripts')
<script>
    const masterAlats = @json($alats ?? []);
    const existingDetails = @json($details ?? []);
    let acaraIndex = 0;

    function getAlatOptions(selectedId = '') {
        let opts = '<option value="">-- Pilih Alat Bermasalah dari Master Alat --</option>';
        if (masterAlats.length > 0) {
            masterAlats.forEach(a => {
                const sel = (a.id == selectedId) ? 'selected' : '';
                opts += `<option value="${a.id}" ${sel}>${a.nama_alat}</option>`;
            });
        }
        return opts;
    }

    function tambahBarisAcara(data = null) {
        const container = document.getElementById('acaraContainer');
        const idx = acaraIndex;
        acaraIndex++;

        const jamVal = data ? (data.jam ? data.jam.substring(0, 5) : '') : '';
        const kegiatanVal = data ? data.kegiatan : '';
        const statusVal = data ? data.status : 'lancar';
        const jenisKendalaVal = data ? (data.jenis_kendala || '') : '';
        const alatIdVal = data ? (data.alat_rusak_id || '') : '';
        const deskripsiKendalaVal = data ? (data.deskripsi_kendala || '') : '';
        const solusiVal = data ? (data.solusi || '') : '';
        const catatanVal = data ? (data.catatan || '') : '';

        const card = document.createElement('div');
        card.className = `acara-item-card border-${statusVal}`;
        card.id = `acara_card_${idx}`;

        card.innerHTML = `
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary bg-opacity-10 text-dark fw-bold rounded-pill px-3 py-1">
                        <i class="far fa-clock me-1"></i> Baris #${idx + 1}
                    </span>
                    <span class="badge-status-label small fw-semibold text-muted" id="badge_status_${idx}">
                        ${statusVal === 'lancar' ? '🟢 Siaran Lancar' : (statusVal === 'kendala' ? '🟡 Ada Kendala' : '🔴 Gagal Tayang')}
                    </span>
                </div>
                <button type="button" class="btn btn-soft-danger btn-sm rounded-circle btn-action-icon" onclick="hapusBarisAcara(${idx})" title="Hapus Baris Acara">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>

            <div class="row g-3 mb-2">
                <div class="col-md-3">
                    <label class="form-label-modern"><i class="fas fa-clock text-primary small"></i> Jam Acara <span class="text-danger">*</span></label>
                    <input type="time" name="details[${idx}][jam]" class="form-control form-control-modern" value="${jamVal}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label-modern"><i class="fas fa-tv text-primary small"></i> Nama Program / Kegiatan Acara <span class="text-danger">*</span></label>
                    <input type="text" name="details[${idx}][kegiatan]" class="form-control form-control-modern" value="${kegiatanVal}" placeholder="Contoh: Gorontalo Hari Ini / Warta Siang" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label-modern"><i class="fas fa-traffic-light text-primary small"></i> Status Pelaksanaan <span class="text-danger">*</span></label>
                    <select name="details[${idx}][status]" class="form-select form-select-modern status-select" onchange="onStatusAcaraChange(${idx}, this.value)" required>
                        <option value="lancar" ${statusVal === 'lancar' ? 'selected' : ''}>🟢 Lancar</option>
                        <option value="kendala" ${statusVal === 'kendala' ? 'selected' : ''}>🟡 Ada Kendala</option>
                        <option value="gagal" ${statusVal === 'gagal' ? 'selected' : ''}>🔴 Gagal Tayang</option>
                    </select>
                </div>
            </div>

            <!-- BLOK KENDALA TEKNIS -->
            <div id="blok_kendala_${idx}" class="p-3 rounded-3 mt-3 bg-light border border-warning" style="display: ${statusVal !== 'lancar' ? 'block' : 'none'};">
                <div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-2">
                    <i class="fas fa-exclamation-circle text-warning"></i>
                    <span>Rincian Kendala & Kerusakan Peralatan:</span>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-modern">Kategori Kendala <span class="text-danger">*</span></label>
                        <select name="details[${idx}][jenis_kendala]" class="form-select form-select-modern" onchange="onJenisKendalaChange(${idx}, this.value)">
                            <option value="">-- Pilih Jenis Kendala --</option>
                            <option value="human_error" ${jenisKendalaVal === 'human_error' ? 'selected' : ''}>👤 Human Error (Kelalaian)</option>
                            <option value="video" ${jenisKendalaVal === 'video' ? 'selected' : ''}>📹 Kendala Video / Visual</option>
                            <option value="audio" ${jenisKendalaVal === 'audio' ? 'selected' : ''}>🔊 Kendala Audio / Suara</option>
                        </select>
                    </div>

                    <div class="col-md-8" id="wrapper_alat_${idx}" style="display: ${(jenisKendalaVal === 'video' || jenisKendalaVal === 'audio') ? 'block' : 'none'};">
                        <label class="form-label-modern">Alat yang Bermasalah (Master Alat) <span class="text-danger">*</span></label>
                        <select name="details[${idx}][alat_rusak_id]" class="form-select form-select-modern">
                            ${getAlatOptions(alatIdVal)}
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label-modern">Deskripsi / Detail Gangguan Kendala</label>
                        <textarea name="details[${idx}][deskripsi_kendala]" class="form-control form-control-modern" rows="2" placeholder="Tuliskan kendala yang terjadi secara detail...">${deskripsiKendalaVal}</textarea>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-7">
                    <label class="form-label-modern"><i class="fas fa-wrench text-secondary small"></i> Tindakan / Solusi Penanganan Lapangan</label>
                    <input type="text" name="details[${idx}][solusi]" class="form-control form-control-modern" value="${solusiVal}" placeholder="Langkah perbaikan/tindakan yang diambil saat siaran...">
                </div>
                <div class="col-md-5">
                    <label class="form-label-modern"><i class="fas fa-sticky-note text-secondary small"></i> Catatan Tambahan</label>
                    <input type="text" name="details[${idx}][catatan]" class="form-control form-control-modern" value="${catatanVal}" placeholder="Catatan opsional...">
                </div>
            </div>
        `;

        container.appendChild(card);
    }

    function hapusBarisAcara(idx) {
        const cards = document.querySelectorAll('.acara-item-card');
        if (cards.length <= 1) {
            alert('Minimal harus ada 1 baris jadwal/acara siaran dalam laporan.');
            return;
        }
        const card = document.getElementById(`acara_card_${idx}`);
        if (card) {
            $(card).slideUp(200, function() { $(this).remove(); });
        }
    }

    function onStatusAcaraChange(idx, val) {
        const blok = document.getElementById(`blok_kendala_${idx}`);
        const card = document.getElementById(`acara_card_${idx}`);
        const label = document.getElementById(`badge_status_${idx}`);

        card.classList.remove('border-lancar', 'border-kendala', 'border-gagal');
        card.classList.add(`border-${val}`);

        if (val === 'lancar') {
            label.textContent = '🟢 Siaran Lancar';
            $(blok).slideUp(200);
        } else if (val === 'kendala') {
            label.textContent = '🟡 Ada Kendala';
            $(blok).slideDown(200);
        } else {
            label.textContent = '🔴 Gagal Tayang';
            $(blok).slideDown(200);
        }
    }

    function onJenisKendalaChange(idx, val) {
        const wrapperAlat = document.getElementById(`wrapper_alat_${idx}`);
        if (val === 'video' || val === 'audio') {
            $(wrapperAlat).slideDown(200);
        } else {
            $(wrapperAlat).slideUp(200);
            const selectAlat = wrapperAlat.querySelector('select');
            if (selectAlat) selectAlat.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        let kruIndex = {{ count($kruBertugas) > 0 ? count($kruBertugas) : 1 }};
        
        const kruTable = document.querySelector('#kruTable tbody');
        const tambahKruBtn = document.getElementById('tambahKru');
        const spoSelect = document.getElementById('spo_id');
        const tanggalInput = document.getElementById('tanggal');
        const btnTambahAcara = document.getElementById('btnTambahAcara');

        // Pre-populasi detail acara yang sudah tersimpan
        if (existingDetails.length > 0) {
            existingDetails.forEach(d => tambahBarisAcara(d));
        } else {
            tambahBarisAcara({ jam: '10:00', kegiatan: '', status: 'lancar' });
        }

        btnTambahAcara.addEventListener('click', function() {
            tambahBarisAcara();
        });

        tambahKruBtn.addEventListener('click', function() {
            tambahBarisKru('', '');
        });

        kruTable.addEventListener('click', function(e) {
            if (e.target.closest('.hapus-baris')) {
                const tr = e.target.closest('tr');
                if (kruTable.querySelectorAll('tr').length > 1) {
                    tr.remove();
                } else {
                    alert('Minimal harus ada 1 orang kru bertugas.');
                }
            }
        });

        function tambahBarisKru(jabatan, nama) {
            const tr = document.createElement('tr');
            const rowIndex = kruIndex;
            
            tr.innerHTML = `
                <td class="ps-md-4">
                    <span class="mobile-label"><i class="fas fa-briefcase me-1 text-primary"></i>Jabatan / Posisi:</span>
                    <input type="text" name="kru_bertugas[${rowIndex}][jabatan]" class="form-control form-control-modern" value="${jabatan}" placeholder="Jabatan / Posisi" required>
                </td>
                <td>
                    <span class="mobile-label"><i class="fas fa-user me-1 text-success"></i>Nama Petugas:</span>
                    <input type="text" name="kru_bertugas[${rowIndex}][nama]" class="form-control form-control-modern" value="${nama}" placeholder="Nama Petugas" required>
                </td>
                <td class="text-center align-middle td-actions">
                    <button type="button" class="btn btn-soft-danger rounded-circle btn-action-icon hapus-baris shadow-sm" title="Hapus Baris">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;
            kruTable.appendChild(tr);
            kruIndex++;
        }

        const monitoringEditForm = document.getElementById('monitoringEditForm');
        if (monitoringEditForm) {
            monitoringEditForm.addEventListener('submit', function() {
                const btn = document.getElementById('btnSubmitMonitoring');
                if (btn) {
                    btn.disabled = true;
                    document.getElementById('textNormalMonitoring').classList.add('d-none');
                    document.getElementById('textLoadingMonitoring').classList.remove('d-none');
                }
            });
        }
    });
</script>
@endpush
