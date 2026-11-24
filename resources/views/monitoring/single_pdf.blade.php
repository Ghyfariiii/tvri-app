<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan TD - {{ $laporan->tanggal }}</title>
    <style>
        @page {
            margin: 15mm 20mm 15mm 20mm;
            size: a4 portrait;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            line-height: 1.35;
            color: #000;
        }

        /* HEADER */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .header-table td {
            border: none;
            padding: 0;
        }

        .header-logo {
            width: 60px;
            vertical-align: middle;
        }

        .header-logo img {
            max-height: 48px;
            max-width: 58px;
        }

        .header-text {
            vertical-align: middle;
            padding-left: 10px;
        }

        .header-title-1 {
            font-size: 13pt;
            font-weight: bold;
            line-height: 1.2;
            color: #000;
        }

        .header-title-2 {
            font-size: 11pt;
            font-weight: bold;
            line-height: 1.2;
            color: #000;
        }

        /* SECTION LABEL */
        .section-label {
            font-size: 10pt;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 6px;
            text-transform: uppercase;
            color: #000;
        }

        /* PETUGAS OPERASIONAL (2 KOLOM) */
        .crew-table {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }

        .crew-table > tbody > tr > td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .crew-col {
            width: 50%;
        }

        .crew-subtable {
            width: 100%;
            border-collapse: collapse;
        }

        .crew-subtable td {
            border: none;
            padding: 2px 2px;
            font-size: 9pt;
            vertical-align: top;
        }

        .crew-role {
            width: 46%;
            color: #000;
        }

        .crew-sep {
            width: 5%;
            text-align: center;
        }

        .crew-name {
            width: 49%;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
        }

        /* TABEL KEGIATAN & KENDALA */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 6px;
            margin-bottom: 20px;
        }

        .data-table th {
            border: 1px solid #000;
            padding: 6px 5px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #ffffff;
            color: #000;
        }

        .data-table td {
            border: 1px solid #000;
            padding: 6px 6px;
            vertical-align: top;
            color: #000;
        }

        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }

        /* TANDA TANGAN */
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .ttd-table td {
            border: none;
            vertical-align: top;
            padding: 0;
        }

        .ttd-box {
            text-align: center;
            font-size: 9.5pt;
            width: 250px;
        }

        .ttd-space {
            height: 55px;
        }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('images/logo.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    @endphp

    <!-- HEADER: LOGO + INSTANSI -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo TVRI">
                @else
                    <span style="font-size: 22pt; font-weight: bold;">TVRI</span>
                @endif
            </td>
            <td class="header-text">
                <div class="header-title-1">LPP TVRI STASIUN GORONTALO</div>
                <div class="header-title-2">Laporan Pengarah Teknik (TD)</div>
            </td>
        </tr>
    </table>

    <!-- HARI / TANGGAL -->
    <div class="section-label">
        HARI / TANGGAL : {{ strtoupper(\Carbon\Carbon::parse($laporan->tanggal)->locale('id')->translatedFormat('d F Y')) }}
    </div>

    <!-- PETUGAS OPERASIONAL -->
    <div class="section-label">
        PETUGAS OPERASIONAL :
    </div>

    @php
        $crewCount = count($kruBertugas);
        $half = ceil($crewCount / 2);
        $kruLeft = array_slice($kruBertugas, 0, $half);
        $kruRight = array_slice($kruBertugas, $half);
    @endphp

    <table class="crew-table">
        <tr>
            <!-- KOLOM KIRI -->
            <td class="crew-col" style="padding-right: 15px;">
                <table class="crew-subtable">
                    @forelse($kruLeft as $kru)
                        <tr>
                            <td class="crew-role">{{ $kru['jabatan'] ?? '-' }}</td>
                            <td class="crew-sep">:</td>
                            <td class="crew-name">{{ $kru['nama'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="font-style: italic; color: #666;">-</td>
                        </tr>
                    @endforelse
                </table>
            </td>

            <!-- KOLOM KANAN -->
            <td class="crew-col" style="padding-left: 15px;">
                <table class="crew-subtable">
                    @forelse($kruRight as $kru)
                        <tr>
                            <td class="crew-role">{{ $kru['jabatan'] ?? '-' }}</td>
                            <td class="crew-sep">:</td>
                            <td class="crew-name">{{ $kru['nama'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="font-style: italic; color: #666;">-</td>
                        </tr>
                    @endforelse
                </table>
            </td>
        </tr>
    </table>

    <!-- LAPORAN KEGIATAN & KENDALA -->
    <div class="section-label" style="margin-top: 14px;">
        LAPORAN KEGIATAN &amp; KENDALA :
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">JAM</th>
                <th style="width: 32%;">KEGIATAN</th>
                <th style="width: 18%;">STATUS</th>
                <th style="width: 15%;">KENDALA</th>
                <th style="width: 25%;">SOLUSI &amp; CATATAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($details as $d)
                <tr>
                    <td class="text-center text-bold">
                        {{ date('H:i', strtotime($d->jam)) }}
                    </td>
                    <td class="text-uppercase">
                        {{ $d->kegiatan }}
                    </td>
                    <td class="text-center text-uppercase">
                        @if(strtolower(trim($d->status)) === 'lancar')
                            BERJALAN LANCAR
                        @elseif(strtolower(trim($d->status)) === 'kendala')
                            TERDAPAT KENDALA
                        @elseif(strtolower(trim($d->status)) === 'gagal')
                            SIARAN GAGAL
                        @else
                            {{ strtoupper($d->status) }}
                        @endif
                    </td>
                    <td class="text-center">
                        @if(!empty($d->nama_alat) || !empty($d->deskripsi_kendala) || !empty($d->jenis_kendala))
                            {{ $d->nama_alat ?? ($d->deskripsi_kendala ?? $d->jenis_kendala) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: bold;">Solusi:</div>
                        <div style="margin-bottom: 5px;">{{ !empty($d->solusi) ? $d->solusi : '-' }}</div>
                        <div style="font-weight: bold;">Catatan:</div>
                        <div>{{ !empty($d->catatan) ? $d->catatan : '-' }}</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 15px; font-style: italic;">
                        Tidak ada catatan kegiatan siaran pada laporan ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN (POJOK KANAN BAWAH) -->
    <table class="ttd-table">
        <tr>
            <td style="width: 55%;"></td>
            <td style="width: 45%;">
                <div class="ttd-box" style="float: right;">
                    Gorontalo, {{ \Carbon\Carbon::parse($laporan->tanggal)->locale('id')->translatedFormat('d F Y') }}<br>
                    Pengarah Teknik,
                    <div class="ttd-space">
                        <!-- Ruang tanda tangan -->
                    </div>
                    <strong style="text-decoration: underline; text-transform: uppercase;">
                        {{ $laporan->penginput ?? 'Pengarah Teknik' }}
                    </strong>
                    @if(!empty($laporan->nip_penginput))
                        <br>NIP. {{ $laporan->nip_penginput }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
