<tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
    <td
        class="border border-gray-200 dark:border-dark-600 px-2 py-1 text-center sticky left-0 bg-white dark:bg-dark-800 z-10 text-gray-900 dark:text-gray-300">
        {{ $numbering }}
    </td>
    <td class="border border-gray-200 dark:border-dark-600 px-2 py-1 sticky left-10 bg-white dark:bg-dark-800 z-10 text-gray-700 dark:text-gray-300 {{ isset($paddingLeft) ? '' : ($indent ?? '') }}"
        style="{{ isset($paddingLeft) ? 'padding-left: ' . $paddingLeft : '' }}">
        {{ $item->work_name }}
    </td>
    <td class="border border-gray-200 dark:border-dark-600 px-2 py-1 text-center text-gray-900 dark:text-white">
        {{ number_format($item->weight_percentage, 1) }}
    </td>
    @for($w = 0; $w < $totalWeeks; $w++)
        @php
            $weekDate = $startDate->copy()->addWeeks($w);
            $weekEnd = $weekDate->copy()->addDays(6);
            $isPlanned = $item->planned_start && $item->planned_end &&
                !($item->planned_end < $weekDate || $item->planned_start > $weekEnd);
        @endphp
        <td class="border border-gray-200 dark:border-dark-600 px-0 py-0 text-center {{ $isPlanned ? 'bg-blue-100 dark:bg-blue-900' : '' }}"
            style="height: 20px;">
            @if($isPlanned)
                <div class="w-full h-2 bg-blue-400 dark:bg-blue-600 mx-auto rounded-sm"></div>
            @endif
        </td>
    @endfor
</tr>


