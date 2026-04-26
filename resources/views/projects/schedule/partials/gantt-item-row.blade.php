<tr class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" onclick="showItemDetail({
    name: '{{ addslashes($item->work_name) }}',
    section: '{{ addslashes($sectionName) }}',
    volume: '{{ number_format($item->volume, 2) }}',
    unit: '{{ $item->unit }}',
    weight: '{{ number_format($item->weight_percentage, 2) }}',
    plannedStart: '{{ $item->planned_start ? $item->planned_start->format('d M Y') : '-' }}',
    plannedEnd: '{{ $item->planned_end ? $item->planned_end->format('d M Y') : '-' }}',
    progress: {{ $item->actual_progress ?? 0 }},
    remaining: {{ 100 - ($item->actual_progress ?? 0) }},
    unitPrice: '{{ number_format($item->unit_price, 0, ',', '.') }}',
    totalPrice: '{{ number_format($item->total_price, 0, ',', '.') }}'
})">
    <td class="px-4 py-1 text-sm text-gray-900 dark:text-white sticky left-0 bg-white dark:bg-dark-800 z-10">
        <div class="flex items-center {{ isset($paddingLeft) ? '' : ($indent ?? '') }}"
            style="{{ isset($paddingLeft) ? 'padding-left: ' . $paddingLeft : '' }}">
            <x-heroicon-o-information-circle class="w-4 h-4 mr-2 text-gray-400" />
            {{ $item->code ? $item->code . '. ' : '' }}{{ $item->work_name }}
        </div>
    </td>
    <td class="px-2 py-1 text-xs text-gray-900 dark:text-white text-center">
        {{ number_format($item->weight_percentage, 1) }}%
    </td>
    @foreach($weeks as $weekDate)
        @php
            $weekEnd = $weekDate->copy()->addDays(6);
            $hasPlanned = false;
            $progressWidth = 0;
            $today = now();

            if ($item->planned_start && $item->planned_end) {
                // Check if this week overlaps with planned dates
                $hasPlanned = !($item->planned_end < $weekDate || $item->planned_start > $weekEnd);

                if ($hasPlanned && $item->actual_progress > 0) {
                    // Calculate total weeks for this item
                    $itemStartWeek = max(0, floor($project->start_date->diffInDays($item->planned_start) / 7));
                    $itemEndWeek = floor($project->start_date->diffInDays($item->planned_end) / 7);
                    $totalItemWeeks = max(1, $itemEndWeek - $itemStartWeek + 1);

                    // Current week index (0-based from item start)
                    $currentWeekIndex = floor($project->start_date->diffInDays($weekDate) / 7);
                    $itemWeekPosition = $currentWeekIndex - $itemStartWeek + 1;

                    // Calculate what percentage of the item should be done by this week
                    $expectedProgressByThisWeek = ($itemWeekPosition / $totalItemWeeks) * 100;

                    // If actual progress >= expected, show 100% for this week
                    // If actual progress < expected, show proportional
                    if ($item->actual_progress >= $expectedProgressByThisWeek) {
                        $progressWidth = 100;
                    } elseif ($item->actual_progress >= (($itemWeekPosition - 1) / $totalItemWeeks) * 100) {
                        // This week is partially complete
                        $weekContribution = 100 / $totalItemWeeks;
                        $previousWeeksProgress = (($itemWeekPosition - 1) / $totalItemWeeks) * 100;
                        $progressInThisWeek = $item->actual_progress - $previousWeeksProgress;
                        $progressWidth = min(100, ($progressInThisWeek / $weekContribution) * 100);
                    } else {
                        $progressWidth = 0;
                    }
                }
            }
        @endphp
        <td class="px-0 py-1 relative" style="height: 28px;">
            @if($hasPlanned)
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-full h-3 bg-blue-200 dark:bg-blue-900 rounded-sm mx-0.5">
                        @if($progressWidth > 0)
                            <div class="h-full bg-green-500 dark:bg-green-600 rounded-sm"
                                style="width: {{ min(100, $progressWidth) }}%"></div>
                        @endif
                    </div>
                </div>
            @endif
        </td>
    @endforeach
</tr>