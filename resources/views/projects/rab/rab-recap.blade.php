<table>
    <thead>
        <tr>
            <th colspan="3" style="font-weight: bold; font-size: 16px; text-align: center;">REKAPITULASI RENCANA
                ANGGARAN BIAYA</th>
        </tr>
        <tr>
            <th colspan="3" style="font-weight: bold; text-align: center;">{{ $project->name }}</th>
        </tr>
        <tr>
            <th colspan="3"></th>
        </tr>
        <tr>
            <th colspan="3" style="text-align: left;">Kegiatan : {{ $project->name }}</th>
        </tr>
        <tr>
            <th colspan="3" style="text-align: left;">Lokasi/Wilayah : {{ $project->location ?? '-' }}</th>
        </tr>
        <tr>
            <th colspan="3" style="text-align: left;">Tahun Pengerjaan:
                {{ $project->start_date ? $project->start_date->format('Y') : date('Y') }}</th>
        </tr>
        <tr>
            <th colspan="3"></th>
        </tr>
        <tr>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f0f0f0;">NO
            </th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f0f0f0;">
                URAIAN PEKERJAAN</th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f0f0f0;">
                JUMLAH HARGA (RP)</th>
        </tr>
    </thead>
    <tbody>
        @php $letterIndex = 0;
        $letters = range('A', 'Z'); @endphp
        @foreach($sections as $section)
            <tr>
                <td style="border: 1px solid #000000; text-align: center; font-weight: bold;">
                    {{ $letters[$letterIndex] ?? ($letterIndex + 1) }}</td>
                <td style="border: 1px solid #000000; font-weight: bold;">{{ strtoupper($section->name) }}</td>
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    {{ number_format($section->total_price, 2, ',', '.') }}</td>
            </tr>
            @php $letterIndex++; @endphp
        @endforeach
        <tr>
            <td colspan="2" style="border: 1px solid #000000; font-weight: bold; text-align: right;">TOTAL</td>
            <td style="border: 1px solid #000000; font-weight: bold; text-align: right;">
                {{ number_format($grandTotal, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: right; font-weight: bold;">PPN 11%</td>
            <td style="border: 1px solid #000000; text-align: right;">
                {{ number_format($grandTotal * 0.11, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2" style="border: 1px solid #000000; font-weight: bold; text-align: right;">TOTAL + PPN</td>
            <td style="border: 1px solid #000000; font-weight: bold; text-align: right;">
                {{ number_format($grandTotal * 1.11, 2, ',', '.') }}</td>
        </tr>
    </tbody>
</table>