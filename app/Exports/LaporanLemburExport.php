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

class LaporanLemburExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $pegawai;
    protected $rekap;
    protected $bulan;
    protected $tahun;

    public function __construct($pegawai, array $rekap, $bulan, $tahun)
    {
        $this->pegawai = $pegawai;
        $this->rekap   = $rekap;
        $this->bulan   = $bulan;
        $this->tahun   = $tahun;
    }

    public function title(): string
    {
        return 'Lembur ' . Carbon::create()->month($this->bulan)->translatedFormat('F Y');
    }

    public function collection()
    {
        return collect($this->pegawai);
    }

    public function headings(): array
    {
        $namaBulan = Carbon::create()->month($this->bulan)->translatedFormat('F');

        return [
            ['LAPORAN REKAPITULASI LEMBUR PEGAWAI'],
            ['MPG HRIS Enterprise System'],
            ['Periode: ' . $namaBulan . ' ' . $this->tahun],
            ['Dicetak: ' . Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB'],
            [],
            [
                'No',
                'NIK / ID Karyawan',
                'Nama Lengkap',
                'Divisi / Dept',
                'Jabatan',
                'Jumlah Hari Lembur',
                'Total Waktu (Menit)',
                'Durasi (Jam)',
                'Poin Lembur Diperoleh',
            ],
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $dataRekap = $this->rekap[$row->id] ?? [
            'total_menit'   => 0,
            'format_jam'    => '0j 0m',
            'jumlah_hari'   => 0,
            'poin_diperoleh'=> 0,
        ];

        return [
            $no,
            $row->nik ?? '-',
            $row->nama_lengkap,
            $row->divisi->nama_divisi ?? '-',
            $row->jabatan->nama_jabatan ?? '-',
            $dataRekap['jumlah_hari'],
            $dataRekap['total_menit'],
            $dataRekap['format_jam'],
            $dataRekap['poin_diperoleh'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' =>  5,
            'B' => 22,
            'C' => 28,
            'D' => 22,
            'E' => 20,
            'F' => 18,
            'G' => 20,
            'H' => 14,
            'I' => 22,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E293B']],
            ],
            2 => [
                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '64748B']],
            ],
            3 => [
                'font' => ['size' => 10, 'color' => ['rgb' => '475569']],
            ],
            4 => [
                'font' => ['size' => 9, 'color' => ['rgb' => '94A3B8']],
            ],
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
                $sheet     = $event->sheet->getDelegate();
                $lastRow   = count($this->pegawai) + 6;
                $dataStart = 7;

                $sheet->mergeCells('A1:I1');
                $sheet->mergeCells('A2:I2');
                $sheet->mergeCells('A3:I3');
                $sheet->mergeCells('A4:I4');

                // Border + zebra striping
                for ($r = $dataStart; $r <= $lastRow; $r++) {
                    $isEven = ($r - $dataStart) % 2 === 1;

                    $sheet->getStyle("A{$r}:I{$r}")->applyFromArray([
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

                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$r}:I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$r}")->getFont()->setBold(true);

                    // Highlight poin lembur > 0 (ungu muda)
                    $poinVal = $sheet->getCell("I{$r}")->getValue();
                    if ($poinVal > 0) {
                        $sheet->getStyle("I{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3E8FF']],
                            'font' => ['color' => ['rgb' => '6B21A8'], 'bold' => true],
                        ]);
                    }
                }

                $sheet->getRowDimension(6)->setRowHeight(30);
                $sheet->freezePane('C7');

                // Ringkasan total
                $summaryRow    = $lastRow + 2;
                $totalHari     = array_sum(array_column($this->rekap, 'jumlah_hari'));
                $totalMenit    = array_sum(array_column($this->rekap, 'total_menit'));
                $totalPoin     = array_sum(array_column($this->rekap, 'poin_diperoleh'));
                $totalJam      = intdiv($totalMenit, 60) . 'j ' . ($totalMenit % 60) . 'm';

                $sheet->setCellValue("A{$summaryRow}", 'RINGKASAN');
                $sheet->setCellValue("B{$summaryRow}", 'Total Hari Lembur: ' . $totalHari . ' hari');
                $sheet->setCellValue("C{$summaryRow}", 'Total Waktu: ' . $totalMenit . ' menit');
                $sheet->setCellValue("D{$summaryRow}", 'Total Durasi: ' . $totalJam);
                $sheet->setCellValue("E{$summaryRow}", 'Total Poin: ' . $totalPoin . ' poin');

                $sheet->getStyle("A{$summaryRow}:E{$summaryRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '1E293B']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                    ],
                ]);

                $noteRow = $summaryRow + 2;
                $sheet->setCellValue("A{$noteRow}", '* Laporan dibuat otomatis oleh sistem MPG HRIS. Data bersumber dari catatan lembur yang telah disetujui.');
                $sheet->getStyle("A{$noteRow}")->getFont()->setItalic(true)->setSize(8)->getColor()->setRGB('94A3B8');
                $sheet->mergeCells("A{$noteRow}:I{$noteRow}");
            },
        ];
    }
}
