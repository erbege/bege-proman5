<tr class="hover:bg-gray-50 dark:hover:bg-gray-700" data-item-id="{{ $item->id }}">
    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white {{ isset($paddingLeft) ? '' : ($indent ?? 'pl-8') }}"
        style="{{ isset($paddingLeft) ? 'padding-left: ' . $paddingLeft : '' }}">
        {{ $item->work_name }}
        @if($item->can_parallel)
            <span
                class="ml-1 inline-flex items-center px-1 py-0.5 text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 rounded">
                <x-heroicon-o-arrows-right-left class="w-2.5 h-2.5" />
            </span>
        @endif
    </td>
    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white text-center">
        {{ number_format($item->weight_percentage, 2) }}%
    </td>
    @if($canEditSchedule)
        <td class="px-4 py-2 text-center">
            <label class="relative inline-flex items-center cursor-pointer"
                title="Dapat dikerjakan paralel dengan item sebelumnya">
                <input type="checkbox" class="sr-only peer parallel-toggle" data-item-id="{{ $item->id }}"
                    data-project-id="{{ $project->id }}" {{ $item->can_parallel ? 'checked' : '' }} {{ ($isFirst ?? false) ? 'disabled' : '' }}>
                <div
                    class="w-8 h-4 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-300 dark:peer-focus:ring-purple-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600 {{ ($isFirst ?? false) ? 'opacity-30 cursor-not-allowed' : '' }}">
                </div>
            </label>
        </td>
    @endif
    <td class="px-4 py-2 text-sm text-center">
        @if($canEditSchedule)
            <input type="date"
                class="schedule-input planned-start w-28 text-xs text-center bg-transparent border border-transparent hover:border-gray-300 dark:hover:border-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded px-1 py-0.5 text-gray-900 dark:text-white dark:bg-dark-700"
                value="{{ $item->planned_start?->format('Y-m-d') }}"
                data-original="{{ $item->planned_start?->format('Y-m-d') }}" data-item-id="{{ $item->id }}">
        @else
            {{ $item->planned_start ? $item->planned_start->format('d/m/y') : '-' }}
        @endif
    </td>
    <td class="px-4 py-2 text-sm text-center">
        @if($canEditSchedule)
            <input type="date"
                class="schedule-input planned-end w-28 text-xs text-center bg-transparent border border-transparent hover:border-gray-300 dark:hover:border-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded px-1 py-0.5 text-gray-900 dark:text-white dark:bg-dark-700"
                value="{{ $item->planned_end?->format('Y-m-d') }}"
                data-original="{{ $item->planned_end?->format('Y-m-d') }}" data-item-id="{{ $item->id }}">
        @else
            {{ $item->planned_end ? $item->planned_end->format('d/m/y') : '-' }}
        @endif
    </td>
    <td class="px-4 py-2 text-center">
        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $item->actual_progress }}%"></div>
        </div>
        <span class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($item->actual_progress, 1) }}%</span>
    </td>
    <td class="px-4 py-2 text-center">
        @if($item->actual_progress >= 100)
            <span
                class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Selesai</span>
        @elseif($item->actual_progress > 0)
            <span
                class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">Berjalan</span>
        @else
            <span
                class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-300">Belum</span>
        @endif
    </td>
    @if($canEditSchedule)
        <td class="px-4 py-2 text-center">
            <button type="button"
                class="save-schedule-btn hidden inline-flex items-center px-2 py-1 bg-green-600 border border-transparent rounded text-xs text-white hover:bg-green-700 focus:outline-none"
                data-item-id="{{ $item->id }}" data-project-id="{{ $project->id }}">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Save
            </button>
        </td>
    @endif
</tr>