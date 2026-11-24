<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Monitoring Harian Siaran</title>
    <style>
        @page {
            margin: 12mm 15mm 15mm 15mm;
            size: a4 landscape;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #111;
        }

        /* KOP SURAT */
        .kop-table {
            width: 100%;
            border-bottom: 3px double #000;
            margin-bottom: 12px;
            padding-bottom: 8px;
        }

        .kop-logo {
            width: 80px;
            text-align: center;
            vertical-align: middle;
        }

        .kop-text {
            text-align: center;
            vertical-align: middle;
        }

        .kop-instansi {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .kop-unit {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 2px;
        }

        .kop-alamat {
            font-size: 8.5pt;
            font-style: italic;
            margin-top: 3px;
            color: #333;
        }

        /* JUDUL LAPORAN */
        .judul-laporan {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 6px;
            margin-bottom: 4px;
            text-decoration: underline;
        }

        .subjudul {
            text-align: center;
            font-size: 9pt;
            color: #444;
            margin-bottom: 15px;
        }

        /* TABEL MONITORING */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }

        .data-table th {
            background-color: #1a3b70;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 6px 4px;
            border: 1px solid #10264b;
        }

        .data-table td {
            padding: 5px 4px;
            border: 1px solid #999;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .badge-status {
            font-weight: bold;
            font-size: 7.5pt;
            padding: 2px 4px;
            border-radius: 3px;
            display: inline-block;
        }

        .status-sukses {
            color: #0f5132;
        }

        .status-kendala {
            color: #856404;
        }

        .status-gagal {
            color: #842029;
        }

        .kru-item {
            font-size: 8pt;
            margin-bottom: 2px;
        }

        /* BAGIAN TANDA TANGAN */
        .ttd-section {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .ttd-table {
            width: 100%;
        }

        .ttd-box {
            text-align: center;
            width: 35%;
        }

        .ttd-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="kop-table">
        <tr>
            <td class="kop-text">
                <div class="kop-instansi">LEMBAGA PENYIARAN PUBLIK TELEVISI REPUBLIK INDONESIA</div>
                <div class="kop-unit">STASIUN GORONTALO - SEKSI TEKNIK & FASILITASI TRANSMISI</div>
                <div class="kop-alamat">
                    Jl. Prof. Dr. H. Aloei Saboe No. 317, Telp. (0435) 82766, Fax. (0435) 822997, Kota Gorontalo 96128
                </div>
            </td>
        </tr>
    </table>

    <div class="judul-laporan">LAPORAN MONITORING HARIAN OPERASIONAL PENYIARAN</div>
    <div class="subjudul">
        Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WITA | Total Laporan: {{ count($monitorings) }} Data
    </div>

    <!-- TABEL DATA MONITORING -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="9%">Tanggal</th>
                <th width="12%">No. SPO</th>
                <th width="12%">Pengarah Teknik (TD)</th>
                <th width="18%">Kru Bertugas (Aktual)</th>
                <th width="8%">Kualitas Audio</th>
                <th width="8%">Kualitas Video</th>
                <th width="9%">Status Siaran</th>
                <th width="13%">Catatan Khusus (Log Book)</th>
                <th width="8%">Status Revisi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($monitorings as $index => $item)
            @php
                $kruList = json_decode($item->kru_bertugas, true) ?? [];
                
                $statusSiaran = match($item->status_siaran ?? '') {
                    'sukses' => 'SUKSES',
                    'kendala' => 'KENDALA',
                    'gagal' => 'GAGAL',
                    default => strtoupper($item->status_siaran ?? '-')
                };

                $siaranClass = match($item->status_siaran ?? '') {
                    'sukses' => 'status-sukses',
                    'kendala' => 'status-kendala',
                    'gagal' => 'status-gagal',
                    default => ''
                };

                $statusRevisi = match($item->status_revisi ?? 'none') {
                    'pending' => 'PENDING',
                    'approved' => 'DISETUJUI',
                    'rejected' => 'DITOLAK',
                    default => 'NORMAL'
                };
            @endphp
            <tr style="{{ $index % 2 == 1 ? 'background-color: #fcfcfc;' : '' }}">
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $item->no_surat ?? '-' }}</td>
                <td><strong>{{ $item->pelapor ?? $item->penginput ?? '-' }}</strong></td>
                <td>
                    @if(count($kruList) > 0)
                        @foreach($kruList as $kru)
                            <div class="kru-item">• <strong>{{ $kru['jabatan'] ?? '' }}:</strong> {{ $kru['nama'] ?? '-' }}</div>
                        @endforeach
                    @else
                        <span style="color: #888;">-</span>
                    @endif
                </td>
                <td class="text-center">{{ $item->kondisi_audio ?? '-' }}</td>
                <td class="text-center">{{ $item->kondisi_video ?? '-' }}</td>
                <td class="text-center">
                    <span class="badge-status {{ $siaranClass }}">
                        {{ $statusSiaran }}
                    </span>
                </td>
                <td>{{ $item->catatan_khusus ?: '-' }}</td>
                <td class="text-center">
                    <strong>{{ $statusRevisi }}</strong>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 20px; color: #777;">
                    Tidak ada data laporan monitoring siaran.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN -->
    <div class="ttd-section">
        <table class="ttd-table">
            <tr>
                <td width="65%"></td>
                <td class="ttd-box">
                    Gorontalo, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    <strong>Ketua Tim Teknik & Transmisi</strong><br>
                    LPP TVRI Stasiun Gorontalo
                    <div class="ttd-space"></div>
                    <u><strong>( .................................................. )</strong></u><br>
                    NIP. ..................................................
                </td>
            </tr>
        </table>
    </div>

</body>
</html>

