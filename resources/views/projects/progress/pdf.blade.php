<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Harian - {{ $project->name }} - {{ $report->report_date->format('Y-m-d') }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #333;
        }
        * {
            box-sizing: border-box;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border: 2px solid #000;
        }
        .header-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
        }
        .logo-cell {
            width: 20%;
            text-align: center;
        }
        .title-cell {
            text-align: center;
            font-weight: bold;
        }
        .info-cell {
            width: 30%;
            font-size: 8pt;
        }
        
        .main-title { font-size: 14pt; margin-bottom: 5px; text-transform: uppercase; }
        .sub-title { font-size: 11pt; }

        .section-title {
            background-color: #f0f0f0;
            font-weight: bold;
            padding: 4px;
            border: 1px solid #000;
            border-bottom: none;
            text-transform: uppercase;
            font-size: 9pt;
            margin-top: 10px;
        }
        
        table.content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 1px solid #000;
        }
        table.content-table th, table.content-table td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 8pt;
        }
        table.content-table th {
            background-color: #f8f9fa;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .grid-2 {
            width: 100%;
            display: table;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 10px;
        }
        .grid-2-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            border: 1px solid #000;
        }
        
        .grid-3 {
            width: 100%;
            display: table;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 10px;
        }
        .grid-3-col {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            border: 1px solid #000;
        }

        .box-title {
            background-color: #f8f9fa;
            border-bottom: 1px solid #000;
            padding: 3px;
            font-weight: bold;
            text-align: center;
            font-size: 8pt;
        }
        .box-content {
            padding: 5px;
            min-height: 40px;
            font-size: 8pt;
        }

        .weather-box {
            display: inline-block;
            width: 20px;
            height: 12px;
            border: 1px solid #333;
            margin-right: 5px;
            vertical-align: middle;
        }
        .weather-filled { background-color: #555; }
        .weather-empty { background-color: #fff; }

        /* Signatures */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #000;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: 1px solid #000;
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 5px;
            font-size: 8pt;
        }
        .signature-space {
            height: 60px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
        
        /* Documentation */
        .photo-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .photo-cell {
            width: 50%;
            padding: 5px;
            text-align: center;
            vertical-align: top;
        }
        .photo-img {
            width: 100%;
            max-height: 200px;
            object-fit: contain;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="logo-cell" rowspan="2">
                <!-- Logo Placeholder -->
                <div style="font-weight:bold; font-size:16pt; color:#555;">PROMAN5</div>
                <div style="font-size:7pt;">Construction ERP</div>
            </td>
            <td class="title-cell" rowspan="2">
                <div class="main-title">LAPORAN HARIAN PROYEK</div>
                <div class="sub-title">(DAILY PROGRESS REPORT)</div>
            </td>
            <td class="info-cell">
                <table style="width:100%; border:none; padding:0;">
                    <tr><td style="border:none; padding:1px; width:40%;">No. Dokumen</td><td style="border:none; padding:1px;">: {{ $report->report_code }}</td></tr>
                    <tr><td style="border:none; padding:1px;">Tanggal</td><td style="border:none; padding:1px;">: {{ $report->report_date->format('d M Y') }}</td></tr>
                    <tr><td style="border:none; padding:1px;">Hari Ke</td><td style="border:none; padding:1px;">: {{ $project->start_date ? $project->start_date->diffInDays($report->report_date) + 1 : '-' }}</td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="info-cell">
                <table style="width:100%; border:none; padding:0;">
                    <tr><td style="border:none; padding:1px; width:40%;">Proyek</td><td style="border:none; padding:1px;">: {{ $project->code }}</td></tr>
                    <tr><td style="border:none; padding:1px;">Status</td><td style="border:none; padding:1px;">: {{ strtoupper($report->status) }}</td></tr>
                    <tr><td style="border:none; padding:1px;">Pelapor</td><td style="border:none; padding:1px;">: {{ $report->reporter->name ?? '-' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="grid-2">
        <div class="grid-2-col">
            <div class="box-title">NAMA & LOKASI PROYEK</div>
            <div class="box-content">
                <strong>{{ $project->name }}</strong><br>
                {{ $project->location ?? '-' }}
            </div>
        </div>
        <div class="grid-2-col">
            <div class="box-title">KONDISI CUACA</div>
            <div class="box-content">
                <table style="width:100%; font-size:8pt; border:none;">
                    <tr>
                        <td style="border:none; padding:2px; width:30%;">Kondisi Mayor</td>
                        <td style="border:none; padding:2px;">: <strong>{{ $report->weather_label }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- PEKERJAAN YANG DILAKUKAN -->
    <div class="section-title">1. URAIAN PEKERJAAN</div>
    <table class="content-table" style="margin-top:0;">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Item Pekerjaan (RAB)</th>
                <th style="width: 50%;">Deskripsi Pelaksanaan</th>
                <th style="width: 10%;">Progres Harian</th>
                <th style="width: 10%;">Kumulatif</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>{{ $report->rabItem ? $report->rabItem->code . ' - ' . $report->rabItem->work_name : 'UMUM / PERSIAPAN' }}</td>
                <td>{!! nl2br(e($report->description ?? '-')) !!}</td>
                <td class="text-center font-bold">{{ number_format($report->progress_percentage, 2) }}%</td>
                <td class="text-center">{{ $report->cumulative_progress ? number_format($report->cumulative_progress, 2) . '%' : '-' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- RESOURCES: TENAGA KERJA, ALAT, MATERIAL -->
    <div class="grid-3">
        <!-- TENAGA KERJA -->
        <div class="grid-3-col">
            <div class="box-title">2. TENAGA KERJA</div>
            <table class="content-table" style="margin-bottom:0; border:none; border-top:1px solid #000;">
                <thead>
                    <tr>
                        <th style="border-left:none; border-right:1px solid #000;">Kualifikasi</th>
                        <th style="border-left:none; border-right:none; width:30%;">Jml</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $labors = $report->labor_details ?? []; 
                        $totalLabor = 0;
                    @endphp
                    @forelse($labors as $type => $qty)
                        @if($qty > 0)
                        <tr>
                            <td style="border-left:none; border-right:1px solid #000;">{{ $type }}</td>
                            <td class="text-center" style="border-left:none; border-right:none;">{{ $qty }}</td>
                        </tr>
                        @php $totalLabor += $qty; @endphp
                        @endif
                    @empty
                        <tr>
                            <td colspan="2" class="text-center" style="border-left:none; border-right:none;">Nihil</td>
                        </tr>
                    @endforelse
                    <tr>
                        <td class="text-right font-bold" style="border-left:none; border-right:1px solid #000;">TOTAL</td>
                        <td class="text-center font-bold" style="border-left:none; border-right:none;">{{ $totalLabor }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PERALATAN -->
        <div class="grid-3-col">
            <div class="box-title">3. PERALATAN</div>
            <table class="content-table" style="margin-bottom:0; border:none; border-top:1px solid #000;">
                <thead>
                    <tr>
                        <th style="border-left:none; border-right:1px solid #000;">Jenis Alat</th>
                        <th style="border-left:none; border-right:none; width:30%;">Jml</th>
                    </tr>
                </thead>
                <tbody>
                    @php $equipments = $report->equipment_details ?? []; @endphp
                    @forelse($equipments as $type => $qty)
                        @if($qty > 0)
                        <tr>
                            <td style="border-left:none; border-right:1px solid #000;">{{ $type }}</td>
                            <td class="text-center" style="border-left:none; border-right:none;">{{ $qty }}</td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="2" class="text-center" style="border-left:none; border-right:none;">Nihil</td>
                        </tr>
                        <tr><td colspan="2" style="border:none;">&nbsp;</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- MATERIAL -->
        <div class="grid-3-col">
            <div class="box-title">4. MATERIAL DITERIMA/DIGUNAKAN</div>
            <table class="content-table" style="margin-bottom:0; border:none; border-top:1px solid #000;">
                <thead>
                    <tr>
                        <th style="border-left:none; border-right:1px solid #000;">Jenis Material</th>
                        <th style="border-left:none; border-right:none; width:30%;">Vol</th>
                    </tr>
                </thead>
                <tbody>
                    @php $materials = $report->material_details ?? []; @endphp
                    @forelse($materials as $type => $qty)
                        @if($qty > 0)
                        <tr>
                            <td style="border-left:none; border-right:1px solid #000;">{{ $type }}</td>
                            <td class="text-center" style="border-left:none; border-right:none;">{{ $qty }}</td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="2" class="text-center" style="border-left:none; border-right:none;">Nihil</td>
                        </tr>
                        <tr><td colspan="2" style="border:none;">&nbsp;</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid-2">
        <div class="grid-2-col">
            <div class="box-title">5. KESELAMATAN KERJA (K3)</div>
            <div class="box-content">
                @php $safety = is_array($report->safety_details) ? $report->safety_details : []; @endphp
                @if(!empty($safety['incidents']) || !empty($safety['near_misses']) || !empty($safety['notes']))
                    <div><strong>Insiden:</strong> {{ $safety['incidents'] ?? 0 }} kejadian</div>
                    <div><strong>Near Miss:</strong> {{ $safety['near_misses'] ?? 0 }} kejadian</div>
                    @if(!empty($safety['notes']))
                        <div style="margin-top:3px;"><strong>Catatan:</strong><br>{{ $safety['notes'] }}</div>
                    @endif
                @else
                    Aman, tidak ada insiden.
                @endif
            </div>
        </div>
        <div class="grid-2-col">
            <div class="box-title">6. HAMBATAN / KENDALA</div>
            <div class="box-content text-center" style="color: {{ $report->issues ? '#000' : '#888' }}">
                {!! $report->issues ? nl2br(e($report->issues)) : 'Nihil / Tidak Ada Kendala' !!}
            </div>
        </div>
    </div>

    <!-- SIGNATURES -->
    <table class="signature-table">
        <tr>
            <td>
                Dibuat Oleh,<br>
                <strong>Kontraktor Pelaksana</strong>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $report->reporter->name ?? 'Pelaksana' }}</div>
                <div>Site Engineer / Supervisor</div>
            </td>
            <td>
                Diperiksa Oleh,<br>
                <strong>Manajemen Proyek</strong>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $report->reviewer->name ?? '....................................' }}</div>
                <div>Project Manager</div>
            </td>
            <td>
                Disetujui Oleh,<br>
                <strong>Konsultan Pengawas / Owner</strong>
                <div class="signature-space"></div>
                <div class="signature-name">....................................</div>
                <div>Pengawas Lapangan</div>
            </td>
        </tr>
    </table>

    <!-- DOKUMENTASI -->
    @if($report->photo_urls && count($report->photo_urls) > 0)
    <div style="page-break-before: always;">
        <div class="section-title" style="border-bottom: 1px solid #000;">7. DOKUMENTASI LAPANGAN</div>
        <table class="photo-grid">
            @foreach(array_chunk($report->photo_urls, 2) as $chunk)
            <tr>
                @foreach($chunk as $url)
                <td class="photo-cell">
                    <img src="{{ $url }}" class="photo-img">
                </td>
                @endforeach
                @if(count($chunk) == 1)
                <td class="photo-cell"></td>
                @endif
            </tr>
            @endforeach
        </table>
    </div>
    @endif

</body>
</html>
