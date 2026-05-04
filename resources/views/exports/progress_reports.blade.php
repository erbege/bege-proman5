<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Progres - {{ $project->name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; margin: 0; }
        .subtitle { font-size: 14px; margin: 5px 0 0 0; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">REKAPITULASI LAPORAN PROGRES</h1>
        <p class="subtitle">Proyek: {{ $project->name }} ({{ $project->code }})</p>
        <p class="subtitle">Diekspor pada: {{ now()->format('d M Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Kode</th>
                <th>Pekerjaan (RAB)</th>
                <th>Progres</th>
                <th>Kumulatif</th>
                <th>Pekerja</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reports as $index => $report)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $report->report_date->format('d/m/Y') }}</td>
                <td>{{ $report->report_code }}</td>
                <td>{{ $report->rabItem ? $report->rabItem->work_name : 'Laporan Umum' }}</td>
                <td class="text-right">{{ number_format($report->progress_percentage, 2) }}%</td>
                <td class="text-right">{{ number_format($report->cumulative_progress, 2) }}%</td>
                <td class="text-center">{{ $report->workers_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
