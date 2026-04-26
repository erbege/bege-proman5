@props(['section', 'weeks', 'project', 'level' => 0])

{{-- Section Row --}}
@php
    $paddingLeft = $level > 0 ? ($level * 0.75) . 'rem' : '0';
@endphp
<tr class="bg-gray-100 dark:bg-gray-600">
    <td class="px-4 py-2 font-semibold text-gray-900 dark:text-white sticky left-0 bg-gray-100 dark:bg-gray-600 z-10 
        {{ $level > 0 ? 'border-l-4 border-gray-300 dark:border-dark-600' : '' }}" colspan="2"
        style="padding-left: {{ $paddingLeft }};">
        {{ $section->code }}. {{ $section->name }}
    </td>
    @foreach($weeks as $week)
        <td class="bg-gray-100 dark:bg-gray-600"></td>
    @endforeach
</tr>

{{-- Items of this Section --}}
@foreach($section->items as $item)
    @include('projects.schedule.partials.gantt-item-row', [
        'item' => $item,
        'weeks' => $weeks,
        'project' => $project,
        'sectionName' => $section->name,
        'paddingLeft' => (($level * 0.75) + 1.5) . 'rem', // Indent items deeper than section
        'indent' => ''
    ])
@endforeach

{{-- Recursive Children --}}
@foreach($section->recursiveChildren as $childSection)
    @include('projects.schedule.partials.recursive-gantt-row', [
        'section' => $childSection,
        'weeks' => $weeks,
        'project' => $project,
        'level' => $level + 1
    ])
@endforeach
