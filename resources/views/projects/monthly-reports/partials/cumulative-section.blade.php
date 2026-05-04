{{-- Recursive partial for cumulative section --}}
@php
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
@endphp

{{-- Section Header --}}
<tr class="bg-gray-50 dark:bg-dark-700 font-semibold">
    <td colspan="11" class="px-3 py-2 border text-gray-900 dark:text-white">
        {!! $indent !!}{{ $section['code'] }} {{ $section['name'] }}
    </td>
</tr>

{{-- Section Items --}}
@foreach($section['items'] as $item)
    <tr class="hover:bg-gray-50 dark:hover:bg-dark-700">
        <td class="px-3 py-1 border text-xs text-gray-900 dark:text-gray-200">
            {!! $indent !!}&nbsp;&nbsp;&nbsp;&nbsp;{{ $item['code'] }} {{ $item['work_name'] ?? $item['name'] ?? '' }}
        </td>
        <td class="px-2 py-1 text-center border text-xs text-gray-900 dark:text-gray-200">
            {{ number_format($item['weight'], 4) }}
        </td>
        <td
            class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-blue-700 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/50">
            {{ number_format($item['planned']['up_to_prev'], 4) }}
        </td>
        <td
            class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-blue-700 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/50">
            {{ number_format($item['planned']['current'], 4) }}
        </td>
        <td
            class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-blue-700 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/50">
            {{ number_format($item['planned']['cumulative'], 4) }}
        </td>
        <td
            class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-green-700 dark:text-green-200 bg-green-50 dark:bg-green-900/50">
            {{ number_format($item['actual']['up_to_prev'], 4) }}
        </td>
        <td
            class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-green-700 dark:text-green-200 bg-green-50 dark:bg-green-900/50">
            {{ number_format($item['actual']['current'], 4) }}
        </td>
        <td
            class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-green-700 dark:text-green-200 bg-green-50 dark:bg-green-900/50">
            {{ number_format($item['actual']['cumulative'], 4) }}
        </td>
        <td
            class="px-2 py-1 text-center border dark:border-gray-600 text-xs bg-yellow-50 dark:bg-yellow-900/50 {{ $item['deviation']['up_to_prev'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
            {{ number_format($item['deviation']['up_to_prev'], 4) }}
        </td>
        <td
            class="px-2 py-1 text-center border dark:border-gray-600 text-xs bg-yellow-50 dark:bg-yellow-900/50 {{ $item['deviation']['current'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
            {{ number_format($item['deviation']['current'], 4) }}
        </td>
        <td
            class="px-2 py-1 text-center border dark:border-gray-600 text-xs bg-yellow-50 dark:bg-yellow-900/50 {{ $item['deviation']['cumulative'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
            {{ number_format($item['deviation']['cumulative'], 4) }}
        </td>
    </tr>
@endforeach

{{-- Recursive Children --}}
@if(isset($section['children']) && count($section['children']) > 0)
    @foreach($section['children'] as $child)
        @include('projects.monthly-reports.partials.cumulative-section', ['section' => $child, 'level' => $level + 1])
    @endforeach
@endif


