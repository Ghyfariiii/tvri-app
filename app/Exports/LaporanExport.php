<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanExport implements FromView, WithColumnWidths, WithStyles
{
    protected $request;

    public function __construct($request = null)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $query = DB::table('monitoring_harians')
            ->leftJoin('spos', 'monitoring_harians.spo_id', '=', 'spos.id')
            ->join('users', 'monitoring_harians.created_by', '=', 'users.id')
            ->select(
                'monitoring_harians.*',
                'spos.no_surat',
                'users.name as penginput'
            );

        if ($this->request) {
            if ($this->request->filled('tanggal_dari')) {
                $query->where('monitoring_harians.tanggal', '>=', $this->request->tanggal_dari);
            }
            if ($this->request->filled('tanggal_sampai')) {
                $query->where('monitoring_harians.tanggal', '<=', $this->request->tanggal_sampai);
            }
            if ($this->request->filled('status_siaran') && $this->request->status_siaran !== 'semua') {
                $query->where('monitoring_harians.status_siaran', $this->request->status_siaran);
            }
            if ($this->request->filled('status_revisi') && $this->request->status_revisi !== 'semua') {
                $query->where('monitoring_harians.status_revisi', $this->request->status_revisi);
            }
            if ($this->request->filled('search')) {
                $search = $this->request->search;
                $query->where(function($q) use ($search) {
                    $q->where('spos.no_surat', 'LIKE', "%{$search}%")
                      ->orWhere('users.name', 'LIKE', "%{$search}%");
                });
            }
        }

        $laporan = $query->orderBy('monitoring_harians.tanggal', 'desc')->get();

        return view('monitoring.export_excel', [
            'laporan' => $laporan
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 18,
            'C' => 25,
            'D' => 25,
            'E' => 25,
            'F' => 25,
            'G' => 18,
            'H' => 45,
            'I' => 45,
            'J' => 18,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = max($sheet->getHighestRow(), 1);

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1A3B70'],
                ],
            ],
            'A1:J'.$lastRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFCCCCCC'],
                    ],
                ],
                'alignment' => ['vertical' => 'top', 'wrapText' => true],
            ],
        ];
    }
}
