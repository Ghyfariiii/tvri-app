@extends('adminlte::page')

@section('title', 'Dashboard Kru')

@section('content_header')
    <h1>Dashboard Kru</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <p>Halo rekan, <b>{{ Auth::user()->name }}</b>!</p>
            <p>Anda login sebagai KRU. Silakan buat SPO baru atau isi laporan Monitoring Siaran.</p>
        </div>
    </div>
@stop