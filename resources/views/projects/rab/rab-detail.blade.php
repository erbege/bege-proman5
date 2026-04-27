@php 
    $canViewFinancials = auth()->user()->can('financials.view');
    $colCount = $canViewFinancials ? 6 : 4;
@endphp
<table>
    <thead>
        <tr>
            <th colspan="{{ $colCount }}" style="font-weight: bold; font-size: 16px; text-align: center;">RENCANA ANGGARAN BIAYA</th>
        </tr>
        <tr>
            <th colspan="{{ $colCount }}" style="font-weight: bold; text-align: center;">{{ $project->name }}</th>
        </tr>
        <tr>
            <th colspan="{{ $colCount }}"></th>
        </tr>
        <tr>
            <th colspan="{{ $colCount }}" style="text-align: left;">Kegiatan : {{ $project->name }}</th>
        </tr>
        <tr>
            <th colspan="{{ $colCount }}" style="text-align: left;">Lokasi/Wilayah : {{ $project->location ?? '-' }}</th>
        </tr>
        <tr>
            <th colspan="{{ $colCount }}" style="text-align: left;">Tahun Pengerjaan:
                {{ $project->start_date ? $project->start_date->format('Y') : date('Y') }}</th>
        </tr>
        <tr>
            <th colspan="{{ $colCount }}"></th>
        </tr>
        <tr>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f0f0f0;">NO
            </th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f0f0f0;">
                URAIAN PEKERJAAN</th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f0f0f0;">
                VOLUME</th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f0f0f0;">
                SATUAN</th>
            @if($canViewFinancials)
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f0f0f0;">
                HARGA SATUAN (RP)</th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f0f0f0;">
                JUMLAH HARGA (RP)</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @php $letterIndex = 0;
        $letters = range('A', 'Z'); @endphp
        @foreach($sections as $section)
            @php $letter = $letters[$letterIndex] ?? ($letterIndex + 1); @endphp
            {{-- Section header --}}
            <tr>
                <td style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #e8e8e8;">
                    {{ $letter }}</td>
                <td style="border: 1px solid #000000; font-weight: bold; background-color: #e8e8e8;">
                    {{ strtoupper($section->name) }}</td>
                <td style="border: 1px solid #000000; background-color: #e8e8e8;"></td>
                <td style="border: 1px solid #000000; background-color: #e8e8e8;"></td>
                @if($canViewFinancials)
                <td style="border: 1px solid #000000; background-color: #e8e8e8;"></td>
                <td style="border: 1px solid #000000; background-color: #e8e8e8;"></td>
                @endif
            </tr>
            {{-- Items in this section --}}
            @php $itemNo = 1; @endphp
            @foreach($section->items->sortBy('code', SORT_NATURAL) as $item)
                <tr>
                    <td style="border: 1px solid #000000; text-align: center;">{{ $itemNo }}</td>
                    <td style="border: 1px solid #000000;"> {{ $item->work_name }}</td>
                    <td style="border: 1px solid #000000; text-align: right;">{{ number_format($item->volume, 2, ',', '.') }}
                    </td>
                    <td style="border: 1px solid #000000; text-align: center;">{{ $item->unit }}</td>
                    @if($canViewFinancials)
                    <td style="border: 1px solid #000000; text-align: right;">
                        {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                    <td style="border: 1px solid #000000; text-align: right;">
                        {{ number_format($item->total_price, 2, ',', '.') }}</td>
                    @endif
                </tr>
                @php $itemNo++; @endphp
            @endforeach
            {{-- Child sections (level 1) --}}
            @foreach($section->children->sortBy('code', SORT_NATURAL) as $childSection)
                <tr>
                    <td style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f5f5f5;">
                    </td>
                    <td style="border: 1px solid #000000; font-weight: bold; background-color: #f5f5f5;">
                        {{ strtoupper($childSection->name) }}</td>
                    <td style="border: 1px solid #000000; background-color: #f5f5f5;"></td>
                    <td style="border: 1px solid #000000; background-color: #f5f5f5;"></td>
                    @if($canViewFinancials)
                    <td style="border: 1px solid #000000; background-color: #f5f5f5;"></td>
                    <td style="border: 1px solid #000000; background-color: #f5f5f5;"></td>
                    @endif
                </tr>
                @php $childItemNo = 1; @endphp
                @foreach($childSection->items->sortBy('code', SORT_NATURAL) as $item)
                    <tr>
                        <td style="border: 1px solid #000000; text-align: center;">{{ $childItemNo }}</td>
                        <td style="border: 1px solid #000000;"> {{ $item->work_name }}</td>
                        <td style="border: 1px solid #000000; text-align: right;">{{ number_format($item->volume, 2, ',', '.') }}
                        </td>
                        <td style="border: 1px solid #000000; text-align: center;">{{ $item->unit }}</td>
                        @if($canViewFinancials)
                        <td style="border: 1px solid #000000; text-align: right;">
                            {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                        <td style="border: 1px solid #000000; text-align: right;">
                            {{ number_format($item->total_price, 2, ',', '.') }}</td>
                        @endif
                    </tr>
                    @php $childItemNo++; @endphp
                @endforeach
            @endforeach
            {{-- Section subtotal --}}
            <tr>
                <td style="border: 1px solid #000000;"></td>
                <td style="border: 1px solid #000000;"></td>
                <td style="border: 1px solid #000000;"></td>
                <td style="border: 1px solid #000000; font-weight: bold; text-align: right;">JUMLAH {{ $letter }}</td>
                @if($canViewFinancials)
                <td style="border: 1px solid #000000;"></td>
                <td style="border: 1px solid #000000; font-weight: bold; text-align: right; background-color: #ffffcc;">
                    {{ number_format($section->total_price, 2, ',', '.') }}</td>
                @endif
            </tr>
            @php $letterIndex++; @endphp
        @endforeach
        <tr>
            <td colspan="{{ $canViewFinancials ? 5 : 3 }}"
                style="border: 1px solid #000000; font-weight: bold; text-align: right; background-color: #d9edf7;">
                TOTAL</td>
            @if($canViewFinancials)
            <td style="border: 1px solid #000000; font-weight: bold; text-align: right; background-color: #d9edf7;">
                {{ number_format($grandTotal, 2, ',', '.') }}</td>
            @else
            <td style="border: 1px solid #000000; font-weight: bold; text-align: right; background-color: #d9edf7;"></td>
            @endif
        </tr>
    </tbody>
</table>
