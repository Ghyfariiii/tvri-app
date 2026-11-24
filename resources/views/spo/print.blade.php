<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak SPO</title>
    <style>
        /* CSS Khusus untuk Cetak PDF agar rapi */
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; color: #000; }
        .container { width: 100%; padding: 0 20px; }
        
        /* KOP SURAT */
        .header-table { width: 100%; border-bottom: 3px double #000; margin-bottom: 10px; padding-bottom: 10px; }
        .header-left { text-align: left; font-weight: bold; font-size: 14pt; }
        .header-right { text-align: right; font-size: 10pt; }
        
        /* BAGIAN SURAT */
        .meta-table { width: 100%; margin-bottom: 10px; }
        .meta-table td { vertical-align: top; }
        
        .content { text-align: justify; margin-bottom: 10px; }
        
        /* TABEL PETUGAS */
        .petugas-table { width: 100%; border-collapse: collapse; margin-left: 20px; margin-bottom: 10px; }
        .petugas-table td { padding: 2px 5px; vertical-align: top; }
        .petugas-label { width: 180px; }
        
        /* TANDA TANGAN */
        .signature-section { width: 100%; margin-top: 30px; }
        .signature-table { width: 100%; }
        .ttd-box { text-align: right; float: right; width: 40%; }
        
        /* FOOTER / CATATAN */
        .footer { margin-top: 20px; font-size: 10pt; border-top: 1px solid #000; padding-top: 5px; }
    </style>
</head>
<body>

    <div class="container">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    LPP TVRI STASIUN GORONTALO<br>
                    <span style="font-size:12pt; font-weight:normal;">SEKSI TEKNIK & FASILITASI TRANSMISI</span>
                </td>
                <td class="header-right">
                    Jl. Prof.HB Yasin No. 317, Telp.0435-82766<br>
                    Fax. 0435-822997, Kota Gorontalo 96128
                </td>
            </tr>
        </table>

        <table class="meta-table">
            <tr>
                <td width="80">No.</td>
                <td width="10">:</td>
                <td>{{ $spo->no_surat }}</td>
            </tr>
            <tr>
                <td>Lamp.</td>
                <td>:</td>
                <td>-</td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td><strong>Surat Perintah Operasional</strong></td>
            </tr>
        </table>

        <div class="content">
            Kepada Yth,<br>
            Kepala TVRI Stasiun Gorontalo<br>
            di-<br>
            Gorontalo
        </div>

        <div class="content">
            Dengan hormat,<br>
            Sehubungan dengan adanya operasional siaran harian, maka melalui surat ini kami sampaikan petugas yang akan melaksanakan operasional siaran dan penunjang penyiaran pada <strong>{{ $spo->hari }}, {{ date('d F Y', strtotime($spo->tanggal_pelaksanaan)) }}</strong> sebagai berikut:
        </div>

        <div class="content">
            <strong>I. Penyiaran :</strong>
            <table class="petugas-table">
                <tr><td class="petugas-label">1. Pengarah Teknik</td><td>:</td><td>{{ $spo->pengarah_teknik }}</td></tr>
                <tr><td class="petugas-label">2. Maintenance</td><td>:</td><td>{{ $spo->maintenance }}</td></tr>
                <tr><td class="petugas-label">3. Operator Komputer</td><td>:</td><td>{{ $spo->operator_komputer }}</td></tr>
                <tr><td class="petugas-label">4. Penata Aksara</td><td>:</td><td>{{ $spo->penata_aksara }}</td></tr>
                <tr><td class="petugas-label">5. Pemadu Gambar</td><td>:</td><td>{{ $spo->pemadu_gambar }}</td></tr>
                <tr><td class="petugas-label">6. Kamerawan 1</td><td>:</td><td>{{ $spo->kamerawan1 }}</td></tr>
                <tr><td class="petugas-label">7. Kamerawan 2</td><td>:</td><td>{{ $spo->kamerawan2 }}</td></tr>
                <tr><td class="petugas-label">8. Kamerawan 3</td><td>:</td><td>{{ $spo->kamerawan3 }}</td></tr>
                <tr><td class="petugas-label">9. Penata Suara</td><td>:</td><td>{{ $spo->penata_suara }}</td></tr>
            </table>
        </div>

        <div class="content">
            <strong>II. Penunjang Penyiaran :</strong>
            <table class="petugas-table">
                <tr><td class="petugas-label">Dokumentasi Digital & VTR</td><td>:</td><td>{{ $spo->dokumentasi }}</td></tr>
                <tr><td class="petugas-label">Petugas IT</td><td>:</td><td>{{ $spo->it_support }}</td></tr>
            </table>
        </div>

        <div class="content">
            Demikian kami sampaikan. Atas kerjasama yang baik, diucapkan terima kasih.
        </div>

        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td width="50%"></td> <td width="50%" align="center">
                        Gorontalo, {{ date('d F Y', strtotime($spo->created_at)) }}<br>
                        Ketua Tim Teknik<br>
                        <br>
                        <img src="data:image/svg+xml;base64, {{ $qrcode }}" alt="QR Code" width="80">
                        <br>
                        <br>
                        {{-- DIUBAH MENJADI DINAMIS BERDASARKAN PILIHAN KATIM --}}
                        <u><strong>{{ $katim->name ?? 'Belum Ditentukan' }}</strong></u><br> 
                        NIP. {{ $katim->nip ?? '-' }}
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <strong>Catatan :</strong>
            <ol>
                <li>SPO ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.</li>
                <li>Crew yang bertugas operasional hadir 1 jam sebelum siaran.</li>
                <li>Dokumen ini ditandatangani secara digital menggunakan QR Code.</li>
            </ol>
        </div>
    </div>

</body>
</html>