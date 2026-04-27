<table>
    <thead>
        <tr>
            <th colspan="6" style="font-weight: bold; font-size: 16px; text-align: center;">ANALISA HARGA SATUAN
                PEKERJAAN</th>
        </tr>
        <tr>
            <th colspan="6" style="font-weight: bold; text-align: center;">{{ $project->name }}</th>
        </tr>
        <tr>
            <th colspan="6"></th>
        </tr>
    </thead>
    <tbody>
        @forelse($ahspItems as $item)
            @php
                $workType = $item->ahspWorkType;
                $hasAhspData = $workType && $workType->components->isNotEmpty();
            @endphp

            {{-- Work Item Header --}}
            <tr>
                <td colspan="6" style="font-weight: bold; background-color: #e8e8e8; border: 1px solid #000000;">
                    {{ $item->section?->name }} - {{ $item->work_name }}
                </td>
            </tr>
            <tr>
                <td colspan="6" style="border: 1px solid #000000;">
                    Kode: {{ $workType?->code ?? $item->code }} | Satuan: {{ $item->unit }} | Volume:
                    {{ number_format($item->volume, 2, ',', '.') }}
                </td>
            </tr>

            {{-- Components Header --}}
            <tr>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f5f5f5;">NO
                </th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f5f5f5;">
                    URAIAN</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f5f5f5;">
                    KOEFISIEN</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f5f5f5;">
                    SATUAN</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f5f5f5;">
                    HARGA SATUAN</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f5f5f5;">
                    JUMLAH</th>
            </tr>

            @if($hasAhspData)
                {{-- Get components by type from ahsp_components table --}}
                @php
                    $materials = $workType->components->where('component_type', 'material');
                    $labor = $workType->components->where('component_type', 'labor');
                    $equipment = $workType->components->where('component_type', 'equipment');

                    // Calculate prices - try to get from base prices or estimate from unit_price
                    $totalCalculated = 0;
                @endphp

                {{-- A. BAHAN (Materials) --}}
                @if($materials->isNotEmpty())
                    <tr>
                        <td colspan="6" style="border: 1px solid #000000; font-weight: bold; font-style: italic;">A. BAHAN</td>
                    </tr>
                    @php $matNo = 1; @endphp
                    @foreach($materials as $comp)
                        @php
                            // Try to get price from base prices (DEFAULT region)
                            $unitPrice = $comp->getPrice('DEFAULT');
                            $amount = $comp->coefficient * $unitPrice;
                            $totalCalculated += $amount;
                        @endphp
                        <tr>
                            <td style="border: 1px solid #000000; text-align: center;">{{ $matNo++ }}</td>
                            <td style="border: 1px solid #000000;">{{ $comp->name }}</td>
                            <td style="border: 1px solid #000000; text-align: right;">
                                {{ number_format($comp->coefficient, 4, ',', '.') }}</td>
                            <td style="border: 1px solid #000000; text-align: center;">{{ $comp->unit }}</td>
                            <td style="border: 1px solid #000000; text-align: right;">
                                {{ $unitPrice > 0 ? number_format($unitPrice, 2, ',', '.') : '-' }}</td>
                            <td style="border: 1px solid #000000; text-align: right;">
                                {{ $amount > 0 ? number_format($amount, 2, ',', '.') : '-' }}</td>
                        </tr>
                    @endforeach
                @endif

                {{-- B. TENAGA KERJA (Labor) --}}
                @if($labor->isNotEmpty())
                    <tr>
                        <td colspan="6" style="border: 1px solid #000000; font-weight: bold; font-style: italic;">B. TENAGA KERJA
                        </td>
                    </tr>
                    @php $labNo = 1; @endphp
                    @foreach($labor as $comp)
                        @php
                            $unitPrice = $comp->getPrice('DEFAULT');
                            $amount = $comp->coefficient * $unitPrice;
                            $totalCalculated += $amount;
                        @endphp
                        <tr>
                            <td style="border: 1px solid #000000; text-align: center;">{{ $labNo++ }}</td>
                            <td style="border: 1px solid #000000;">{{ $comp->name }}</td>
                            <td style="border: 1px solid #000000; text-align: right;">
                                {{ number_format($comp->coefficient, 4, ',', '.') }}</td>
                            <td style="border: 1px solid #000000; text-align: center;">{{ $comp->unit }}</td>
                            <td style="border: 1px solid #000000; text-align: right;">
                                {{ $unitPrice > 0 ? number_format($unitPrice, 2, ',', '.') : '-' }}</td>
                            <td style="border: 1px solid #000000; text-align: right;">
                                {{ $amount > 0 ? number_format($amount, 2, ',', '.') : '-' }}</td>
                        </tr>
                    @endforeach
                @endif

                {{-- C. PERALATAN (Equipment) --}}
                @if($equipment->isNotEmpty())
                    <tr>
                        <td colspan="6" style="border: 1px solid #000000; font-weight: bold; font-style: italic;">C. PERALATAN</td>
                    </tr>
                    @php $eqNo = 1; @endphp
                    @foreach($equipment as $comp)
                        @php
                            $unitPrice = $comp->getPrice('DEFAULT');
                            $amount = $comp->coefficient * $unitPrice;
                            $totalCalculated += $amount;
                        @endphp
                        <tr>
                            <td style="border: 1px solid #000000; text-align: center;">{{ $eqNo++ }}</td>
                            <td style="border: 1px solid #000000;">{{ $comp->name }}</td>
                            <td style="border: 1px solid #000000; text-align: right;">
                                {{ number_format($comp->coefficient, 4, ',', '.') }}</td>
                            <td style="border: 1px solid #000000; text-align: center;">{{ $comp->unit }}</td>
                            <td style="border: 1px solid #000000; text-align: right;">
                                {{ $unitPrice > 0 ? number_format($unitPrice, 2, ',', '.') : '-' }}</td>
                            <td style="border: 1px solid #000000; text-align: right;">
                                {{ $amount > 0 ? number_format($amount, 2, ',', '.') : '-' }}</td>
                        </tr>
                    @endforeach
                @endif

                {{-- Overhead if exists --}}
                @if($workType->overhead_percentage > 0)
                    @php
                        $overheadAmount = $totalCalculated * ($workType->overhead_percentage / 100);
                    @endphp
                    <tr>
                        <td colspan="5" style="border: 1px solid #000000; text-align: right; font-style: italic;">Overhead & Profit
                            ({{ number_format($workType->overhead_percentage, 0) }}%)</td>
                        <td style="border: 1px solid #000000; text-align: right; font-style: italic;">
                            {{ $overheadAmount > 0 ? number_format($overheadAmount, 2, ',', '.') : '-' }}</td>
                    </tr>
                @endif
            @else
                <tr>
                    <td colspan="6" style="border: 1px solid #000000; text-align: center; font-style: italic; color: #666;">
                        Data analisa AHSP tidak tersedia (item tidak terhubung dengan AHSP)
                    </td>
                </tr>
            @endif

            {{-- Unit Price Total --}}
            <tr>
                <td colspan="5"
                    style="border: 1px solid #000000; font-weight: bold; text-align: right; background-color: #ffffcc;">
                    HARGA SATUAN</td>
                <td style="border: 1px solid #000000; font-weight: bold; text-align: right; background-color: #ffffcc;">
                    {{ number_format($item->unit_price, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5"
                    style="border: 1px solid #000000; font-weight: bold; text-align: right; background-color: #d9edf7;">
                    JUMLAH ({{ number_format($item->volume, 2, ',', '.') }} x
                    {{ number_format($item->unit_price, 2, ',', '.') }})</td>
                <td style="border: 1px solid #000000; font-weight: bold; text-align: right; background-color: #d9edf7;">
                    {{ number_format($item->total_price, 2, ',', '.') }}</td>
            </tr>

            {{-- Spacing row --}}
            <tr>
                <td colspan="6"></td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="border: 1px solid #000000; text-align: center; font-style: italic;">
                    Tidak ada item pekerjaan dalam RAB ini
                </td>
            </tr>
        @endforelse
    </tbody>
</table>


