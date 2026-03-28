<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Rekapitulasi Presensi</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1e293b;
            background: #fff;
        }

        /* ── KOP SURAT ── */
        .kop {
            display: table;
            width: 100%;
            border-bottom: 3px solid #1e293b;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .kop-left  { display: table-cell; width: 70%; vertical-align: middle; }
        .kop-right { display: table-cell; width: 30%; vertical-align: middle; text-align: right; }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #1e293b;
            letter-spacing: 0.5px;
        }
        .company-sub {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }
        .doc-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-no {
            font-size: 11px;
            font-weight: bold;
            color: #1e293b;
        }

        /* ── JUDUL DOKUMEN ── */
        .doc-title {
            text-align: center;
            margin-bottom: 14px;
        }
        .doc-title h1 {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1e293b;
        }
        .doc-title .periode-badge {
            display: inline-block;
            margin-top: 5px;
            padding: 3px 12px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 10px;
            color: #475569;
        }

        /* ── INFO DOKUMEN ── */
        .meta-table {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
            font-size: 9.5px;
        }
        .meta-table td { padding: 2px 4px; color: #475569; }
        .meta-table .label { width: 28%; color: #94a3b8; }
        .meta-table .sep   { width: 2%; }
        .meta-table .value { font-weight: 600; color: #1e293b; }

        /* ── TABEL DATA ── */
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9.5px;
        }
        table.data thead tr {
            background-color: #1e293b;
            color: #fff;
        }
        table.data thead th {
            padding: 7px 6px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #334155;
            white-space: nowrap;
        }
        table.data thead th.left { text-align: left; }
        table.data tbody tr:nth-child(even)  { background: #f8fafc; }
        table.data tbody tr:nth-child(odd)   { background: #ffffff; }
        table.data tbody td {
            padding: 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        table.data tbody td.center { text-align: center; }
        table.data tbody td.name   { font-weight: 600; }
        table.data tbody td.sub    { font-size: 9px; color: #64748b; }

        /* Status badge */
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 600;
        }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-blue   { background: #dbeafe; color: #1e40af; }
        .badge-orange { background: #ffedd5; color: #9a3412; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-purple { background: #f3e8ff; color: #6b21a8; }
        .badge-gray   { background: #f1f5f9; color: #475569; }

        /* ── RINGKASAN ── */
        .summary-box {
            width: 100%;
            display: table;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        .summary-item {
            display: table-cell;
            width: 16.66%;
            padding: 8px 10px;
            text-align: center;
            border-right: 1px solid #e2e8f0;
        }
        .summary-item:last-child { border-right: none; }
        .summary-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            margin-bottom: 3px;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
        }
        .summary-value.green  { color: #16a34a; }
        .summary-value.blue   { color: #2563eb; }
        .summary-value.orange { color: #ea580c; }
        .summary-value.red    { color: #dc2626; }
        .summary-value.purple { color: #9333ea; }

        /* ── FOOTER / TANDA TANGAN ── */
        .footer-area {
            margin-top: 30px;
            display: table;
            width: 100%;
        }
        .footer-left  { display: table-cell; width: 50%; vertical-align: top; font-size: 9px; color: #64748b; }
        .footer-right { display: table-cell; width: 50%; text-align: right; vertical-align: top; }
        .ttd-box {
            display: inline-block;
            text-align: center;
            font-size: 9.5px;
        }
        .ttd-box .city-date { color: #475569; margin-bottom: 48px; }
        .ttd-box .ttd-name  { font-weight: bold; border-top: 1px solid #1e293b; padding-top: 4px; }
        .ttd-box .ttd-title { color: #64748b; }

        .page-number { text-align: center; font-size: 8px; color: #94a3b8; margin-top: 12px; }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <div class="kop">
        <div class="kop-left">
            <div class="company-name">MPG HRIS Enterprise System</div>
            <div class="company-sub">Human Resource Information System</div>
        </div>
        <div class="kop-right">
            <div class="doc-label">Nomor Dokumen</div>
            <div class="doc-no">LAP-{{ str_pad($bulan, 2, '0', STR_PAD_LEFT) }}/{{ $tahun }}/PRES</div>
        </div>
    </div>

    {{-- JUDUL --}}
    <div class="doc-title">
        <h1>Laporan Rekapitulasi Presensi Pegawai</h1>
        <span class="periode-badge">
            Periode: {{ \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F') }} {{ $tahun }}
        </span>
    </div>

    {{-- META INFO --}}
    <table class="meta-table">
        <tr>
            <td class="label">Tanggal Cetak</td>
            <td class="sep">:</td>
            <td class="value">{{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</td>
            <td width="30%"></td>
            <td class="label">Total Pegawai</td>
            <td class="sep">:</td>
            <td class="value">{{ count($rekap) }} Orang</td>
        </tr>
        <tr>
            <td class="label">Dicetak Oleh</td>
            <td class="sep">:</td>
            <td class="value">HRD Department</td>
            <td></td>
            <td class="label">Status Dokumen</td>
            <td class="sep">:</td>
            <td class="value">Resmi / Official</td>
        </tr>
    </table>

    {{-- RINGKASAN STATISTIK --}}
    @php
        $totalHadir    = collect($rekap)->sum('hadir');
        $totalIzin     = collect($rekap)->sum('izin');
        $totalSakit    = collect($rekap)->sum('sakit');
        $totalAlpha    = collect($rekap)->sum('alpha');
        $totalTerlambat= collect($rekap)->sum('terlambat');
        $totalPoin     = collect($rekap)->sum('poin_lembur');
    @endphp
    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-label">Total Hadir</div>
            <div class="summary-value green">{{ $totalHadir }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Izin</div>
            <div class="summary-value blue">{{ $totalIzin }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Sakit</div>
            <div class="summary-value orange">{{ $totalSakit }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Alpha</div>
            <div class="summary-value red">{{ $totalAlpha }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Terlambat</div>
            <div class="summary-value orange">{{ $totalTerlambat }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Poin Lembur</div>
            <div class="summary-value purple">{{ $totalPoin }}</div>
        </div>
    </div>

    {{-- TABEL DATA --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th class="left" style="width:11%">NIK / ID</th>
                <th class="left" style="width:20%">Nama Lengkap</th>
                <th class="left" style="width:13%">Divisi</th>
                <th class="left" style="width:12%">Jabatan</th>
                <th style="width:7%">Hadir</th>
                <th style="width:7%">Izin</th>
                <th style="width:7%">Sakit</th>
                <th style="width:6%">Alpha</th>
                <th style="width:7%">Terlambat</th>
                <th style="width:6%">Poin</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekap as $index => $row)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $row['user']->nik ?? '-' }}</td>
                <td class="name">{{ $row['user']->nama_lengkap }}</td>
                <td>{{ $row['user']->divisi->nama_divisi ?? '-' }}</td>
                <td>{{ $row['user']->jabatan->nama_jabatan ?? '-' }}</td>
                <td class="center">
                    <span class="badge badge-green">{{ $row['hadir'] }}</span>
                </td>
                <td class="center">
                    @if($row['izin'] > 0)
                        <span class="badge badge-blue">{{ $row['izin'] }}</span>
                    @else
                        <span style="color:#cbd5e1">—</span>
                    @endif
                </td>
                <td class="center">
                    @if($row['sakit'] > 0)
                        <span class="badge badge-orange">{{ $row['sakit'] }}</span>
                    @else
                        <span style="color:#cbd5e1">—</span>
                    @endif
                </td>
                <td class="center">
                    @if($row['alpha'] > 0)
                        <span class="badge badge-red">{{ $row['alpha'] }}</span>
                    @else
                        <span style="color:#cbd5e1">—</span>
                    @endif
                </td>
                <td class="center">
                    @if($row['terlambat'] > 0)
                        <span class="badge badge-orange">{{ $row['terlambat'] }}x</span>
                    @else
                        <span style="color:#cbd5e1">—</span>
                    @endif
                </td>
                <td class="center">
                    @if($row['poin_lembur'] > 0)
                        <span class="badge badge-purple">{{ $row['poin_lembur'] }}</span>
                    @else
                        <span style="color:#cbd5e1">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer-area">
        <div class="footer-left">
            <p>* Laporan ini dibuat secara otomatis oleh sistem MPG HRIS.</p>
            <p>* Data bersumber dari catatan presensi dan lembur bulan berjalan.</p>
        </div>
        <div class="footer-right">
            <div class="ttd-box">
                <div class="city-date">Jakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                <div class="ttd-name">HR Department</div>
                <div class="ttd-title">Human Resources</div>
            </div>
        </div>
    </div>

    <div class="page-number"></div>

    <script type="text/php">
        if (isset($pdf)) {
            $w = $pdf->get_width();
            $h = $pdf->get_height();
            $font = $fontMetrics->get_font("DejaVu Sans, sans-serif", "normal");
            $pdf->page_text($w / 2 - 40, $h - 20, "— Halaman {PAGE_NUM} dari {PAGE_COUNT} —", $font, 7, [0.58, 0.64, 0.71]);
        }
    </script>

</body>
</html>
