<div> <div class="max-w-7xl mx-auto space-y-6">
        
        <style>
            .board-scrollbar::-webkit-scrollbar { height: 10px; }
            .board-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; margin: 0 10px; }
            .board-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
            .board-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        </style>

        <div class="bg-white p-6 sm:px-8 rounded-xl shadow-sm border border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 font-secondary uppercase tracking-wide">
                        Tablero Estructural Funcional
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Arrastra el fondo para desplazarte. Las columnas organizan las unidades por su nivel de profundidad funcional.</p>
                </div>
                <button wire:click="openPanel" class="shrink-0 inline-flex justify-center items-center px-5 py-2 text-sm font-bold rounded-md text-white bg-brand-cyan hover:bg-cyan-600 transition-colors uppercase tracking-wide gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nueva Unidad
                </button>
            </div>

            @if (session('status'))
                <div class="mt-6 px-4 py-3 bg-green-50 text-green-700 border border-green-200 rounded-md text-sm font-bold flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('status') }}
                </div>
            @endif
        </div>

        <div 
            x-data="hospitalBoard()" 
            class="bg-slate-50 border border-gray-200 rounded-xl pt-6 overflow-hidden"
        >
            <div 
                x-ref="scrollContainer"
                @mousedown="startDrag"
                @mouseleave="endDrag"
                @mouseup="endDrag"
                @mousemove="onDrag"
                class="flex flex-nowrap overflow-x-auto px-6 pb-6 gap-4 snap-x cursor-grab active:cursor-grabbing board-scrollbar" 
                style="min-height: 60vh;"
            >
                @foreach($groupedUnits as $level => $units)
                    <div wire:key="col-level-{{ $level }}" class="snap-start shrink-0 w-72 bg-gray-100/60 rounded-xl p-3 border border-gray-200 flex flex-col gap-3">
                        
                        <div class="flex items-center justify-between border-b border-gray-200 pb-2 mb-1 px-1">
                            <h3 class="font-bold text-gray-700 font-secondary uppercase tracking-wide text-[11px] flex items-center gap-2">
                                @if($level == 99)
                                    ⚠️ Error de Bucle
                                @else
                                    <span class="bg-gray-200 text-gray-500 px-1.5 rounded text-[9px]">L{{ $level }}</span>
                                    Nivel {{ $level }}
                                @endif
                            </h3>
                            <span class="bg-gray-200 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                {{ count($units) }}
                            </span>
                        </div>

                        @foreach($units as $unit)
                            <div 
                                wire:key="unit-{{ $unit->id }}"
                                @mouseenter="hoverNode({{ $unit->id }})"
                                @mouseleave="clearHover()"
                                @click="$wire.openPanel({{ $unit->id }})"
                                :class="getNodeClass({{ $unit->id }})"
                                class="relative bg-white p-3.5 rounded-lg shadow-sm border transition-all duration-200 cursor-pointer group select-none"
                            >
                                <span x-show="isParent({{ $unit->id }})" x-cloak class="absolute -top-2.5 right-2 bg-amber-100 text-amber-800 border border-amber-200 text-[9px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider shadow-sm z-10 transition-opacity">
                                    Padre
                                </span>
                                <span x-show="isChild({{ $unit->id }})" x-cloak class="absolute -top-2.5 right-2 bg-emerald-100 text-emerald-800 border border-emerald-200 text-[9px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider shadow-sm z-10 transition-opacity">
                                    Hijo
                                </span>

                                <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">
                                    {{ $unit->type->description ?? 'Sin Tipo' }}
                                </div>
                                
                                <div class="font-bold text-gray-800 text-sm font-secondary leading-tight">
                                    {{ $unit->alias }}
                                </div>

                                @if($unit->clinical_specialty_id)
                                    <div class="mt-1.5 text-[9px] text-gray-500 uppercase tracking-wider flex items-center gap-1">
                                        <svg class="w-3 h-3 text-brand-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                        {{ $unit->specialty->name ?? '' }}
                                    </div>
                                @endif
                            </div>
                        @endforeach

                    </div>
                @endforeach

            </div>
        </div>

    </div> <div x-data="{ show: @entangle('showPanel') }"
         x-show="show"
         class="fixed inset-0 overflow-hidden z-[100]" 
         x-cloak
         aria-labelledby="slide-over-title"
         role="dialog"
         aria-modal="true">
        <div class="absolute inset-0 overflow-hidden">
            <div x-show="show"
                 x-transition:enter="ease-in-out duration-500"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in-out duration-500"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm"
                 @click="$wire.closePanel()"></div>

            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-md w-full pl-10">
                <div x-show="show"
                     x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="pointer-events-auto w-screen max-w-md flex flex-col bg-white shadow-2xl">

                    <div class="bg-brand-cyan px-6 py-6 shadow-sm z-10">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold text-white font-secondary uppercase tracking-wide" id="slide-over-title">
                                {{ $is_editing ? 'Editar Unidad' : 'Nueva Unidad' }}
                            </h2>
                            <button @click="$wire.closePanel()" type="button" class="text-cyan-100 hover:text-white focus:outline-none transition-colors">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="relative flex-1 px-6 py-6 overflow-y-auto">
                        <form wire:submit="save" id="unit-form" class="space-y-6">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 font-secondary uppercase tracking-wider mb-1">Nombre de la Unidad</label>
                                <input wire:model="alias" type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm transition-colors">
                                @error('alias') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 font-secondary uppercase tracking-wider mb-1">Categoría / Tipo</label>
                                <select wire:model.live="type_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm transition-colors">
                                    <option value="">Seleccione...</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}">{{ $type->description }}</option>
                                    @endforeach
                                </select>
                                @error('type_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            @if($isServicioSelected)
                                <div class="p-4 bg-cyan-50 rounded-md border border-cyan-100">
                                    <label class="block text-[11px] font-bold text-cyan-800 font-secondary uppercase tracking-wider mb-1">Especialidad Médica</label>
                                    <select wire:model="clinical_specialty_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm">
                                        <option value="">No aplica...</option>
                                        @foreach($specialties as $specialty)
                                            <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <hr class="border-gray-100">

                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 font-secondary uppercase tracking-wider mb-2">Dependencia Funcional (Padres)</label>
                                <div class="mb-2">
                                    <input wire:model.live="search_parents" type="text" placeholder="Buscar unidades..." class="block w-full rounded-md border-gray-200 bg-gray-50 text-sm py-1.5 focus:bg-white focus:border-brand-cyan focus:ring-brand-cyan transition-colors">
                                </div>
                                <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-md bg-white p-2 space-y-1">
                                    @forelse($formSearchUnits as $unit)
                                        @if(!$is_editing || $unit->id !== $unit_id)
                                            <label wire:key="parent-chk-{{ $unit->id }}" class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer transition-colors">
                                                <input wire:model="parent_ids" type="checkbox" value="{{ $unit->id }}" class="rounded border-gray-300 text-brand-cyan focus:ring-brand-cyan w-4 h-4">
                                                <span class="ml-3 text-sm text-gray-700">{{ $unit->alias }}</span>
                                            </label>
                                        @endif
                                    @empty
                                        <div class="text-xs text-gray-400 text-center py-2">No se encontraron unidades.</div>
                                    @endforelse
                                </div>
                                @error('parent_ids') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <hr class="border-gray-100">

                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 font-secondary uppercase tracking-wider mb-1">Reporta Estadísticamente a</label>
                                <select wire:model="hierarchical_unit_id_to_report" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm">
                                    <option value="">Ninguno...</option>
                                    @foreach($serviceUnits as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->alias }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
                        <div class="flex flex-col gap-3">
                            <button type="submit" form="unit-form" class="w-full justify-center items-center px-4 py-2 border border-transparent text-sm font-bold rounded-md text-white bg-brand-cyan hover:bg-cyan-600 focus:outline-none transition-colors uppercase tracking-wide shadow-sm">
                                {{ $is_editing ? 'Guardar Cambios' : 'Confirmar y Crear' }}
                            </button>
                            
                            @if($is_editing)
                                <button type="button" wire:click="delete" wire:confirm="¿Confirmas eliminar esta unidad? Se borrará de la estructura." class="w-full justify-center items-center px-4 py-2 border border-gray-300 text-sm font-bold rounded-md text-red-600 bg-white hover:bg-red-50 focus:outline-none transition-colors uppercase tracking-wide">
                                    Eliminar Unidad
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> </div> <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('hospitalBoard', () => ({
            relations: @entangle('relationsMap'),
            activeNode: null,
            activeParents: [],
            activeChildren: [],
            
            isDragging: false,
            startX: 0,
            scrollLeft: 0,

            startDrag(e) {
                this.isDragging = true;
                this.startX = e.pageX - this.$refs.scrollContainer.offsetLeft;
                this.scrollLeft = this.$refs.scrollContainer.scrollLeft;
            },
            endDrag() {
                this.isDragging = false;
            },
            onDrag(e) {
                if (!this.isDragging) return;
                e.preventDefault();
                const x = e.pageX - this.$refs.scrollContainer.offsetLeft;
                const walk = (x - this.startX) * 1.5; 
                this.$refs.scrollContainer.scrollLeft = this.scrollLeft - walk;
            },

            hoverNode(id) {
                this.activeNode = id;
                this.activeParents = this.relations[id]?.parents || [];
                this.activeChildren = this.relations[id]?.children || [];
            },
            clearHover() {
                this.activeNode = null;
                this.activeParents = [];
                this.activeChildren = [];
            },
            isParent(id) {
                return this.activeParents.includes(id);
            },
            isChild(id) {
                return this.activeChildren.includes(id);
            },
            getNodeClass(id) {
                if (this.activeNode === null) return 'border-gray-200 opacity-100 hover:border-brand-cyan hover:shadow-md';
                if (this.activeNode === id) return 'border-brand-cyan ring-2 ring-brand-cyan/20 opacity-100 scale-[1.02] z-10';
                if (this.isParent(id)) return 'border-amber-400 opacity-100 shadow-md ring-1 ring-amber-400/50 scale-[1.01]';
                if (this.isChild(id)) return 'border-emerald-400 opacity-100 shadow-md ring-1 ring-emerald-400/50 scale-[1.01]';
                return 'border-gray-100 opacity-40 grayscale-[40%] scale-[0.98]';
            }
        }))
    })
</script>