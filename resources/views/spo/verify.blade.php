<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen TVRI</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .verify-box { max-width: 600px; margin: 40px auto; text-align: center; }
        .icon-box { font-size: 80px; margin-bottom: 20px; }
        .valid { color: #28a745; }
        .invalid { color: #dc3545; }
        .pending { color: #ffc107; }
    </style>
</head>
<body>

<div class="container">
    <div class="verify-box">
        <div class="card shadow-lg border-0 rounded-lg">
            <div class="card-body p-5">
                
                {{-- PEMBARUAN: Memanggil logo.png dari dalam folder public/images/ --}}
                <img src="{{ asset('images/logo.png') }}" alt="Logo TVRI Gorontalo" width="130" class="mb-4">

                @if($spo && ($spo->status_persetujuan == 'approved' || Auth::check()))
                    
                    @if($spo->status_persetujuan == 'approved')
                        <div class="icon-box valid">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2 class="text-success font-weight-bold">DOKUMEN VALID</h2>
                        <p class="lead mt-3">Surat Perintah Operasional (SPO) ini adalah dokumen asli yang diterbitkan oleh TVRI Stasiun Gorontalo.</p>
                    @elseif($spo->status_persetujuan == 'rejected')
                        <div class="icon-box invalid">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h2 class="text-danger font-weight-bold">SPO DITOLAK</h2>
                        <p class="lead mt-3">Surat Perintah Operasional ini telah <strong>ditolak</strong> oleh Katim dan tidak berlaku untuk pelaksanaan siaran.</p>
                        @if(!empty($spo->alasan_penolakan))
                        <div class="alert alert-danger text-left rounded shadow-sm mt-3">
                            <i class="fas fa-comment-dots mr-2"></i><strong>Alasan Penolakan dari Katim:</strong>
                            <p class="mb-0 mt-1 font-italic">{{ $spo->alasan_penolakan }}</p>
                        </div>
                        @endif
                    @else
                        <div class="icon-box pending">
                            <i class="fas fa-search"></i>
                        </div>
                        <h2 class="text-warning font-weight-bold">PRATINJAU DOKUMEN</h2>
                        <p class="lead mt-3">SPO ini berstatus <strong>Menunggu Persetujuan</strong> dan halaman ini hanya dapat dilihat oleh pegawai internal TVRI.</p>
                    @endif
                    
                    <hr>
                    <div class="text-left bg-light p-4 rounded mb-4 shadow-sm">
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-muted">Nomor Surat</dt>
                            <dd class="col-sm-8 font-weight-bold">{{ $spo->no_surat }}</dd>

                            <dt class="col-sm-4 text-muted">Tgl Tugas</dt>
                            <dd class="col-sm-8">{{ $spo->hari }}, {{ date('d M Y', strtotime($spo->tanggal_pelaksanaan)) }}</dd>

                            <dt class="col-sm-4 text-muted">Katim Tujuan</dt>
                            <dd class="col-sm-8">{{ $penyetuju->name ?? 'KATIM' }}</dd>

                            @if($spo->status_persetujuan == 'approved')
                            <dt class="col-sm-4 text-muted">Tgl Disetujui</dt>
                            <dd class="col-sm-8 text-success font-weight-bold">{{ date('d M Y H:i', strtotime($spo->tanggal_disetujui)) }}</dd>
                            @elseif($spo->status_persetujuan == 'rejected')
                            <dt class="col-sm-4 text-muted">Status</dt>
                            <dd class="col-sm-8 text-danger font-weight-bold">Ditolak</dd>
                            @endif
                        </dl>
                    </div>

                    @if(Auth::check())
                        <div class="text-left bg-white border p-4 rounded shadow-sm">
                            <h5 class="font-weight-bold border-bottom pb-2 mb-3 text-info"><i class="fas fa-users mr-2"></i>Daftar Petugas Penugasan</h5>
                            
                            @php
                                $penyiaran = json_decode($spo->petugas_penyiaran, true) ?? [];
                                $penunjang = json_decode($spo->petugas_penunjang, true) ?? [];
                                $semuaPetugas = array_merge($penyiaran, $penunjang);
                                
                                $setengah = ceil(count($semuaPetugas) / 2);
                                $kolomKiri = array_slice($semuaPetugas, 0, $setengah);
                                $kolomKanan = array_slice($semuaPetugas, $setengah);
                            @endphp

                            <div class="row text-sm">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        @forelse($kolomKiri as $petugas)
                                            <li class="mb-3">
                                                <strong class="text-dark">{{ $petugas['jabatan'] }}:</strong><br>
                                                <span class="text-primary">{{ $petugas['nama'] }}</span>
                                            </li>
                                        @empty
                                            <li class="text-muted small">Tidak ada petugas</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        @foreach($kolomKanan as $petugas)
                                            <li class="mb-3">
                                                <strong class="text-dark">{{ $petugas['jabatan'] }}:</strong><br>
                                                <span class="text-primary">{{ $petugas['nama'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                @else
                    <div class="icon-box invalid">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h2 class="text-danger font-weight-bold">TIDAK VALID</h2>
                    <p class="lead">Maaf, data dokumen tidak ditemukan atau belum disetujui dalam sistem kami.</p>
                @endif

                <div class="mt-4">
                    @if(Auth::check())
                        <a href="{{ url('/spo') }}" class="btn btn-secondary btn-block py-2"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard</a>
                    @else
                        <a href="{{ url('/login') }}" class="btn btn-primary btn-block py-2">Masuk ke Aplikasi</a>
                    @endif
                </div>
            </div>
            <div class="card-footer text-muted bg-white border-top-0 pb-4">
                &copy; {{ date('Y') }} TVRI Gorontalo System
            </div>
        </div>
    </div>
</div>

</body>
</html>