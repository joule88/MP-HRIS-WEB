<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Presensi Pegawai</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; padding: 0; font-size: 16px; }
        .header p { margin: 5px 0 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: right; }
        .signature { margin-top: 50px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>LAPORAN REKAPITULASI PRESENSI PEGAWAI</h2>
        <h2>MPG HRIS ENTERPRISE SYSTEM</h2>
        <p>Periode: {{ \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F') }} {{ $tahun }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="12%">NIK</th>
                <th width="25%">Nama Lengkap</th>
                <th width="15%">Hadir</th>
                <th width="12%">Izin/Cuti</th>
                <th width="12%">Sakit</th>
                <th width="10%">Alpha</th>
                <th width="9%">Telat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekap as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row['user']->nik ?? '-' }}</td>
                <td>{{ $row['user']->nama_lengkap }}</td>
                <td class="text-center">{{ $row['hadir'] }} x</td>
                <td class="text-center">{{ $row['izin'] }} x</td>
                <td class="text-center">{{ $row['sakit'] }} x</td>
                <td class="text-center">{{ $row['alpha'] }} x</td>
                <td class="text-center">{{ $row['terlambat'] }} x</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
        <div class="signature">
            <p>( _______________________ )</p>
            <p><strong>HR Department</strong></p>
        </div>
    </div>

</body>
</html>
