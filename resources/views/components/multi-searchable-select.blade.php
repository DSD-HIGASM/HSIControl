@props(['label' => '', 'options' => [], 'placeholder' => 'Buscar...', 'defaultText' => 'Todos'])

<div 
    x-data="{
        open: false,
        search: '',
        selected: @entangle($attributes->wire('model')),
        options: {{ Js::from($options) }},
        
        get selectedArray() {
            return Array.isArray(this.selected) ? this.selected : [];
        },

        get filteredOptions() {
            const query = this.search.trim();
            if (query === '') return this.options;
            const terms = query.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').split(/\s+/);
            return this.options.filter(opt => {
                const target = opt.name.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                return terms.every(t => target.includes(t));
            });
        },

        isSelected(id) {
            return this.selectedArray.some(item => String(item) === String(id));
        },

        toggle(id) {
            let current = [...this.selectedArray];
            let idx = current.findIndex(item => String(item) === String(id));
            if (idx > -1) {
                current.splice(idx, 1);
            } else {
                current.push(id);
            }
            this.selected = current;
        },

        selectAll() {
            let current = new Set(this.selectedArray.map(String));
            this.filteredOptions.forEach(opt => current.add(String(opt.id)));
            this.selected = Array.from(current);
        },

        clearAll() {
            this.selected = [];
        },

        get summaryText() {
            if (!this.selectedArray || this.selectedArray.length === 0) return '{{ $defaultText }}';
            if (this.selectedArray.length === 1) {
                let found = this.options.find(o => String(o.id) === String(this.selectedArray[0]));
                return found ? found.name : '1 seleccionado';
            }
            return `${this.selectedArray.length} seleccionados`;
        },

        updatePosition() {
            if (!this.open) return;
            const rect = this.$refs.trigger.getBoundingClientRect();
            this.$refs.dropdown.style.top = `${rect.bottom + window.scrollY + 4}px`;
            this.$refs.dropdown.style.left = `${rect.left + window.scrollX}px`;
            this.$refs.dropdown.style.minWidth = `${Math.max(rect.width, 340)}px`;
            this.$refs.dropdown.style.maxWidth = `480px`;
        }
    }"
    @click.outside="open = false"
    x-init="$watch('open', v => { if(v) { search = ''; $nextTick(() => { updatePosition();$refs.sInput?.focus(); }); } })"
    @resize.window="updatePosition()"
    @scroll.window="updatePosition()"
    class="relative w-full"
>
    @if(!empty($label))
        <label class="block text-xs font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1.5 truncate">
            {{ $label }}
        </label>
    @endif

    <div 
        x-ref="trigger"
        @click="open = !open"
        :title="summaryText"
        class="flex items-center justify-between w-full h-[42px] px-3.5 bg-white border border-gray-300 rounded-lg shadow-sm cursor-pointer hover:border-brand-cyan sm:text-xs font-secondary transition-colors"
    >
        <div class="flex items-center gap-2 truncate">
            <span x-text="summaryText" :class="selectedArray.length ? 'font-bold text-gray-900' : 'text-gray-400 font-normal'" class="truncate"></span>
            <span x-show="selectedArray.length > 1" x-text="selectedArray.length" class="px-1.5 py-0.5 text-[10px] font-bold bg-brand-cyan/10 text-brand-cyan-dark rounded-full shrink-0"></span>
        </div>

        <div class="flex items-center gap-1.5 shrink-0 ml-2">
            <button x-show="selectedArray.length > 0" @click.stop="clearAll()" type="button" class="text-gray-400 hover:text-brand-pink p-0.5" title="Limpiar selección">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <svg class="w-4 h-4 text-gray-400 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>

    <template x-teleport="body">
        <div 
            x-ref="dropdown"
            x-show="open"
            x-transition.opacity.duration.100ms
            class="absolute z-[9999] bg-white border border-gray-200 rounded-xl shadow-2xl overflow-hidden font-secondary text-xs"
            style="display: none;"
        >
            <div class="p-2 border-b border-gray-100 bg-gray-50 flex items-center justify-between gap-2">
                <div class="relative flex-1">
                    <input 
                        x-ref="sInput"
                        type="text" 
                        x-model="search"
                        placeholder="{{ $placeholder }}"
                        class="w-full text-xs pl-7 pr-2 py-1.5 rounded-md border-gray-300 focus:border-brand-cyan focus:ring-0 font-secondary"
                    >
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <div class="flex items-center gap-2 shrink-0 pr-1 text-[11px]">
                    <button type="button" @click="selectAll()" class="font-bold text-brand-cyan hover:underline">Todos</button>
                    <button type="button" @click="clearAll()" class="font-bold text-gray-400 hover:text-brand-pink hover:underline">Limpiar</button>
                </div>
            </div>

            <ul class="max-h-60 overflow-y-auto p-1 divide-y divide-gray-50">
                <template x-for="opt in filteredOptions" :key="opt.id">
                    <li 
                        @click="toggle(opt.id)"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-cyan-50/60 cursor-pointer select-none transition-colors"
                        :class="isSelected(opt.id) ? 'bg-cyan-50/40 text-brand-cyan-dark font-bold' : 'text-gray-700'"
                    >
                        <input type="checkbox" :checked="isSelected(opt.id)" class="rounded border-gray-300 text-brand-cyan focus:ring-0 w-4 h-4 pointer-events-none">
                        <span x-text="opt.name" class="block leading-snug"></span>
                    </li>
                </template>
                <li x-show="filteredOptions.length === 0" class="py-3 text-center text-gray-400 text-xs">Sin coincidencias</li>
            </ul>
        </div>
    </template>
</div>