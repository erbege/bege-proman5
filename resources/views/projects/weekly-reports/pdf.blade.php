<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Mingguan - {{ $project->name }} - Minggu {{ $report->week_number }}</title>
    <style>
        /* =========================================================
           PUPR Standard — Laporan Mingguan PDF Styling
           ========================================================= */
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 25mm 20mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Times New Roman', 'DejaVu Serif', serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        .page-break { page-break-after: always; }
        .no-break   { page-break-inside: avoid; }

        /* --- Running Footer --- */
        footer {
            position: fixed;
            bottom: -15mm;
            left: 0; right: 0;
            height: 10mm;
            border-top: 0.5pt solid #999;
            padding-top: 3pt;
            font-size: 7pt;
            color: #666;
        }
        .footer-left  { float: left; }
        .footer-right { float: right; }
        .page-number:after { content: counter(page); }

        /* --- Kop Surat (Letterhead) --- */
        .kop-surat {
            border: 2pt solid #000;
            padding: 10pt;
            margin-bottom: 15pt;
        }

        .kop-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1pt;
            border-bottom: 2pt solid #000;
            padding-bottom: 8pt;
            margin-bottom: 8pt;
        }

        .kop-subtitle {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 4pt;
        }

        .kop-info {
            text-align: center;
            font-size: 9pt;
            color: #333;
        }

        /* --- Section Headers --- */
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1.5pt solid #000;
            padding-bottom: 3pt;
            margin-top: 15pt;
            margin-bottom: 10pt;
        }

        .section-number {
            font-weight: bold;
            margin-right: 5pt;
        }

        /* --- Data Kontrak Table --- */
        table.kontrak-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15pt;
            font-size: 9pt;
        }

        table.kontrak-table td {
            padding: 3pt 5pt;
            vertical-align: top;
        }

        table.kontrak-table .label {
            width: 30%;
            font-weight: bold;
        }

        table.kontrak-table .separator {
            width: 3%;
            text-align: center;
        }

        /* --- Progress Table (Cumulative) --- */
        table.progress-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15pt;
            font-size: 7.5pt;
        }

        table.progress-table th,
        table.progress-table td {
            border: 0.5pt solid #000;
            padding: 2pt 3pt;
            vertical-align: middle;
        }

        table.progress-table th {
            background: #e8e8e8;
            font-weight: bold;
            text-align: center;
            font-size: 7pt;
        }

        table.progress-table .section-row td {
            font-weight: bold;
            background: #f0f0f0;
        }

        table.progress-table .grand-total td {
            font-weight: bold;
            background: #d0d0d0;
            font-size: 8pt;
            border-top: 1.5pt solid #000;
        }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-left   { text-align: left; }
        .bold        { font-weight: bold; }

        .val-pos { color: #006600; }
        .val-neg { color: #cc0000; }

        /* --- Detail / Daily Log Table --- */
        table.daily-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15pt;
            font-size: 8pt;
        }

        table.daily-table th,
        table.daily-table td {
            border: 0.5pt solid #000;
            padding: 3pt 4pt;
            vertical-align: top;
        }

        table.daily-table th {
            background: #e8e8e8;
            font-weight: bold;
            text-align: center;
            font-size: 8pt;
        }

        /* --- Weather & Labor Table --- */
        table.weather-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15pt;
            font-size: 8.5pt;
        }

        table.weather-table th,
        table.weather-table td {
            border: 0.5pt solid #000;
            padding: 3pt 5pt;
            vertical-align: middle;
        }

        table.weather-table th {
            background: #e8e8e8;
            font-weight: bold;
            text-align: center;
        }

        /* --- Documentation Grid --- */
        table.doc-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15pt;
        }

        table.doc-grid td {
            width: 50%;
            padding: 5pt;
            vertical-align: top;
            text-align: center;
        }

        table.doc-grid img {
            width: 100%;
            max-height: 180pt;
            object-fit: cover;
            border: 0.5pt solid #999;
        }

        table.doc-grid .caption {
            font-size: 7.5pt;
            color: #333;
            margin-top: 3pt;
            font-style: italic;
        }

        /* --- Signature Block --- */
        .ttd-section {
            margin-top: 25pt;
            width: 100%;
        }

        .ttd-place-date {
            text-align: right;
            font-size: 9pt;
            margin-bottom: 10pt;
        }

        table.ttd-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.ttd-table td {
            text-align: center;
            vertical-align: top;
            padding: 5pt 8pt;
            font-size: 9pt;
        }

        .ttd-role {
            font-weight: bold;
            margin-bottom: 60pt;
        }

        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .ttd-position {
            font-size: 8pt;
            color: #333;
        }
    </style>
