<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>RAB - {{ $project->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 14px;
            margin: 0 0 5px 0;
        }

        .header h2 {
            font-size: 12px;
            margin: 0 0 10px 0;
        }

        .info-table {
            margin-bottom: 15px;
        }

        .info-table td {
            padding: 2px 5px;
        }

        table.rab-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.rab-table th,
        table.rab-table td {
            border: 1px solid #000;
            padding: 4px 6px;
        }

        table.rab-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .section-header {
            background-color: #e8e8e8;
            font-weight: bold;
        }

        .subsection-header {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .subtotal-row {
            background-color: #ffffcc;
            font-weight: bold;
        }

        .total-row {
            background-color: #d9edf7;
            font-weight: bold;
        }

        .item-indent {
            padding-left: 20px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    {{-- REKAPITULASI --}}
    <div class="header">
        <h1>DAFTAR REKAPITULASI RENCANA ANGGARAN BIAYA (RAB)</h1>
    </div>

    <table class="info-table">
        <tr>
            <td width="150">PEKERJAAN</td>
            <td>: {{ strtoupper($project->name) }}</td>
        </tr>
        <tr>
            <td>TAHUN ANGGARAN</td>
            <td>: {{ $project->start_date ? $project->start_date->format('Y') : date('Y') }}</td>
        </tr>
    </table>

    <table class="rab-table">
        <thead>
            <tr>
                <th width="8%">NO</th>
                <th width="62%">URAIAN PEKERJAAN</th>
                <th width="30%">JUMLAH HARGA</th>
            </tr>
        </thead>
        <tbody>
            @php
                $romanNumerals = [
                    'I',
                    'II',
                    'III',
                    'IV',
                    'V',
                    'VI',
                    'VII',
                    'VIII',
                    'IX',
                    'X',
                    'XI',
                    'XII',
                    'XIII',
                    'XIV',
                    'XV',
                    'XVI',
                    'XVII',
                    'XVIII',
                    'XIX',
                    'XX'
                ];
                $index = 0;
                $sectionLabels = [];

                // Terbilang function
                function terbilang($angka)
                {
                    $angka = abs($angka);
                    $bilangan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

                    if ($angka < 12) {
                        return $bilangan[$angka];
                    } elseif ($angka < 20) {
                        return terbilang($angka - 10) . ' belas';
                    } elseif ($angka < 100) {
                        return terbilang(floor($angka / 10)) . ' puluh ' . terbilang($angka % 10);
                    } elseif ($angka < 200) {
                        return 'seratus ' . terbilang($angka - 100);
                    } elseif ($angka < 1000) {
                        return terbilang(floor($angka / 100)) . ' ratus ' . terbilang($angka % 100);
                    } elseif ($angka < 2000) {
                        return 'seribu ' . terbilang($angka - 1000);
                    } elseif ($angka < 1000000) {
                        return terbilang(floor($angka / 1000)) . ' ribu ' . terbilang($angka % 1000);
                    } elseif ($angka < 1000000000) {
                        return terbilang(floor($angka / 1000000)) . ' juta ' . terbilang($angka % 1000000);
                    } elseif ($angka < 1000000000000) {
                        return terbilang(floor($angka / 1000000000)) . ' milyar ' . terbilang($angka % 1000000000);
                    } elseif ($angka < 1000000000000000) {
                        return terbilang(floor($angka / 1000000000000)) . ' triliun ' . terbilang($angka % 1000000000000);
                    }
                    return trim(preg_replace('/\s+/', ' ', terbilang($angka)));
                }

                $ppn = $grandTotal * 0.10;
                $grandTotalWithPpn = $grandTotal + $ppn;
                $grandTotalRounded = round($grandTotalWithPpn / 1000) * 1000;
            @endphp
            @foreach($sections as $section)
                @php
                    $roman = $romanNumerals[$index] ?? ($index + 1);
                    $sectionLabels[] = $roman;
                @endphp
                <tr>
                    <td class="text-center" style="font-weight: bold;">{{ $roman }}</td>
                    <td style="font-weight: bold;">{{ strtoupper($section->name) }}</td>
                    <td class="text-right" style="font-weight: bold;">
                        {{ number_format($section->total_price, 2, ',', '.') }}
                    </td>
                </tr>
                @php $index++; @endphp
            @endforeach
            <tr class="subtotal-row">
                <td colspan="2" class="text-right">JUMLAH TOTAL ( {{ implode(' + ', $sectionLabels) }} )</td>
                <td class="text-right">{{ number_format($grandTotal, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-right">PPN 10%</td>
                <td class="text-right">{{ number_format($ppn, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-right">GRAND TOTAL</td>
                <td class="text-right">{{ number_format($grandTotalWithPpn, 2, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2" class="text-right">GRAND TOTAL DIBULATKAN</td>
                <td class="text-right">{{ number_format($grandTotalRounded, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table class="info-table" style="margin-top: 10px;">
        <tr>
            <td width="80"><strong>Terbilang</strong></td>
            <td>: {{ terbilang($grandTotalRounded) }} rupiah</td>
        </tr>
    </table>

    {{-- RINCIAN RAB --}}
    <div class="page-break"></div>

    <div class="header">
        <h1>RENCANA ANGGARAN BIAYA</h1>
        <h2>{{ $project->name }}</h2>
    </div>

    <table class="info-table">
        <tr>
            <td width="120">Kegiatan</td>
            <td>: {{ $project->name }}</td>
        </tr>
        <tr>
            <td>Lokasi/Wilayah</td>
            <td>: {{ $project->location ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tahun Pengerjaan</td>
            <td>: {{ $project->start_date ? $project->start_date->format('Y') : date('Y') }}</td>
        </tr>
    </table>

    <table class="rab-table">
        <thead>
            <tr>
                <th width="6%">NO</th>
                <th width="40%">URAIAN PEKERJAAN</th>
                <th width="10%">VOLUME</th>
                <th width="8%">SATUAN</th>
                <th width="16%">HARGA SATUAN (RP)</th>
                <th width="20%">JUMLAH HARGA (RP)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sectionLetters = range('A', 'Z');
                $letterIndex = 0;

                function renderPdfSection($section, $sectionLetters, &$letterIndex, $level = 0)
                {
                    $html = '';
                    $letter = $level === 0 ? ($sectionLetters[$letterIndex] ?? ($letterIndex + 1)) : '';
                    $headerClass = $level === 0 ? 'section-header' : 'subsection-header';
                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level);

                    // Section header
                    $html .= '<tr class="' . $headerClass . '">';
                    $html .= '<td class="text-center">' . $letter . '</td>';
                    $html .= '<td>' . $indent . strtoupper($section->name) . '</td>';
                    $html .= '<td></td><td></td><td></td><td></td>';
                    $html .= '</tr>';

                    // Items
                    $itemNumber = 1;
                    foreach ($section->items->sortBy('code', SORT_NATURAL) as $item) {
                        $html .= '<tr>';
                        $html .= '<td class="text-center">' . $itemNumber . '</td>';
                        $html .= '<td class="item-indent">' . $indent . '&nbsp;&nbsp;&nbsp;' . $item->work_name . '</td>';
                        $html .= '<td class="text-right">' . number_format($item->volume, 2, ',', '.') . '</td>';
                        $html .= '<td class="text-center">' . $item->unit . '</td>';
                        $html .= '<td class="text-right">' . number_format($item->unit_price, 2, ',', '.') . '</td>';
                        $html .= '<td class="text-right">' . number_format($item->total_price, 2, ',', '.') . '</td>';
                        $html .= '</tr>';
                        $itemNumber++;
                    }

                    // Child sections
                    foreach ($section->children->sortBy('code', SORT_NATURAL) as $child) {
                        $html .= renderPdfSection($child, $sectionLetters, $letterIndex, $level + 1);
                    }

                    // Subtotal (only for level 0)
                    if ($level === 0) {
                        $html .= '<tr class="subtotal-row">';
                        $html .= '<td></td><td></td><td></td><td></td>';
                        $html .= '<td class="text-right">JUMLAH ' . $letter . '</td>';
                        $html .= '<td class="text-right">' . number_format($section->total_price, 2, ',', '.') . '</td>';
                        $html .= '</tr>';
                        $letterIndex++;
                    }

                    return $html;
                }
            @endphp

            @foreach($sections as $section)
                {!! renderPdfSection($section, $sectionLetters, $letterIndex) !!}
            @endforeach

            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($grandTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>


