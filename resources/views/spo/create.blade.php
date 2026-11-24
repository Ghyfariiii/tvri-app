@extends('layouts.backend')

@section('content')
<div class="container-fluid pb-5">
    
    <!-- PAGE HEADER -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <div class="d-inline-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill small fw-bold">
                    <i class="fas fa-file-signature me-1"></i> SURAT PERINTAH OPERASI (SPO)
                </span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-1 rounded-pill small">
                    TAHUN {{ date('Y') }}
                </span>
            </div>
            <h3 class="fw-bold text-dark mb-1">Buat SPO Baru</h3>
            <p class="text-muted small mb-0">Lengkapi parameter jadwal penugasan siaran, daftar kru teknis, dan tujuan persetujuan Ketua Tim.</p>
        </div>
        <div>
            <a href="{{ route('spo.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm btn-sm py-2">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar SPO
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
                <strong class="d-block text-danger mb-1">Terdapat kesalahan pada input form:</strong>
                <ul class="mb-0 ps-3 small text-dark">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('spo.store') }}" method="POST" id="formSPO">
        @csrf
        
        <div class="row g-4">
            
            <!-- LEFT COLUMN: PARAMETER & DELEGASI SURAT -->
            <div class="col-lg-4">
                <div class="card card-modern rounded-4 border-0 shadow-sm sticky-top" style="top: 24px; z-index: 10;">
                    
                    <div class="card-header bg-white border-bottom p-3 p-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary-navy) 0%, var(--primary-navy-light) 100%); color: #fff;">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Parameter & Otorisasi</h6>
                                <small class="text-muted">Nomor surat, jadwal & Katim</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        
                        <!-- NOMOR SURAT -->
                        <div class="mb-3">
                            <label class="form-label-modern">
                                <i class="fas fa-hashtag text-primary small"></i> Nomor Surat SPO
                            </label>
                            <div class="input-group input-group-modern">
                                <span class="input-group-text fw-bold text-primary bg-primary bg-opacity-10 border-end-0 px-3">
                                    {{ $nextNo }}
                                </span>
                                <input type="hidden" name="nomor_urut" value="{{ $nextNo }}">
                                <input type="text" name="format_surat" class="form-control form-control-modern bg-light text-dark fw-bold" 
                                       value="/SPO-TEK/TVRI/{{ date('Y') }}" readonly>
                                <span class="input-group-text bg-light text-muted border-start-0" title="Format Otomatis">
                                    <i class="fas fa-lock small"></i>
                                </span>
                            </div>
                            <div class="form-text text-muted" style="font-size: 11px;">Nomor urut surat di-generate otomatis oleh sistem.</div>
                        </div>

                        <!-- HARI & TANGGAL PELAKSANAAN -->
                        <div class="row g-2 mb-3">
                            <div class="col-sm-5">
                                <label class="form-label-modern">
                                    <i class="fas fa-calendar-day text-primary small"></i> Hari <span class="text-danger">*</span>
                                </label>
                                <select name="hari" id="selectHari" class="form-select form-select-modern" required>
                                    <option value="" disabled selected>-- Pilih --</option>
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                                        <option value="{{ $hari }}" {{ (old('hari') == $hari || (old('hari') == null && $hari == ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][date('w')])) ? 'selected' : '' }}>
                                            {{ $hari }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-7">
                                <label class="form-label-modern">
                                    <i class="fas fa-calendar-alt text-primary small"></i> Tanggal <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="tanggal_pelaksanaan" id="inputTanggal" class="form-control form-control-modern" 
                                       value="{{ old('tanggal_pelaksanaan', date('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <!-- KATIM TUJUAN & TELEGRAM INTEGRATION -->
                        <div class="mb-4">
                            <label class="form-label-modern">
                                <i class="fab fa-telegram text-primary small"></i> Ketua Tim (Katim) Tujuan <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-modern">
                                <span class="input-group-text"><i class="fas fa-user-check text-muted"></i></span>
                                <select name="katim_id" id="selectKatim" class="form-select form-select-modern" required>
                                    <option value="" selected disabled>-- Pilih Katim Penyetuju --</option>
                                    @foreach($katims as $katim)
                                        @php
                                            $hasTelegram = !empty($katim->telegram_chat_id);
                                        @endphp
                                        <option value="{{ $katim->id }}" data-telegram="{{ $hasTelegram ? 'yes' : 'no' }}">
                                            {{ $katim->name }} {{ $hasTelegram ? '⚡ (Telegram Siap)' : '⚠️ (Telegram Belum Terhubung)' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="p-3 rounded-3 mt-2 bg-light border" style="font-size: 11.5px;">
                                <div class="d-flex align-items-center gap-2 text-dark fw-semibold mb-1">
                                    <i class="fab fa-telegram-plane text-info fs-6"></i>
                                    <span>Notifikasi Bot Otomatis</span>
                                </div>
                                <span class="text-muted">
                                    Setelah SPO diajukan, bot Telegram TVRI akan langsung mengirim pesan notifikasi interaktif ke Katim untuk persetujuan.
                                </span>
                            </div>
                        </div>

                        <!-- RINGKASAN PETUGAS -->
                        <div class="p-3 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-primary"><i class="fas fa-users me-1"></i> Total Personil:</span>
                                <span class="badge bg-primary text-white rounded-pill px-3 py-1" id="totalKruBadge">0 Orang</span>
                            </div>
                            <div class="d-flex justify-content-between text-muted" style="font-size: 11.5px;">
                                <span>Kru Penyiaran: <strong class="text-dark" id="countPenyiaranTxt">0</strong></span>
                                <span>Kru Penunjang: <strong class="text-dark" id="countPenunjangTxt">0</strong></span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: DAFTAR PETUGAS PENYIARAN & PENUNJANG -->
            <div class="col-lg-8">
                
                <!-- CARD 1: PETUGAS PENYIARAN -->
                <div class="card card-modern rounded-4 border-0 shadow-sm mb-4 overflow-hidden">
                    <div class="card-header py-3 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2" 
                         style="background: linear-gradient(135deg, var(--primary-navy) 0%, var(--primary-navy-light) 100%);">
                        <div class="d-flex align-items-center gap-2 text-white">
                            <i class="fas fa-broadcast-tower"></i>
                            <h6 class="fw-bold m-0 text-white">I. Tim Teknis Penyiaran</h6>
                            <span class="badge bg-white text-primary rounded-pill px-2 py-1 ms-1 fw-bold" id="badgeCountPenyiaran" style="font-size: 11px;">0 Posisi</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3 shadow-sm text-nowrap" id="addPenyiaran">
                            <i class="fas fa-plus me-1"></i> Tambah Posisi
                        </button>
                    </div>
                    
                    <div class="card-body p-2 p-md-0">
                        <div class="table-responsive">
                            <table class="table table-modern table-mobile-cards align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4" style="width: 46%;">Jabatan / Posisi Penyiaran</th>
                                        <th style="width: 46%;">Nama Personil Bertugas</th>
                                        <th class="text-center" style="width: 8%;"><i class="fas fa-trash-alt text-muted"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="containerPenyiaran">
                                    {{-- Dynamically filled via JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: PETUGAS PENUNJANG -->
                <div class="card card-modern rounded-4 border-0 shadow-sm mb-4 overflow-hidden">
                    <div class="card-header py-3 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2" 
                         style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                        <div class="d-flex align-items-center gap-2 text-white">
                            <i class="fas fa-truck-loading"></i>
                            <h6 class="fw-bold m-0 text-white">II. Tim Penunjang Operasional</h6>
                            <span class="badge bg-white text-success rounded-pill px-2 py-1 ms-1 fw-bold" id="badgeCountPenunjang" style="font-size: 11px;">0 Posisi</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-light text-success fw-bold rounded-pill px-3 shadow-sm text-nowrap" id="addPenunjang">
                            <i class="fas fa-plus me-1"></i> Tambah Penunjang
                        </button>
                    </div>
                    
                    <div class="card-body p-2 p-md-0">
                        <div class="table-responsive">
                            <table class="table table-modern table-mobile-cards align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4" style="width: 46%;">Jabatan / Posisi Penunjang</th>
                                        <th style="width: 46%;">Nama Personil Bertugas</th>
                                        <th class="text-center" style="width: 8%;"><i class="fas fa-trash-alt text-muted"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="containerPenunjang">
                                    {{-- Dynamically filled via JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SUBMIT ACTION CARD -->
                <div class="card card-modern rounded-4 border-0 shadow-sm bg-white overflow-hidden">
                    <div class="card-body p-4 text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2 text-muted small mb-3">
                            <i class="fas fa-shield-alt text-success"></i>
                            <span>Pastikan data jadwal dan susunan personil siaran telah sesuai sebelum diajukan.</span>
                        </div>
                        <button type="submit" id="btnSubmitSPO" class="btn btn-gradient-primary rounded-pill py-3 px-5 fw-bold shadow-lg w-100 fs-6">
                            <span id="textNormalSPO">
                                <i class="fas fa-paper-plane me-2"></i> AJUKAN SURAT PENUGASAN (SPO) SEKARANG
                            </span>
                            <span id="textLoadingSPO" class="d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Memproses & Mengirim Notifikasi ke Telegram Katim...
                            </span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const pegawaiList = @json($pegawais ?? []);
    console.log("Data Pegawai TVRI:", pegawaiList);

    const listJabatanPenyiaran = [
        'Pengarah Teknik', 'Maintenance', 'Operator Komputer', 
        'Penata Aksara', 'Pemadu Gambar', 'Kamerawan 1', 
        'Kamerawan 2', 'Kamerawan 3', 'Kamerawan 4', 
        'Penata Suara', 'Asisten Penata Suara'
    ];
    
    const listJabatanPenunjang = [
        'Dokumentasi Digital & VTR', 'Petugas IT', 'Supir'
    ];

    let globalRowIndex = 0; 

    function getPegawaiOptions() {
        let opts = '<option value="" disabled selected>-- Pilih Personil TVRI --</option>';
        if(pegawaiList.length > 0) {
            pegawaiList.forEach(p => opts += `<option value="${p.nama_lengkap}">${p.nama_lengkap}</option>`);
        } else {
            opts += '<option value="" disabled>Data Pegawai Kosong (Isi Master Pegawai dulu)</option>';
        }
        opts += '<option value="Lainnya" class="fw-bold text-primary">+ Ketik Nama Manual / Pengganti</option>';
        return opts;
    }

    function getJabatanOptions(type, selected = '') {
        const list = (type === 'penyiaran') ? listJabatanPenyiaran : listJabatanPenunjang;
        let opts = '<option value="" disabled selected>-- Pilih Posisi --</option>';
        let isCustom = (selected && !list.includes(selected)); 
        list.forEach(j => {
            let selectedAttr = (j === selected) ? 'selected' : '';
            opts += `<option value="${j}" ${selectedAttr}>${j}</option>`;
        });
        let selectedLainnya = (isCustom || selected === 'Lainnya') ? 'selected' : '';
        opts += `<option value="Lainnya" class="fw-bold text-primary" ${selectedLainnya}>+ Posisi Lainnya</option>`;
        return opts;
    }

    function updateCounters() {
        const penyiaranCount = $('#containerPenyiaran tr').length;
        const penunjangCount = $('#containerPenunjang tr').length;
        const total = penyiaranCount + penunjangCount;

        $('#badgeCountPenyiaran').text(`${penyiaranCount} Posisi`);
        $('#badgeCountPenunjang').text(`${penunjangCount} Posisi`);
        $('#countPenyiaranTxt').text(`${penyiaranCount} Orang`);
        $('#countPenunjangTxt').text(`${penunjangCount} Orang`);
        $('#totalKruBadge').text(`${total} Orang`);
    }

    function addRow(type, jabatanDefault = '') {
        const container = (type === 'penyiaran') ? '#containerPenyiaran' : '#containerPenunjang';
        globalRowIndex++; 
        const currentRowId = globalRowIndex; 

        const html = `
            <tr id="row_${currentRowId}" style="display: none;">
                <td class="ps-md-4">
                    <span class="mobile-label"><i class="fas fa-briefcase me-1 text-primary"></i>Jabatan / Posisi:</span>
                    <select name="${type}[${currentRowId}][jabatan]" class="form-select form-select-modern mb-1" onchange="checkLainnyaJabatan(this)" required>
                        ${getJabatanOptions(type, jabatanDefault)}
                    </select>
                    <input type="text" name="${type}[${currentRowId}][custom_jabatan]" class="form-control form-control-modern form-control-sm bg-light border-primary input-lainnya-jabatan mt-1" placeholder="Ketik nama jabatan..." style="display:none;">
                </td>
                <td>
                    <span class="mobile-label"><i class="fas fa-user me-1 text-success"></i>Nama Personil:</span>
                    <select name="${type}[${currentRowId}][nama]" class="form-select form-select-modern mb-1" onchange="checkLainnyaNama(this)" required>
                        ${getPegawaiOptions()}
                    </select>
                    <input type="text" name="${type}[${currentRowId}][custom_nama]" class="form-control form-control-modern form-control-sm bg-light border-primary input-lainnya-nama mt-1" placeholder="Ketik nama personil..." style="display:none;">
                </td>
                <td class="text-center align-middle td-actions">
                    <button type="button" class="btn btn-soft-danger rounded-circle btn-action-icon shadow-sm" title="Hapus Baris" onclick="removeRow(${currentRowId})">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
        const $el = $(html);
        $(container).append($el);
        $el.fadeIn(250);
        updateCounters();
    }

    window.removeRow = function(id) {
        $(`#row_${id}`).fadeOut(250, function() { 
            $(this).remove(); 
            updateCounters();
        });
    }

    window.checkLainnyaJabatan = function(el) {
        const input = $(el).siblings('.input-lainnya-jabatan');
        $(el).val() === 'Lainnya' ? input.slideDown(200).focus() : input.slideUp(200);
    }
    window.checkLainnyaNama = function(el) {
        const input = $(el).siblings('.input-lainnya-nama');
        $(el).val() === 'Lainnya' ? input.slideDown(200).focus() : input.slideUp(200);
    }

    $(document).ready(function() {
        console.log("Script SPO Ready!");
        
        // Inisialisasi baris standar penyiaran & penunjang
        listJabatanPenyiaran.forEach(j => addRow('penyiaran', j));
        listJabatanPenunjang.forEach(j => addRow('penunjang', j));

        $('#addPenyiaran').click(() => addRow('penyiaran'));
        $('#addPenunjang').click(() => addRow('penunjang'));

        // Auto-sinkronisasi Hari saat Tanggal Pelaksanaan dipilih
        $('#inputTanggal').on('change', function() {
            const dateVal = $(this).val();
            if (!dateVal) return;
            const dateObj = new Date(dateVal + 'T00:00:00');
            const daysIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const dayName = daysIndo[dateObj.getDay()];
            if (dayName) {
                $('#selectHari').val(dayName);
            }
        });

        // Form submit loading state
        $('#formSPO').on('submit', function() {
            $('#btnSubmitSPO').prop('disabled', true);
            $('#textNormalSPO').addClass('d-none');
            $('#textLoadingSPO').removeClass('d-none');
        });
    });
</script>
@endpush
