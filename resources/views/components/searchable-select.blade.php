@props(['label' => 'Seleccione', 'options' => [], 'placeholder' => 'Buscar...', 'defaultText' => 'Sin Asignar'])

<div 
    x-data="{
        open: false,
        search: '',
        selectedId: @entangle($attributes->wire('model')),
        options: {{ Js::from($options) }},
        highlightedIndex: 0,
        
        get filteredOptions() {
            const query = this.search.trim();
            if (query === '') return this.options;
            
            const searchTerms = query.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').split(/\s+/);
            
            return this.options.filter(opt => {
                const targetName = opt.name.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                return searchTerms.every(term => targetName.includes(term));
            });
        },
        
        syncText() {
            let selected = this.options.find(o => o.id == this.selectedId);
            this.search = selected ? selected.name : '';
        },

        init() {
            this.syncText();
            this.$watch('selectedId', () => {
                this.syncText();
            });
        },
        
        selectOption(option) {
            if (!option || option.id === '') {
                this.selectedId = ''; 
                this.search = '';
            } else {
                this.selectedId = option.id;
                this.search = option.name;
            }
            this.open = false;
        },
        
        close() {
            this.open = false;
            this.syncText();
            this.highlightedIndex = 0;
        },

        updatePosition() {
            if(!this.open) return;
            const inputRect = this.$refs.input.getBoundingClientRect();
            this.$refs.dropdown.style.top = `${inputRect.bottom + window.scrollY}px`;
            this.$refs.dropdown.style.left = `${inputRect.left + window.scrollX}px`;
            this.$refs.dropdown.style.width = `${inputRect.width}px`;
        }
    }"
    @click.outside="close()"
    x-init="$watch('open', value => { if(value) { $nextTick(() => updatePosition()); } })"
    @resize.window="updatePosition()"
    @scroll.window="updatePosition()"
    class="relative w-full"
>
    <label class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">
        {{ $label }}
    </label>

    <div class="relative">
        <input 
            x-ref="input"
            type="text" 
            x-model="search"
            @focus="open = true; search = ''"
            @keydown.arrow-down.prevent="if(open) highlightedIndex = Math.min(highlightedIndex + 1, filteredOptions.length - 1); else open = true;"
            @keydown.arrow-up.prevent="highlightedIndex = Math.max(highlightedIndex - 1, 0)"
            @keydown.enter.prevent="if(open) selectOption(filteredOptions[highlightedIndex])"
            @keydown.escape.prevent="close()"
            @keydown.tab="close()"
            placeholder="{{ $placeholder }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring focus:ring-brand-cyan focus:ring-opacity-50 sm:text-sm font-secondary bg-white"
        >
        
        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>

    <template x-teleport="body">
        <ul 
            x-ref="dropdown"
            x-show="open"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute z-[9999] bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto font-secondary text-sm mt-1"
            style="display: none;"
        >
            <li 
                @mousedown.prevent="selectOption({id: '', name: '{{ $defaultText }}'})"
                @mouseenter="highlightedIndex = -1"
                :class="{'bg-gray-100 text-gray-900': highlightedIndex === -1, 'text-gray-500': highlightedIndex !== -1}"
                class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors italic"
            >
                {{ $defaultText }}
            </li>

            <template x-for="(option, index) in filteredOptions" :key="option.id">
                <li 
                    @mousedown.prevent="selectOption(option)"
                    @mouseenter="highlightedIndex = index"
                    :class="{
                        'bg-gray-100 text-gray-900': highlightedIndex === index, 
                        'text-gray-900': highlightedIndex !== index,
                        'font-bold': selectedId === option.id
                    }"
                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors"
                >
                    <span x-text="option.name" class="block truncate"></span>
                    
                    <span x-show="selectedId === option.id" class="text-brand-cyan absolute inset-y-0 right-0 flex items-center pr-4">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </span>
                </li>
            </template>

            <li x-show="filteredOptions.length === 0" class="py-2 pl-3 pr-9 text-gray-500 cursor-default">
                No se encontraron coincidencias...
            </li>
        </ul>
    </template>
</div>