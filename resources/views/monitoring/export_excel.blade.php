<table>
    <thead>
        <tr>
            <th style="background-color: #1a3b70; color: #ffffff; font-weight: bold; text-align: center;">No</th>
            <th style="background-color: #1a3b70; color: #ffffff; font-weight: bold; text-align: center;">Tanggal Laporan</th>
            <th style="background-color: #1a3b70; color: #ffffff; font-weight: bold; text-align: center;">No. SPO</th>
            <th style="background-color: #1a3b70; color: #ffffff; font-weight: bold; text-align: center;">Pengarah Teknik (TD)</th>
            <th style="background-color: #1a3b70; color: #ffffff; font-weight: bold; text-align: center;">Kualitas Audio</th>
            <th style="background-color: #1a3b70; color: #ffffff; font-weight: bold; text-align: center;">Kualitas Video</th>
            <th style="background-color: #1a3b70; color: #ffffff; font-weight: bold; text-align: center;">Status Siaran</th>
            <th style="background-color: #1a3b70; color: #ffffff; font-weight: bold; text-align: center;">Kru Bertugas (Aktual)</th>
            <th style="background-color: #1a3b70; color: #ffffff; font-weight: bold; text-align: center;">Catatan Khusus (Log Book)</th>
            <th style="background-color: #1a3b70; color: #ffffff; font-weight: bold; text-align: center;">Status Revisi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($laporan as $index => $row)
        @php
            $kruList = json_decode($row->kru_bertugas, true) ?? [];
            $kruString = collect($kruList)->map(function($k) {
                return ($k['jabatan'] ?? '') . ': ' . ($k['nama'] ?? '');
            })->implode("\n");

            $statusSiaran = match($row->status_siaran ?? '') {
                'sukses' => 'SUKSES',
                'kendala' => 'DENGAN KENDALA',
                'gagal' => 'GAGAL TAYANG',
                default => strtoupper($row->status_siaran ?? '-')
            };

            $statusRevisi = match($row->status_revisi ?? 'none') {
                'pending' => 'PENDING',
                'approved' => 'DISETUJUI',
                'rejected' => 'DITOLAK',
                default => 'NORMAL'
            };
        @endphp
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td style="text-align: center;">{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y') }}</td>
            <td>{{ $row->no_surat ?? '-' }}</td>
            <td>{{ $row->penginput }}</td>
            <td>{{ $row->kondisi_audio }}</td>
            <td>{{ $row->kondisi_video }}</td>
            <td style="text-align: center;">{{ $statusSiaran }}</td>
            <td>{{ $kruString ?: '-' }}</td>
            <td>{{ $row->catatan_khusus ?? '-' }}</td>
            <td style="text-align: center;">{{ $statusRevisi }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
