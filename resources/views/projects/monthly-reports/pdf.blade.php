<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report - {{ $project->name }} - {{ \Carbon\Carbon::createFromDate($report->year, $report->month, 1)->translatedFormat('F Y') }}</title>
    <style>
        /* -----------------------------------------------------------
           Page Setup & Reset
           ----------------------------------------------------------- */
        @page {
            size: A4;
            /* Margin: Top, Right, Bottom, Left (lebih lebar untuk jilid) */
            margin: 25mm 20mm 30mm 30mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #334155; /* Slate 700 */
            background: #fff;
        }

        .page-break {
            page-break-after: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

        /* -----------------------------------------------------------
           Running Header & Footer (DomPDF Support)
           ----------------------------------------------------------- */
        header {
            position: fixed;
            top: -15mm;
            left: 0;
            right: 0;
            height: 10mm;
            border-bottom: 1px solid #e2e8f0;
            font-size: 8pt;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }

        .header-left { float: left; }
        .header-right { float: right; font-weight: bold; color: #64748b; }

        footer {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            height: 10mm;
            border-top: 1px solid #e2e8f0;
            padding-top: 5pt;
            font-size: 8pt;
            color: #94a3b8;
        }

        .footer-left { float: left; }
        .footer-right { float: right; }
        .page-number:after { content: counter(page); }

        /* -----------------------------------------------------------
           Cover Page Styling
           ----------------------------------------------------------- */
        .cover-wrap {
            text-align: center;
            height: 100%;
            position: relative;
            padding-top: 40pt;
        }

        .cover-title {
            font-size: 24pt;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 10pt;
            text-transform: uppercase;
        }

        .cover-subtitle {
            font-size: 14pt;
            color: #475569;
            margin-bottom: 40pt;
        }

        .week-badge {
            display: inline-block;
            background: #2563eb;
            color: #ffffff;
            padding: 10pt 30pt;
            font-size: 32pt;
            font-weight: 900;
            border-radius: 4pt;
            margin-bottom: 20pt;
        }

        .period-box {
            font-size: 12pt;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 50pt;
        }

        .cover-img-wrap {
            width: 100%;
            height: 250pt;
            overflow: hidden;
            border: 5pt solid #f1f5f9;
            border-radius: 8pt;
            margin-bottom: 50pt;
        }

        .cover-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cover-details-table {
            width: 90%;
            margin: 0 auto;
            text-align: left;
            border-top: 2pt solid #2563eb;
            background: #f8fafc;
            padding: 15pt;
        }

        .cover-details-table td {
            padding: 4pt 8pt;
            font-size: 11pt;
            vertical-align: top;
        }

        .label-cell {
            width: 30%;
            font-weight: bold;
            color: #475569;
        }

        .exec-summary-box {
            margin-top: 20pt;
            border: 2pt solid #1e293b;
            padding: 10pt;
            background: #fff;
        }

        .exec-summary-title {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid #1e293b;
            padding-bottom: 5pt;
            margin-bottom: 10pt;
            text-transform: uppercase;
        }

        .exec-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .exec-col {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            border-right: 1px solid #cbd5e1;
            padding: 5pt;
        }

        .exec-col:last-child {
            border-right: none;
        }

        .exec-value {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 2pt;
        }

        .exec-label {
            font-size: 8pt;
            color: #64748b;
            text-transform: uppercase;
        }

        /* -----------------------------------------------------------
           Section Content Styling
           ----------------------------------------------------------- */
        .section-header {
            background-color: #1e293b;
            color: #f8fafc;
            padding: 6pt 12pt;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 15pt;
            margin-top: 10pt;
            border-radius: 2pt;
        }

        .sub-section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 8pt;
            padding-bottom: 2pt;
            border-bottom: 1px solid #cbd5e1;
        }

        /* -----------------------------------------------------------
           Tables Styling
           ----------------------------------------------------------- */
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20pt;
            font-size: 8pt;
            table-layout: fixed;
        }

        table.main-table th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #94a3b8;
            padding: 4pt 2pt;
            text-align: center;
        }

        table.main-table td {
            border: 1px solid #cbd5e1;
            padding: 4pt;
            vertical-align: middle;
        }

        /* Alternating row colors for readability */
        table.main-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .col-plan { background-color: #eff6ff; }
        .col-actual { background-color: #f0fdf4; }
        .col-dev { background-color: #fefce8; }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }

        .status-pos { color: #15803d; font-weight: bold; }
        .status-neg { color: #b91c1c; font-weight: bold; }

        /* -----------------------------------------------------------
           Documentation Grid
           ----------------------------------------------------------- */
        .doc-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10pt;
            margin-bottom: 20pt;
        }

        .doc-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            padding: 5pt;
            text-align: center;
            width: 25%;
        }

        .doc-card img {
            width: 100%;
            height: 120pt;
            object-fit: cover;
            border-radius: 2pt;
            margin-bottom: 5pt;
        }

        .doc-card p {
            font-size: 7pt;
            color: #64748b;
            line-height: 1.2;
        }

        /* -----------------------------------------------------------
           Two Column Grid (Activities / Problem)
           ----------------------------------------------------------- */
        .grid-row {
            width: 100%;
            display: table;
            table-layout: fixed;
            border-spacing: 15pt 0;
            margin-bottom: 20pt;
        }

        .grid-col {
            display: table-cell;
            vertical-align: top;
            border: 1px solid #e2e8f0;
            background: #fdfdfd;
            border-radius: 4pt;
        }

        .grid-col-header {
            background: #f1f5f9;
            padding: 6pt 10pt;
            font-weight: bold;
            font-size: 10pt;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
        }

        .grid-col-body {
            padding: 10pt;
            font-size: 9pt;
            min-height: 100pt;
            color: #334155;
        }

        /* -----------------------------------------------------------
           Signature Block
           ----------------------------------------------------------- */
        .signature-section {
            margin-top: 40pt;
            width: 100%;
            display: table;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .signature-col {
            display: table-cell;
            text-align: center;
            padding: 10pt;
        }

        .signature-label {
            font-size: 10pt;
            margin-bottom: 50pt;
            color: #475569;
        }

        .signature-name {
            font-weight: bold;
            font-size: 10pt;
            text-decoration: underline;
            color: #0f172a;
        }

        .signature-role {
            font-size: 9pt;
            color: #64748b;
        }
    </style>
</head>

<body>
    <!-- Running Header & Footer -->
    <header>
        <div class="header-left">PROMAN-5 System | {{ $project->code }}</div>
        <div class="header-right">MONTHLY REPORT - {{ strtoupper(\Carbon\Carbon::createFromDate($report->year, $report->month, 1)->translatedFormat('F Y')) }}</div>
    </header>

    <footer>
        <div class="footer-left">Generated by Antigravity AI &bull; {{ now()->format('d M Y, H:i') }}</div>
        <div class="footer-right">Halaman <span class="page-number"></span></div>
    </footer>

    <!-- 1. COVER PAGE -->
    <div class="cover-wrap">
        <div class="cover-title">{{ $report->cover_title ?? 'Project Progress Report' }}</div>
        <div class="cover-subtitle">Laporan Bulanan Pelaksanaan Pekerjaan</div>

        <div class="week-badge">BULAN {{ strtoupper(\Carbon\Carbon::createFromDate($report->year, $report->month, 1)->translatedFormat('F Y')) }}</div>

        <div class="period-box">
            PERIODE: {{ $report->period_start->format('d F Y') }} – {{ $report->period_end->format('d F Y') }}
        </div>

        @if($report->cover_image_url)
        <div class="cover-img-wrap">
            <img src="{{ $report->cover_image_url }}" alt="Project Documentation">
        </div>
        @else
        <div style="height: 100pt;"></div>
        @endif

        <table class="cover-details-table">
            <tr>
                <td class="label-cell">Nama Proyek</td>
                <td>: {{ $project->name }}</td>
            </tr>
            <tr>
                <td class="label-cell">Lokasi Pekerjaan</td>
                <td>: {{ $project->location ?? 'Tidak Terdefinisi' }}</td>
            </tr>
            <tr>
                <td class="label-cell">Nama Klien / Owner</td>
                <td>: {{ $project->client_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-cell">Kontrak No.</td>
                <td>: {{ $project->contract_number ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-cell">Masa Pelaksanaan</td>
                <td>: {{ $project->start_date ? $project->start_date->format('d/m/Y') : '-' }} s/d {{ $project->end_date ? $project->end_date->format('d/m/Y') : '-' }}</td>
            </tr>
        </table>

        <!-- Executive Summary -->
        <div class="exec-summary-box">
            <div class="exec-summary-title">Executive Summary</div>
            <div class="exec-grid">
                @php
                    $totals = $report->cumulative_data['totals'] ?? null;
                    $dev = $totals['deviation_cumulative'] ?? 0;
                    $devColor = $dev >= 0 ? '#15803d' : '#b91c1c';
                    $devLabel = $dev >= 0 ? 'Ahead of Schedule' : 'Behind Schedule';
                    $devVal = abs($dev);

                    $weatherDays = 0;
                    if($report->detail_data) {
                        $weatherDays = collect($report->detail_data)->filter(fn($d) => stripos($d['weather'] ?? '', 'Hujan') !== false)->count();
                    }
                @endphp
                <div class="exec-col">
                    <div class="exec-value">{{ number_format($totals['actual_cumulative'] ?? 0, 2) }}%</div>
                    <div class="exec-label">Actual Progress</div>
                </div>
                <div class="exec-col">
                    <div class="exec-value" style="color: {{ $devColor }}">{{ $dev >= 0 ? '+' : '-' }}{{ number_format($devVal, 2) }}%</div>
                    <div class="exec-label">{{ $devLabel }}</div>
                </div>
                <div class="exec-col">
                    <div class="exec-value">{{ $weatherDays }} Hari</div>
                    <div class="exec-label">Curah Hujan</div>
                </div>
            </div>
        </div>

    </div>

    <div class="page-break"></div>

    <!-- 2. CUMULATIVE PROGRESS -->
    <div class="section-header">SECTION 01: CUMULATIVE PROGRESS</div>
    
    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 35%;">Uraian Pekerjaan</th>
                <th rowspan="2" style="width: 5%;">Weight (%)</th>
                <th colspan="3" class="col-plan">Planned (%)</th>
                <th colspan="3" class="col-actual">Actual (%)</th>
                <th colspan="3" class="col-dev">Deviation (%)</th>
            </tr>
            <tr>
                <th class="col-plan">Prev</th>
                <th class="col-plan">Week</th>
                <th class="col-plan">Total</th>
                <th class="col-actual">Prev</th>
                <th class="col-actual">Week</th>
                <th class="col-actual">Total</th>
                <th class="col-dev">Prev</th>
                <th class="col-dev">Week</th>
                <th class="col-dev">Total</th>
            </tr>
        </thead>
        <tbody>
            @if($report->cumulative_data && isset($report->cumulative_data['sections']))
                @foreach($report->cumulative_data['sections'] as $section)
                    @include('projects.monthly-reports.partials.pdf-cumulative-section', ['section' => $section, 'level' => 0])
                @endforeach

                @if(isset($report->cumulative_data['totals']))
                @php $totals = $report->cumulative_data['totals']; @endphp
                <tr class="bold" style="background: #e2e8f0; font-size: 9pt;">
                    <td class="text-right">GRAND TOTAL</td>
                    <td class="text-center">{{ number_format($totals['weight'] ?? 0, 2) }}</td>
                    <td class="text-center">{{ number_format($totals['planned_prev'] ?? 0, 4) }}</td>
                    <td class="text-center">{{ number_format($totals['planned_current'] ?? 0, 4) }}</td>
                    <td class="text-center">{{ number_format($totals['planned_cumulative'] ?? 0, 4) }}</td>
                    <td class="text-center">{{ number_format($totals['actual_prev'] ?? 0, 4) }}</td>
                    <td class="text-center">{{ number_format($totals['actual_current'] ?? 0, 4) }}</td>
                    <td class="text-center">{{ number_format($totals['actual_cumulative'] ?? 0, 4) }}</td>
                    <td class="text-center {{ ($totals['deviation_prev'] ?? 0) >= 0 ? 'status-pos' : 'status-neg' }}">{{ number_format($totals['deviation_prev'] ?? 0, 4) }}</td>
                    <td class="text-center {{ ($totals['deviation_current'] ?? 0) >= 0 ? 'status-pos' : 'status-neg' }}">{{ number_format($totals['deviation_current'] ?? 0, 4) }}</td>
                    <td class="text-center {{ ($totals['deviation_cumulative'] ?? 0) >= 0 ? 'status-pos' : 'status-neg' }}">{{ number_format($totals['deviation_cumulative'] ?? 0, 4) }}</td>
                </tr>
                @endif
            @else
                <tr><td colspan="11" class="text-center">Data tidak tersedia.</td></tr>
            @endif
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- 3. DETAIL PROGRESS -->
    <div class="section-header">SECTION 02: DETAIL DAILY REPORT LOGS</div>
    
    @if($report->detail_data && count($report->detail_data) > 0)
        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 25%;">Work Item</th>
                    <th style="width: 7%;">Prog.</th>
                    <th style="width: 35%;">Remark / Description</th>
                    <th style="width: 10%;">Weather</th>
                    <th style="width: 11%;">Reporter</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report->detail_data as $detail)
                <tr class="no-break">
                    <td class="text-center">{{ $detail['date_label'] }}</td>
                    <td>{{ $detail['rab_item'] ? $detail['rab_item']['code'] . ' - ' . ($detail['rab_item']['work_name'] ?? $detail['rab_item']['name'] ?? '-') : '-' }}</td>
                    <td class="text-center bold status-pos">{{ $detail['progress_percentage'] }}%</td>
                    <td>{{ $detail['description'] ?? '-' }}</td>
                    <td class="text-center">{{ $detail['weather'] ?? '-' }} <br> <span style="font-size: 7pt; color: #64748b;">(W: {{ $detail['workers_count'] ?? '0' }})</span></td>
                    <td class="text-center">{{ $detail['reporter'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-center" style="padding: 50pt; color: #94a3b8;">Empty Logs.</p>
    @endif

    <!-- 4. ACTIVITY & PROBLEM LOGS -->
    <div class="no-break">
        <div class="sub-section-title">SECTION 03: ACTIVITY & PROBLEM SUMMARIES</div>
        <div class="grid-row">
            <div class="grid-col">
                <div class="grid-col-header">Significant Activities This Month</div>
                <div class="grid-col-body">
                    {!! nl2br(e($report->activities ?? 'No activities reported.')) !!}
                </div>
            </div>
            <div class="grid-col">
                <div class="grid-col-header">Obstacles / Pending Issues</div>
                <div class="grid-col-body">
                    {!! nl2br(e($report->problems ?? 'No major problems reported.')) !!}
                </div>
            </div>
        </div>
    </div>

    <!-- 5. DOCUMENTATION -->
    @php $docs = $report->documentation_files; @endphp
    @if(count($docs) > 0)
        <div class="page-break"></div>
        <div class="section-header">SECTION 04: PROJECT DOCUMENTATIONS</div>
        
        <table class="doc-grid">
            @foreach(array_chunk($docs, 4) as $chunk)
            <tr>
                @foreach($chunk as $doc)
                <td class="doc-card">
                    <img src="{{ $doc['url'] }}" alt="Documentation Image">
                    <p>{{ $doc['name'] }}</p>
                </td>
                @endforeach
                @for($i = count($chunk); $i < 4; $i++)
                <td style="width: 25%;"></td>
                @endfor
            </tr>
            @endforeach
        </table>
    @endif

    <!-- 6. FINAL SIGNATURE BLOCK -->
    <div class="no-break" style="margin-top: 30pt; border-top: 2px solid #0f172a; padding-top: 10pt;">
        <div style="font-size: 10pt; font-weight: bold; margin-bottom: 10pt;">APPROVAL & VALIDATIONS:</div>
        <div class="signature-section">
            <div class="signature-col">
                <div class="signature-label">Prepared By,</div>
                <div class="signature-name">__________________________</div>
                <div class="signature-role">Supervisor / Site Manager</div>
            </div>
            <div class="signature-col">
                <div class="signature-label">Checked By,</div>
                <div class="signature-name">__________________________</div>
                <div class="signature-role">Project Manager</div>
            </div>
            <div class="signature-col">
                <div class="signature-label">Approved By,</div>
                <div class="signature-name">__________________________</div>
                <div class="signature-role">Client / Owner Representative</div>
            </div>
        </div>
    </div>

</body>
</html>


