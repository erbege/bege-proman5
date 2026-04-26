@props(['options', 'optionsValue' => 'id', 'optionsLabel' => 'name', 'placeholder' => 'Select Option', 'name' => null])

<div x-data="{
        open: false,
        search: '',
        value: null,
        initialOptions: {{ \Illuminate\Support\Js::from($options) }},
        optionsLabel: '{{ $optionsLabel }}',
        optionsValue: '{{ $optionsValue }}',
        get filteredOptions() {
            if (this.search === '') return this.initialOptions;
            const terms = this.search.toLowerCase().split(/\s+/).filter(t => t.length > 0);
            return this.initialOptions.filter(option => {
                const label = option[this.optionsLabel] ? String(option[this.optionsLabel]).toLowerCase() : '';
                return terms.every(term => label.includes(term));
            });
        },
        get selectedLabel() {
            if (!this.value) return '{{ $placeholder }}';
            const option = this.initialOptions.find(o => o[this.optionsValue] == this.value);
            return option ? option[this.optionsLabel] : '{{ $placeholder }}';
        },
        select(option) {
            this.value = option[this.optionsValue];
            this.open = false;
            this.search = '';
        },
        toggle() {
            if (this.open) {
                this.open = false;
            } else {
                this.calculatePosition();
                this.open = true;
                this.$nextTick(() => this.$refs.searchInput.focus());
            }
        },
        calculatePosition() {
            const rect = this.$refs.button.getBoundingClientRect();
            this.top = rect.bottom + window.scrollY;
            this.left = rect.left + window.scrollX;
            this.width = rect.width;
        },
        top: 0,
        left: 0,
        width: 0
    }" x-init="$watch('open', value => { if(value) calculatePosition(); })" x-modelable="value" {{ $attributes->except(['name', 'x-bind:name'])->merge(['class' => 'relative w-full']) }} @click.outside="open = false">
    <!-- Hidden Input for Form Submission -->
    <input type="hidden" @if($name) name="{{ $name }}" @endif {{ $attributes->only('x-bind:name') }} :value="value">

    <!-- Trigger Button -->
    <button type="button" x-ref="button" @click="toggle()"
        class="relative w-full py-2 pl-3 pr-10 text-left bg-white dark:bg-dark-900 border border-gray-300 dark:border-dark-700 rounded-md shadow-sm cursor-pointer focus:outline-none focus:ring-1 focus:ring-gold-500 focus:border-gold-500 sm:text-sm">
        <span class="block truncate text-gray-700 dark:text-gray-300" x-text="selectedLabel"></span>
        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </span>
    </button>

    <!-- Dropdown Menu (Teleported) -->
    <template x-teleport="body">
        <div x-show="open" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute z-50 mt-1 bg-white dark:bg-dark-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
            :style="`top: ${top}px; left: ${left}px; width: ${width}px;`">
            <!-- Search Input -->
            <div
                class="sticky top-0 z-10 bg-white dark:bg-dark-800 px-3 py-2 border-b border-gray-200 dark:border-dark-700">
                <input type="text" x-ref="searchInput" x-model="search" placeholder="Cari..."
                    class="w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 rounded-md shadow-sm focus:border-gold-500 focus:ring-gold-500 text-sm"
                    @keydown.enter.prevent="select(filteredOptions[0])">
            </div>

            <!-- Options List -->
            <ul class="pt-1">
                <template x-for="option in filteredOptions" :key="option[optionsValue]">
                    <li @click="select(option)"
                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gold-500 hover:text-white dark:hover:bg-gold-600 group"
                        :class="{'bg-gold-100 dark:bg-gold-900 text-gold-900 dark:text-gold-100': value == option[optionsValue], 'text-gray-900 dark:text-gray-300': value != option[optionsValue]}">
                        <span class="block truncate font-normal" x-text="option[optionsLabel]"></span>
                        <span x-show="value == option[optionsValue]"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-gold-600 dark:text-gold-400 group-hover:text-white">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                </template>
                <div x-show="filteredOptions.length === 0"
                    class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 italic">
                    Tidak ada hasil ditemukan.
                </div>
            </ul>
        </div>
    </template>
</div>