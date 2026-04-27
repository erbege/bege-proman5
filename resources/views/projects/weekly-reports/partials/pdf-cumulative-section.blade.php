{{-- PDF Recursive partial for cumulative section --}}
@php
    $indent = str_repeat('    ', $level);
@endphp

{{-- Section Header --}}
<tr class="section-row">
    <td colspan="11">{{ $indent }}{{ $section['code'] }} {{ $section['name'] }}</td>
</tr>

{{-- Section Items --}}
@foreach($section['items'] as $item)
    <tr>
        <td>{{ $indent }} {{ $item['code'] }} {{ $item['work_name'] }}</td>
        <td class="text-center">{{ number_format($item['weight'], 4) }}</td>
        <td class="text-center bg-blue">{{ number_format($item['planned']['up_to_prev'], 4) }}</td>
        <td class="text-center bg-blue">{{ number_format($item['planned']['current'], 4) }}</td>
        <td class="text-center bg-blue">{{ number_format($item['planned']['cumulative'], 4) }}</td>
        <td class="text-center bg-green">{{ number_format($item['actual']['up_to_prev'], 4) }}</td>
        <td class="text-center bg-green">{{ number_format($item['actual']['current'], 4) }}</td>
        <td class="text-center bg-green">{{ number_format($item['actual']['cumulative'], 4) }}</td>
        <td class="text-center bg-yellow {{ $item['deviation']['up_to_prev'] >= 0 ? 'text-positive' : 'text-negative' }}">
            {{ number_format($item['deviation']['up_to_prev'], 4) }}</td>
        <td class="text-center bg-yellow {{ $item['deviation']['current'] >= 0 ? 'text-positive' : 'text-negative' }}">
            {{ number_format($item['deviation']['current'], 4) }}</td>
        <td class="text-center bg-yellow {{ $item['deviation']['cumulative'] >= 0 ? 'text-positive' : 'text-negative' }}">
            {{ number_format($item['deviation']['cumulative'], 4) }}</td>
    </tr>
@endforeach

{{-- Recursive Children --}}
@if(isset($section['children']) && count($section['children']) > 0)
    @foreach($section['children'] as $child)
        @include('projects.weekly-reports.partials.pdf-cumulative-section', ['section' => $child, 'level' => $level + 1])
    @endforeach
@endif


