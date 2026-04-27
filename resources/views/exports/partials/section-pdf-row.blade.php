@props(['section', 'startDate', 'totalWeeks', 'level' => 0])

<!-- Section Row -->
<tr class="section-row">
    <td>{{ $section->code }}</td>
    <td class="text-left">{{ $section->name }}</td>
    <td>{{ number_format($section->weight_percentage, 1) }}</td>
    @for($w = 0; $w < $totalWeeks; $w++)
        <td></td>
    @endfor
</tr>

<!-- Items of this Section -->
@foreach($section->items as $item)
    <tr class="item-row">
        <td>{{ $item->code }}</td>
        <td class="text-left">{{ $item->work_name }}</td>
        <td>{{ number_format($item->weight_percentage, 1) }}</td>
        @for($w = 0; $w < $totalWeeks; $w++)
            @php
                $weekDate = $startDate->copy()->addWeeks($w);
                $weekEnd = $weekDate->copy()->addDays(6);
                $isPlanned = $item->planned_start && $item->planned_end &&
                    !($item->planned_end < $weekDate || $item->planned_start > $weekEnd);
            @endphp
            <td>
                @if($isPlanned)
                    <div class="bar"></div>
                @endif
            </td>
        @endfor
    </tr>
@endforeach

<!-- Recursive Children -->
@foreach($section->recursiveChildren as $childSection)
    @include('exports.partials.section-pdf-row', [
        'section' => $childSection,
        'startDate' => $startDate,
        'totalWeeks' => $totalWeeks,
        'level' => $level + 1
    ])
@endforeach


