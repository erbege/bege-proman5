<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Time Schedule - {{ $project->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            padding: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 10px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
            /* Force table to fit constraints */
        }

        th,
        td {
            border: 1px solid #333;
            padding: 2px 2px;
            /* Reduce padding */
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
            /* Hide overflow */
            white-space: nowrap;
            /* Prevent wrapping in week columns */
        }

        /* Allow wrapping in description column */
        .desc-col {
            white-space: normal;
            word-wrap: break-word;
            text-align: left;
        }

        th {
            background-color: #D4A574;
            color: white;
            font-weight: bold;
            font-size: 7px;
            /* Slightly smaller header */
        }

        .section-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        /* Specific column widths */
        .col-no {
            width: 3%;
        }

        .col-desc {
            width: 30%;
        }

        /* Give description plenty of room */
        .col-weight {
            width: 5%;
        }

        /* Week columns will split the remaining 62% */

        .item-row td:nth-child(1),
        .item-row td:nth-child(2) {
            text-align: left;
        }

        .bar {
            background-color: #3B82F6;
            height: 6px;
            /* Smaller bar */
            width: 100%;
            border-radius: 1px;
        }

        .footer-row {
            font-weight: bold;
        }

        /* ... existing footer styles ... */
        .footer-plan {
            background-color: #d1fae5;
        }

        .footer-actual {
            background-color: #dbeafe;
        }

        .footer-deviation {
            background-color: #fef3c7;
        }

        .positive {
            color: #059669;
        }

        .negative {
            color: #dc2626;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>TIME SCHEDULE (KURVA S)</h1>
        <p>{{ $project->name }} ({{ $project->code }})</p>
        <p>Periode: {{ $project->start_date->format('d M Y') }} - {{ $project->end_date->format('d M Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="col-no">NO</th>
                <th rowspan="2" class="col-desc">URAIAN PEKERJAAN</th>
                <th rowspan="2" class="col-weight">BOBOT %</th>
                @foreach($months as $monthData)
                    <th colspan="{{ count($monthData['weeks']) }}">{{ $monthData['label'] }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach($months as $monthData)
                    @foreach($monthData['weeks'] as $week)
                        <th>M{{ $week['num'] }}</th>
                    @endforeach
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rabSections as $section)
                @include('exports.partials.section-pdf-row', [
                    'section' => $section,
                    'startDate' => $project->start_date,
                    'totalWeeks' => $totalWeeks
                ])
            @endforeach
        </tbody>
    <tfoot>
            <tr class="footer-row footer-plan">
                <td colspan="3" class="text-right">Rencana Mingguan (%)</td>
            @foreach($schedules as $schedule)
                <td>{{ number_format($schedule->planned_weight, 1) }}</td>
            @endforeach
                @for($i = count($schedules); $i < $totalWeeks; $i++)
                    <td></td>
                @endfor
        </tr>
            <tr class="footer-row footer-plan">
                <td colspan="3" class="text-right">Rencana Kumulatif (%)</td>
            @foreach($schedules as $schedule)
                <td>{{ number_format($schedule->planned_cumulative, 1) }}</td>
            @endforeach
                @for($i = count($schedules); $i < $totalWeeks; $i++)
                    <td></td>
                @endfor
        </tr>
            <tr class="footer-row footer-actual">
                <td colspan="3" class="text-right">Realisasi Mingguan (%)</td>
            @foreach($schedules as $schedule)
                <td>{{ number_format($schedule->actual_weight, 1) }}</td>
            @endforeach
                @for($i = count($schedules); $i < $totalWeeks; $i++)
                    <td></td>
                @endfor
        </tr>
            <tr class="footer-row footer-actual">
                <td colspan="3" class="text-right">Realisasi Kumulatif (%)</td>
            @foreach($schedules as $schedule)
                <td>{{ number_format($schedule->actual_cumulative, 1) }}</td>
            @endforeach
                @for($i = count($schedules); $i < $totalWeeks; $i++)
                    <td></td>
                @endfor
        </tr>
        <tr class="footer-row footer-deviation">
            <td colspan="3" class="text-right">Deviasi (%)</td>
                @foreach($schedules as $schedule)
                    <td class="{{ $schedule->deviation > 0 ? 'positive' : ($schedule->deviation < 0 ? 'negative' : '') }}">
                    {{ $schedule->deviation > 0 ? '+' : '' }}{{ number_format($schedule->deviation, 1) }}
                    </td>
                @endforeach
                @for($i = count($schedules); $i < $totalWeeks; $i++)
                    <td></td>
                @endfor
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px; font-size: 9px; color: #666;">
        <p>Dicetak pada: {{ now()->format('d M Y H:i') }}</p>
    </div>
</body>

</html>