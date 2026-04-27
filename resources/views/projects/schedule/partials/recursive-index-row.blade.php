@props(['section', 'project', 'canEditSchedule'])

{{-- Section Row --}}
<tr class="bg-gray-100 dark:bg-gray-700">
    <td colspan="{{ $canEditSchedule ? 8 : 6 }}"
        class="px-3 py-1.5 text-xs font-black uppercase text-gray-700 dark:text-gray-200 {{ $section->level > 0 ? 'border-l-4 border-gray-300 dark:border-dark-600' : '' }}"
        style="padding-left: {{ ($section->level * 0.75) + 0.75 }}rem;">
        {{ $section->code }}. {{ $section->name }}
    </td>
</tr>

{{-- Items of this Section --}}
@foreach($section->items as $itemIndex => $item)
    @include('projects.schedule.partials.item-row', [
        'item' => $item,
        'project' => $project,
        'canEditSchedule' => $canEditSchedule,
        'indent' => '', // Indentation handled by style in item-row or we need to pass a style
        'paddingLeft' => ($section->level * 0.75) + 2 . 'rem',
        'isFirst' => $itemIndex === 0
    ])
@endforeach

{{-- Recursive Children --}}
@foreach($section->recursiveChildren as $childSection)
    @include('projects.schedule.partials.recursive-index-row', ['section' => $childSection, 'project' => $project, 'canEditSchedule' => $canEditSchedule])
@endforeach


