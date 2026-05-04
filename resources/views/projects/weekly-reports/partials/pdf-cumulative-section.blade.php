{{-- PUPR Recursive partial for cumulative progress section --}}

{{-- Section Header Row --}}
<tr class="section-row">
    <td class="text-center bold" colspan="12" style="text-align: left; padding-left: {{ $level * 10 }}pt;">
        {{ $section['code'] }}. {{ $section['name'] }}
    </td>
</tr>

{{-- Section Items --}}
@foreach($section['items'] as $item)
    @php $rowNum++; @endphp
    <tr>
        <td class="text-center">{{ $rowNum }}</td>
        <td style="padding-left: {{ ($level + 1) * 8 }}pt;">{{ $item['code'] }} {{ $item['work_name'] }}</td>
        <td class="text-center">{{ number_format($item['weight'], 2) }}</td>
        <td class="text-center">{{ number_format($item['planned']['up_to_prev'], 2) }}</td>
        <td class="text-center">{{ number_format($item['actual']['up_to_prev'], 2) }}</td>
        <td class="text-center">{{ number_format($item['planned']['current'], 2) }}</td>
        <td class="text-center">{{ number_format($item['actual']['current'], 2) }}</td>
        <td class="text-center">{{ number_format($item['planned']['cumulative'], 2) }}</td>
        <td class="text-center">{{ number_format($item['actual']['cumulative'], 2) }}</td>
        <td class="text-center {{ $item['deviation']['up_to_prev'] >= 0 ? 'val-pos' : 'val-neg' }}">
            {{ number_format($item['deviation']['up_to_prev'], 2) }}</td>
        <td class="text-center {{ $item['deviation']['current'] >= 0 ? 'val-pos' : 'val-neg' }}">
            {{ number_format($item['deviation']['current'], 2) }}</td>
        <td class="text-center {{ $item['deviation']['cumulative'] >= 0 ? 'val-pos' : 'val-neg' }}">
            {{ number_format($item['deviation']['cumulative'], 2) }}</td>
    </tr>
@endforeach

{{-- Recursive Children --}}
@if(isset($section['children']) && count($section['children']) > 0)
    @foreach($section['children'] as $child)
        @include('projects.weekly-reports.partials.pdf-cumulative-section', ['section' => $child, 'level' => $level + 1, 'rowNum' => &$rowNum])
    @endforeach
@endif
