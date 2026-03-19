<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanLemburExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $pegawai;
    protected $rekap;
    protected $bulan;
    protected $tahun;

    public function __construct($pegawai, array $rekap, $bulan, $tahun)
    {
        $this->pegawai = $pegawai;
        $this->rekap = $rekap;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function collection()
    {
        return collect($this->pegawai);
    }

    public function headings(): array
    {
        return [
            ['LAPORAN REKAPITULASI LEMBUR PEGAWAI'],
            ['Periode: ' . \Carbon\Carbon::create()->month($this->bulan)->translatedFormat('F') . ' ' . $this->tahun],
            [],
            [
                'No',
                'NIK',
                'Nama Lengkap',
                'Divisi / Dept',
                'Jabatan',
                'Jumlah Hari Lembur',
                'Total Waktu (Menit)',
                'Durasi Jam',
                'Poin Lembur Diperoleh'
            ]
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $dataRekap = $this->rekap[$row->id] ?? [
            'total_menit' => 0,
            'format_jam' => '0j 0m',
            'jumlah_hari' => 0,
            'poin_diperoleh' => 0,
        ];

        return [
            $no,
            $row->nik ?? '-',
            $row->nama_lengkap,
            $row->divisi->nama_divisi ?? '-',
            $row->jabatan->nama_jabatan ?? '-',
            $dataRekap['jumlah_hari'] . ' Hari',
            $dataRekap['total_menit'] . ' Menit',
            $dataRekap['format_jam'],
            $dataRekap['poin_diperoleh'] . ' Poin'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [

            1    => ['font' => ['bold' => true, 'size' => 14]],
            2    => ['font' => ['italic' => true]],
            4    => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E6F4EA']]],
        ];
    }
}
