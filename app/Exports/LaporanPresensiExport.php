<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPresensiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $rekap;
    protected $bulan;
    protected $tahun;

    public function __construct(array $rekap, $bulan, $tahun)
    {
        $this->rekap = $rekap;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function collection()
    {
        return collect($this->rekap);
    }

    public function headings(): array
    {
        return [
            ['LAPORAN REKAPITULASI PRESENSI PEGAWAI'],
            ['Periode: ' . \Carbon\Carbon::createFromDate($this->tahun, (int) $this->bulan, 1)->translatedFormat('F') . ' ' . $this->tahun],
            [],
            [
                'No',
                'NIK',
                'Nama Lengkap',
                'Jabatan',
                'Divisi',
                'Hadir',
                'Izin / Cuti',
                'Sakit',
                'Alpha (Mangkir)',
                'Terlambat',
                'Poin Lembur Diperoleh'
            ]
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
            $row['hadir'] . ' Hari',
            $row['izin'] . ' Hari',
            $row['sakit'] . ' Hari',
            $row['alpha'] . ' Hari',
            $row['terlambat'] . ' Kali',
            $row['poin_lembur'] . ' Poin'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [

            1    => ['font' => ['bold' => true, 'size' => 14]],
            2    => ['font' => ['italic' => true]],
            4    => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E2E8F0']]],
        ];
    }
}