</head>

<body>
    <!-- Running Footer -->
    <footer>
        <div class="footer-left">Laporan Mingguan — {{ $project->code }} — Minggu {{ $report->week_number }}</div>
        <div class="footer-right">Halaman <span class="page-number"></span></div>
    </footer>

    <!-- ============================================================
         I. KOP SURAT (LETTERHEAD)
         ============================================================ -->
    <div class="kop-surat">
        <div class="kop-title">LAPORAN MINGGUAN</div>
        <div class="kop-subtitle">( WEEKLY REPORT )</div>
        <div class="kop-info" style="margin-top: 8pt;">
            <strong>{{ $project->name }}</strong>
        </div>
        <div class="kop-info">
            Minggu ke-{{ $report->week_number }} &bull;
            Periode: {{ $report->period_start->translatedFormat('d F Y') }} s.d. {{ $report->period_end->translatedFormat('d F Y') }}
        </div>
    </div>

    <!-- ============================================================
         II. DATA UMUM KONTRAK
         ============================================================ -->
    <div class="section-title">
        <span class="section-number">I.</span> DATA UMUM KONTRAK
    </div>

    <table class="kontrak-table">
        <tr>
            <td class="label">Nama Pekerjaan</td>
            <td class="separator">:</td>
            <td>{{ $project->name }}</td>
        </tr>
        <tr>
            <td class="label">Nomor / Kode Proyek</td>
            <td class="separator">:</td>
            <td>{{ $project->code }}</td>
        </tr>
        <tr>
            <td class="label">Lokasi Pekerjaan</td>
            <td class="separator">:</td>
            <td>{{ $project->location ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Pemberi Tugas (Owner)</td>
            <td class="separator">:</td>
            <td>{{ $project->client_name ?? '-' }}</td>
        </tr>
        @if($project->contract_value)
        <tr>
            <td class="label">Nilai Kontrak</td>
            <td class="separator">:</td>
            <td>{{ $project->formatted_contract_value }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Masa Pelaksanaan</td>
            <td class="separator">:</td>
            <td>
                {{ $project->duration_days }} Hari Kalender
                ({{ $project->start_date ? $project->start_date->translatedFormat('d F Y') : '-' }}
                s.d.
                {{ $project->end_date ? $project->end_date->translatedFormat('d F Y') : '-' }})
            </td>
        </tr>
        <tr>
            <td class="label">Minggu Pelaporan</td>
            <td class="separator">:</td>
            <td>Minggu ke-{{ $report->week_number }}
                ({{ $report->period_start->translatedFormat('d F Y') }} s.d. {{ $report->period_end->translatedFormat('d F Y') }})
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- ============================================================
         III. REKAPITULASI PROGRESS KUMULATIF
         ============================================================ -->
    <div class="section-title">
        <span class="section-number">II.</span> REKAPITULASI PROGRESS PEKERJAAN
    </div>

    <table class="progress-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 4%;">No</th>
                <th rowspan="2" style="width: 26%;">Uraian Pekerjaan</th>
                <th rowspan="2" style="width: 6%;">Bobot (%)</th>
                <th colspan="2" style="width: 14%;">s.d. Minggu Lalu</th>
                <th colspan="2" style="width: 14%;">Minggu Ini</th>
                <th colspan="2" style="width: 14%;">s.d. Minggu Ini</th>
                <th colspan="3" style="width: 22%;">Deviasi (%)</th>
            </tr>
            <tr>
                <th>Rencana</th>
                <th>Realisasi</th>
                <th>Rencana</th>
                <th>Realisasi</th>
                <th>Rencana</th>
                <th>Realisasi</th>
                <th>Lalu</th>
                <th>Ini</th>
                <th>Kum.</th>
            </tr>
        </thead>
        <tbody>
            @php $rowNum = 0; @endphp
            @if($report->cumulative_data && isset($report->cumulative_data['sections']))
                @foreach($report->cumulative_data['sections'] as $section)
                    @include('projects.weekly-reports.partials.pdf-cumulative-section', ['section' => $section, 'level' => 0, 'rowNum' => &$rowNum])
                @endforeach

                @if(isset($report->cumulative_data['totals']))
                    @php $totals = $report->cumulative_data['totals']; @endphp
                    <tr class="grand-total">
                        <td colspan="2" class="text-center">JUMLAH TOTAL</td>
                        <td class="text-center">{{ number_format($totals['weight'] ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($totals['planned_prev'] ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($totals['actual_prev'] ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($totals['planned_current'] ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($totals['actual_current'] ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($totals['planned_cumulative'] ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($totals['actual_cumulative'] ?? 0, 2) }}</td>
                        <td class="text-center {{ ($totals['deviation_prev'] ?? 0) >= 0 ? 'val-pos' : 'val-neg' }}">{{ number_format($totals['deviation_prev'] ?? 0, 2) }}</td>
                        <td class="text-center {{ ($totals['deviation_current'] ?? 0) >= 0 ? 'val-pos' : 'val-neg' }}">{{ number_format($totals['deviation_current'] ?? 0, 2) }}</td>
                        <td class="text-center {{ ($totals['deviation_cumulative'] ?? 0) >= 0 ? 'val-pos' : 'val-neg' }}">{{ number_format($totals['deviation_cumulative'] ?? 0, 2) }}</td>
                    </tr>
                @endif
            @else
                <tr><td colspan="12" class="text-center" style="padding: 20pt;">Data rekapitulasi belum tersedia.</td></tr>
            @endif
        </tbody>
    </table>

    @php
        $totals = $report->cumulative_data['totals'] ?? null;
        $dev = $totals['deviation_cumulative'] ?? 0;
    @endphp
    @if($totals)
    <div class="no-break" style="margin-bottom: 15pt; font-size: 9pt;">
        <strong>Keterangan:</strong><br>
        &bull; Bobot Rencana Kumulatif s.d. Minggu Ini: <strong>{{ number_format($totals['planned_cumulative'] ?? 0, 2) }}%</strong><br>
        &bull; Bobot Realisasi Kumulatif s.d. Minggu Ini: <strong>{{ number_format($totals['actual_cumulative'] ?? 0, 2) }}%</strong><br>
        &bull; Deviasi Kumulatif: <strong style="color: {{ $dev >= 0 ? '#006600' : '#cc0000' }}">{{ $dev >= 0 ? '+' : '' }}{{ number_format($dev, 2) }}%</strong>
        ({{ $dev >= 0 ? 'Lebih cepat dari rencana' : 'Terlambat dari rencana' }})
    </div>
    @endif

    <div class="page-break"></div>

    <!-- ============================================================
         IV. CATATAN HARIAN PELAKSANAAN
         ============================================================ -->
    <div class="section-title">
        <span class="section-number">III.</span> CATATAN HARIAN PELAKSANAAN
    </div>

    @if($report->detail_data && count($report->detail_data) > 0)
        <table class="daily-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 12%;">Hari / Tanggal</th>
                    <th style="width: 24%;">Uraian Pekerjaan</th>
                    <th style="width: 8%;">Volume (%)</th>
                    <th style="width: 32%;">Keterangan / Uraian Kegiatan</th>
                    <th style="width: 10%;">Cuaca</th>
                    <th style="width: 10%;">Pelapor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report->detail_data as $idx => $detail)
                <tr class="no-break">
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ $detail['date_label'] }}</td>
                    <td>{{ $detail['rab_item'] ? $detail['rab_item']['code'] . ' ' . $detail['rab_item']['name'] : '-' }}</td>
                    <td class="text-center bold">{{ $detail['progress_percentage'] }}%</td>
                    <td>{{ $detail['description'] ?? '-' }}@if(!empty($detail['issues']))<br><em style="color: #cc0000;">Kendala: {{ $detail['issues'] }}</em>@endif</td>
                    <td class="text-center">{{ $detail['weather'] ?? '-' }}</td>
                    <td class="text-center">{{ $detail['reporter'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 30pt; color: #666;">Tidak ada catatan harian pada periode ini.</p>
    @endif

    <!-- ============================================================
         V. DATA CUACA DAN TENAGA KERJA
         ============================================================ -->
    <div class="section-title">
        <span class="section-number">IV.</span> DATA CUACA DAN TENAGA KERJA
    </div>

    @if(count($weatherSummary) > 0)
        <table class="weather-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 20%;">Hari / Tanggal</th>
                    <th style="width: 20%;">Cuaca</th>
                    <th style="width: 15%;">Jumlah Tenaga Kerja</th>
                    <th style="width: 40%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($weatherSummary as $idx => $ws)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ $ws['date'] }}</td>
                    <td class="text-center">{{ $ws['weather'] }}</td>
                    <td class="text-center">{{ $ws['workers'] }} orang</td>
                    <td>{{ $ws['description'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20pt; color: #666;">Data cuaca dan tenaga kerja tidak tersedia.</p>
    @endif

    <!-- ============================================================
         VI. KEGIATAN DAN PERMASALAHAN
         ============================================================ -->
    <div class="section-title">
        <span class="section-number">V.</span> KEGIATAN DAN PERMASALAHAN
    </div>

    <div class="no-break" style="margin-bottom: 15pt;">
        <table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
            <tr>
                <td style="width: 48%; vertical-align: top; border: 0.5pt solid #000; padding: 8pt;">
                    <div style="font-weight: bold; border-bottom: 0.5pt solid #000; padding-bottom: 3pt; margin-bottom: 5pt;">
                        A. Kegiatan Utama Minggu Ini
                    </div>
                    <div style="white-space: pre-line; line-height: 1.5;">{{ $report->activities ?? 'Tidak ada kegiatan yang dilaporkan.' }}</div>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%; vertical-align: top; border: 0.5pt solid #000; padding: 8pt;">
                    <div style="font-weight: bold; border-bottom: 0.5pt solid #000; padding-bottom: 3pt; margin-bottom: 5pt;">
                        B. Permasalahan / Kendala
                    </div>
                    <div style="white-space: pre-line; line-height: 1.5;">{{ $report->problems ?? 'Tidak ada permasalahan yang dilaporkan.' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ============================================================
         VII. DOKUMENTASI FOTO
         ============================================================ -->
    @php $docs = $report->documentation_files; @endphp
    @if(count($docs) > 0)
        <div class="page-break"></div>
        <div class="section-title">
            <span class="section-number">VI.</span> DOKUMENTASI FOTO LAPANGAN
        </div>

        @foreach(array_chunk($docs, 4) as $chunkIdx => $chunk)
            <table class="doc-grid">
                @foreach(array_chunk($chunk, 2) as $row)
                <tr>
                    @foreach($row as $doc)
                    <td>
                        <img src="{{ $doc['url'] }}" alt="Foto Dokumentasi">
                        <div class="caption">{{ $doc['name'] }}</div>
                    </td>
                    @endforeach
                    @if(count($row) < 2)
                    <td></td>
                    @endif
                </tr>
                @endforeach
            </table>
            @if(!$loop->last)
                <div class="page-break"></div>
            @endif
        @endforeach
    @endif

    <!-- ============================================================
         VIII. TANDA TANGAN (APPROVAL BLOCK)
         ============================================================ -->
    <div class="no-break ttd-section">
        <div class="section-title" style="margin-top: 5pt;">
            <span class="section-number">{{ count($docs) > 0 ? 'VII' : 'VI' }}.</span> PENGESAHAN
        </div>

        <div class="ttd-place-date">
            {{ $project->location ?? '...................' }}, {{ now()->translatedFormat('d F Y') }}
        </div>

        <table class="ttd-table">
            <tr>
                <td style="width: 25%;">
                    <div class="ttd-role">Dibuat oleh,</div>
                    <div class="ttd-name">
                        @if($report->creator)
                            {{ $report->creator->name }}
                        @else
                            __________________________
                        @endif
                    </div>
                    <div class="ttd-position">Pelaksana / Site Manager</div>
                </td>
                <td style="width: 25%;">
                    <div class="ttd-role">Diperiksa oleh,</div>
                    <div class="ttd-name">
                        @if($report->reviewer)
                            {{ $report->reviewer->name }}
                        @else
                            __________________________
                        @endif
                    </div>
                    <div class="ttd-position">Konsultan Pengawas</div>
                </td>
                <td style="width: 25%;">
                    <div class="ttd-role">Disetujui oleh,</div>
                    <div class="ttd-name">
                        @if($report->approver)
                            {{ $report->approver->name }}
                        @else
                            __________________________
                        @endif
                    </div>
                    <div class="ttd-position">Direksi Teknis / PM</div>
                </td>
                <td style="width: 25%;">
                    <div class="ttd-role">Mengetahui,</div>
                    <div class="ttd-name">
                        @if($project->client_name)
                            {{ $project->client_name }}
                        @else
                            __________________________
                        @endif
                    </div>
                    <div class="ttd-position">PPK / Owner</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
