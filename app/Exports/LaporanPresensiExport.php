<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class LaporanPresensiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $rekap;
    protected $bulan;
    protected $tahun;

    public function __construct(array $rekap, $bulan, $tahun)
    {
        $this->rekap  = $rekap;
        $this->bulan  = $bulan;
        $this->tahun  = $tahun;
    }

    public function title(): string
    {
        return 'Presensi ' . Carbon::createFromDate($this->tahun, (int) $this->bulan, 1)->translatedFormat('F Y');
    }

    public function collection()
    {
        return collect($this->rekap);
    }

    public function headings(): array
    {
        $namaBulan = Carbon::createFromDate($this->tahun, (int) $this->bulan, 1)->translatedFormat('F');

        return [
            ['LAPORAN REKAPITULASI PRESENSI PEGAWAI'],
            ['MPG HRIS Enterprise System'],
            ['Periode: ' . $namaBulan . ' ' . $this->tahun],
            ['Dicetak: ' . Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB'],
            [],
            [
                'No',
                'NIK / ID Karyawan',
                'Nama Lengkap',
                'Jabatan',
                'Divisi',
                'Hadir (Hari)',
                'Izin / Cuti (Hari)',
                'Sakit (Hari)',
                'Alpha / Mangkir (Hari)',
                'Terlambat (Kali)',
                'Poin Lembur',
            ],
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row['user']->nik ?? '-',
            $row['user']->nama_lengkap,
            $row['user']->jabatan->nama_jabatan ?? '-',
            $row['user']->divisi->nama_divisi ?? '-',
            $row['hadir'],
            $row['izin'],
            $row['sakit'],
            $row['alpha'],
            $row['terlambat'],
            $row['poin_lembur'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' =>  5,
            'B' => 22,
            'C' => 28,
            'D' => 20,
            'E' => 20,
            'F' => 14,
            'G' => 18,
            'H' => 14,
            'I' => 22,
            'J' => 18,
            'K' => 14,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->rekap) + 7;

        return [
            // Judul utama
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E293B']],
            ],
            // Sub judul
            2 => [
                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '64748B']],
            ],
            // Periode
            3 => [
                'font' => ['size' => 10, 'color' => ['rgb' => '475569']],
            ],
            // Tanggal cetak
            4 => [
                'font' => ['size' => 9, 'color' => ['rgb' => '94A3B8']],
            ],
            // Header kolom
            6 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => '334155'],
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet    = $event->sheet->getDelegate();
                $lastRow  = count($this->rekap) + 6;
                $dataStart = 7;

                // Merge judul
                $sheet->mergeCells('A1:K1');
                $sheet->mergeCells('A2:K2');
                $sheet->mergeCells('A3:K3');
                $sheet->mergeCells('A4:K4');

                // Alignment judul
                foreach (['A1', 'A2', 'A3', 'A4'] as $cell) {
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Border + alignment data baris
                for ($r = $dataStart; $r <= $lastRow; $r++) {
                    $isEven = ($r - $dataStart) % 2 === 1;

                    $sheet->getStyle("A{$r}:K{$r}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['rgb' => 'E2E8F0'],
                            ],
                        ],
                        'fill' => $isEven ? [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8FAFC'],
                        ] : [],
                    ]);

                    // Kolom A (No) — center
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Kolom F–K (angka) — center
                    $sheet->getStyle("F{$r}:K{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Bold nama karyawan
                    $sheet->getStyle("C{$r}")->getFont()->setBold(true);

                    // Highlight alpha > 0 (merah muda)
                    $alphaVal = $sheet->getCell("I{$r}")->getValue();
                    if ($alphaVal > 0) {
                        $sheet->getStyle("I{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
                            'font' => ['color' => ['rgb' => '991B1B'], 'bold' => true],
                        ]);
                    }
                }

                // Tinggi baris header
                $sheet->getRowDimension(6)->setRowHeight(30);

                // Freeze panel di baris header + kolom nama
                $sheet->freezePane('C7');

                // Ringkasan di bawah tabel
                $summaryRow = $lastRow + 2;
                $totalHadir     = array_sum(array_column($this->rekap, 'hadir'));
                $totalIzin      = array_sum(array_column($this->rekap, 'izin'));
                $totalSakit     = array_sum(array_column($this->rekap, 'sakit'));
                $totalAlpha     = array_sum(array_column($this->rekap, 'alpha'));
                $totalTerlambat = array_sum(array_column($this->rekap, 'terlambat'));
                $totalPoin      = array_sum(array_column($this->rekap, 'poin_lembur'));

                $sheet->setCellValue("A{$summaryRow}", 'RINGKASAN');
                $sheet->setCellValue("B{$summaryRow}", 'Total Hadir: ' . $totalHadir . ' hari');
                $sheet->setCellValue("C{$summaryRow}", 'Total Izin: ' . $totalIzin . ' hari');
                $sheet->setCellValue("D{$summaryRow}", 'Total Sakit: ' . $totalSakit . ' hari');
                $sheet->setCellValue("E{$summaryRow}", 'Total Alpha: ' . $totalAlpha . ' hari');
                $sheet->setCellValue("F{$summaryRow}", 'Terlambat: ' . $totalTerlambat . 'x');
                $sheet->setCellValue("G{$summaryRow}", 'Poin Lembur: ' . $totalPoin);

                $sheet->getStyle("A{$summaryRow}:G{$summaryRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '1E293B']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                    ],
                ]);

                // Catatan kaki
                $noteRow = $summaryRow + 2;
                $sheet->setCellValue("A{$noteRow}", '* Laporan dibuat otomatis oleh sistem MPG HRIS. Data bersumber dari catatan presensi dan lembur bulan berjalan.');
                $sheet->getStyle("A{$noteRow}")->getFont()->setItalic(true)->setSize(8)->getColor()->setRGB('94A3B8');
                $sheet->mergeCells("A{$noteRow}:K{$noteRow}");
            },
        ];
    }
}
