@props(['section', 'startDate', 'totalWeeks', 'level' => 0])

@php
    $paddingLeft = $level > 0 ? ($level * 0.75) . 'rem' : '0';
    // Base padding reduced from 2.5rem to 0.5rem (standard px-2)
    $namePaddingLeft = $level > 0 ? (($level * 0.75) + 0.5) . 'rem' : '0.5rem'; 
@endphp

{{-- Section Row --}}
<tr class="bg-gray-100 dark:bg-gray-600 font-semibold">
    <td
        class="border border-gray-300 dark:border-gray-500 px-2 py-1 sticky left-0 bg-gray-100 dark:bg-gray-600 z-10 w-12 text-center dark:text-white">
        {{ $section->code }}
    </td>
    <td class="border border-gray-300 dark:border-gray-500 px-2 py-1 sticky left-10 bg-gray-100 dark:bg-gray-600 z-10 text-gray-900 dark:text-white"
        style="padding-left: {{ $namePaddingLeft }}">
        {{ $section->name }}
    </td>
    <td class="border border-gray-300 dark:border-gray-500 px-2 py-1 text-center text-gray-900 dark:text-white">
        {{ number_format($section->weight_percentage, 2) }}
    </td>
    @for($w = 0; $w < $totalWeeks; $w++)
        <td class="border border-gray-300 dark:border-gray-500"></td>
    @endfor
</tr>

{{-- Items of this Section --}}
@foreach($section->items as $item)
    @include('projects.schedule.partials.scurve-item-row', [
        'item' => $item,
        'numbering' => $item->code,
        'startDate' => $startDate,
        'totalWeeks' => $totalWeeks,
        'paddingLeft' => (($level * 0.75) + 1.25) . 'rem', // Indent items deeper than section (0.5 + 0.75)
        'indent' => ''
    ])
@endforeach

{{-- Recursive Children --}}
@foreach($section->recursiveChildren as $childSection)
    @include('projects.schedule.partials.recursive-scurve-row', [
        'section' => $childSection,
        'startDate' => $startDate,
        'totalWeeks' => $totalWeeks,
        'level' => $level + 1
    ])
@endforeach


