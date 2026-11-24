@extends('adminlte::page')

@section('title', 'Dashboard Admin')

@section('content_header')
    <h1>Dashboard & Analitik</h1>
@stop

@section('content')
<div class="container-fluid">
    
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $total_spo }}</h3>
                    <p>Total SPO Diajukan</p>
                </div>
                <div class="icon"><i class="fas fa-file-alt"></i></div>
                <a href="{{ route('spo.index') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $spo_pending }}</h3>
                    <p>SPO Pending</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
                <a href="{{ route('spo.index') }}" class="small-box-footer">Segera Tinjau <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $total_laporan }}</h3>
                    <p>Laporan Monitoring</p>
                </div>
                <div class="icon"><i class="fas fa-tv"></i></div>
                <a href="{{ route('monitoring.index') }}" class="small-box-footer">Lihat Data <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $total_kendala }}</h3>
                    <p>Total Kendala Tercatat</p>
                </div>
                <div class="icon"><i class="fas fa-tools"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">Statistik Jenis Kendala</h3>
                </div>
                <div class="card-body">
                    <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
                <div class="card-footer text-center">
                    <small>Grafik ini menampilkan distribusi masalah teknis yang sering terjadi.</small>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Selamat Datang, {{ Auth::user()->name }}!</h5>
                    
                    <p class="card-text">
                        Ini adalah halaman pusat kontrol. Anda dapat melihat ringkasan kinerja operasional teknik TVRI Gorontalo di sini.
                    </p>
                    
                    @if(Auth::user()->role == 'admin')
                        <p class="text-muted">Sebagai <b>Admin</b>, Anda memiliki akses penuh ke Manajemen Pengguna dan Data Master.</p>
                    @else
                        <p class="text-muted">Sebagai <b>Katim</b>, Anda bertugas memantau kinerja dan menyetujui (Approve) SPO yang masuk.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Ambil data dari Controller PHP ke Javascript
    var labelData = @json($label_kendala);
    var valueData = @json($data_kendala);

    // Konfigurasi Pie Chart
    var ctx = document.getElementById('pieChart').getContext('2d');
    var donutChart = new Chart(ctx, {
        type: 'doughnut', // Bisa diganti 'pie'
        data: {
            labels: labelData,
            datasets: [{
                data: valueData,
                backgroundColor : ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
            }]
        },
        options: {
            maintainAspectRatio : false,
            responsive : true,
        }
    });
</script>
@stop