<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class LaporanIzinExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $data;
    protected $bulan;
    protected $tahun;

    public function __construct($data, $bulan, $tahun)
    {
        $this->data  = $data;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function collection()
    {
        return $this->data;
    }

    public function title(): string
    {
        return 'Laporan Izin & Cuti';
    }

    public function headings(): array
    {
        $namaBulan = Carbon::createFromDate($this->tahun, $this->bulan, 1)->translatedFormat('F Y');
        return [
            ["LAPORAN IZIN & CUTI — {$namaBulan}"],
            [],
            ['No', 'Nama Pegawai', 'NIK', 'Divisi', 'Jenis Izin', 'Tanggal Mulai', 'Tanggal Selesai', 'Alasan', 'Status'],
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $status = match ($row->id_status) {
            2       => 'Disetujui',
            3       => 'Ditolak',
            default => 'Menunggu',
        };

        return [
            $no,
            $row->user->nama_lengkap ?? '-',
            $row->user->nik ?? '-',
            $row->user->divisi->nama_divisi ?? '-',
            $row->jenisIzin->nama_izin ?? '-',
            Carbon::parse($row->tanggal_mulai)->format('d/m/Y'),
            Carbon::parse($row->tanggal_selesai)->format('d/m/Y'),
            $row->alasan ?? '-',
            $status,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 28,
            'C' => 16,
            'D' => 20,
            'E' => 14,
            'F' => 16,
            'G' => 16,
            'H' => 40,
            'I' => 14,
        ];
    }

    public function styles($sheet)
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'size' => 13],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $lastRow   = $this->data->count() + 3;
                $dataRange = "A3:I{$lastRow}";

                // Merge judul
                $sheet->mergeCells('A1:I1');

                // Border semua sel data
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFE2E8F0'],
                        ],
                    ],
                ]);

                // Zebra striping & highlight status
                for ($i = 4; $i <= $lastRow; $i++) {
                    $status = $sheet->getCell("I{$i}")->getValue();

                    if ($status === 'Menunggu') {
                        $sheet->getStyle("A{$i}:I{$i}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFEF9C3');
                    } elseif ($status === 'Ditolak') {
                        $sheet->getStyle("A{$i}:I{$i}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFEE2E2');
                    } elseif ($i % 2 === 0) {
                        $sheet->getStyle("A{$i}:I{$i}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFF8FAFC');
                    }
                }

                // Freeze header
                $sheet->freezePane('A4');

                // Auto wrap alasan
                $sheet->getStyle("H4:H{$lastRow}")->getAlignment()->setWrapText(true);
            },
        ];
    }
}
