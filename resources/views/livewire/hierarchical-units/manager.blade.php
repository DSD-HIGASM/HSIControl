<div x-data="{ 
    currentView: 'board',
    initTabs() {
        this.$watch('currentView', val => {
            if(val === 'graph' && typeof network !== 'undefined') {
                setTimeout(() => network.fit(), 50);
            }
        });
    }
}" x-init="initTabs()">

    <div class="max-w-[1400px] mx-auto space-y-6 relative">

        <style>
            .board-scrollbar::-webkit-scrollbar {
                height: 10px;
            }

            .board-scrollbar::-webkit-scrollbar-track {
                background: #f1f5f9;
                border-radius: 8px;
                margin: 0 10px;
            }

            .board-scrollbar::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 8px;
            }

            .board-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }

            .col-scrollbar::-webkit-scrollbar {
                width: 4px;
            }

            .col-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .col-scrollbar::-webkit-scrollbar-thumb {
                background: #e2e8f0;
                border-radius: 4px;
            }

            .col-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #cbd5e1;
            }
        </style>

        <div class="bg-white p-6 sm:px-8 rounded-xl shadow-sm border border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2
                        class="text-xl font-bold text-gray-800 font-secondary uppercase tracking-wide flex items-center gap-3">
                        Estructura Funcional

                        <div class="flex bg-gray-100 p-1 rounded-lg">
                            <button @click="currentView = 'board'"
                                :class="currentView === 'board' ? 'bg-white shadow-sm text-brand-cyan' : 'text-gray-500 hover:text-gray-700'"
                                class="px-3 py-1 text-[11px] font-bold uppercase tracking-wider rounded-md transition-all">
                                Tablero
                            </button>
                            <button @click="currentView = 'graph'"
                                :class="currentView === 'graph' ? 'bg-white shadow-sm text-brand-cyan' : 'text-gray-500 hover:text-gray-700'"
                                class="px-3 py-1 text-[11px] font-bold uppercase tracking-wider rounded-md transition-all">
                                Mapa (Grafo)
                            </button>
                        </div>
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Explora la jerarquía organizativa o gestiona nuevas
                        dependencias.</p>
                </div>
                <button wire:click="openPanel"
                    class="shrink-0 inline-flex justify-center items-center px-5 py-2 text-sm font-bold rounded-md text-white bg-brand-cyan hover:bg-cyan-600 transition-colors uppercase tracking-wide gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nueva Unidad Raíz
                </button>
            </div>

            @if (session('status'))
                <div
                    class="mt-6 px-4 py-3 bg-green-50 text-green-700 border border-green-200 rounded-md text-sm font-bold flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ session('status') }}
                </div>
            @endif
        </div>

        <div x-data="hospitalBoard(@js($relationsMap), @js($unitsData))">

            <div x-show="contextMenu.show" @click.away="closeContextMenu()" x-transition.opacity.duration.150ms x-cloak
                :style="`top: ${contextMenu.y}px; left: ${contextMenu.x}px;`"
                class="fixed z-[150] bg-white rounded-xl shadow-xl border border-gray-200 p-2 flex flex-col gap-1 min-w-[220px]">

                <div class="text-[10px] font-bold text-gray-400 uppercase border-b border-gray-100 pb-1.5 mb-1 px-2 text-center"
                    x-text="unitsDict[contextMenu.unitId]?.alias"></div>

                <button @click="$wire.openPanel(contextMenu.unitId); closeContextMenu()"
                    class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-cyan-50 hover:text-brand-cyan rounded-md transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                        </path>
                    </svg>
                    Editar Unidad
                </button>

                <button @click="$wire.createChild(contextMenu.unitId); closeContextMenu()"
                    class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-md transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Agregar Hija aquí
                </button>

                <button @click="setLineageFilter(contextMenu.unitId)"
                    class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 rounded-md transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    Aislar Línea (Padres/Hijos)
                </button>
            </div>

            <div x-show="lineageFilterId" x-cloak
                class="bg-gradient-to-r from-brand-cyan/10 to-brand-blue/10 border border-brand-cyan/20 p-4 rounded-xl mb-4 flex flex-col sm:flex-row justify-between items-center gap-4 shadow-sm">
                <div class="text-sm text-brand-cyan font-medium flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span><strong class="font-bold">Aislamiento Jerárquico:</strong> Mostrando árbol genealógico
                        completo de <strong class="uppercase text-brand-blue"
                            x-text="unitsDict[lineageFilterId]?.alias"></strong></span>
                </div>
                <button @click="clearLineageFilter()"
                    class="text-brand-cyan hover:text-white font-bold text-xs uppercase bg-white hover:bg-brand-cyan px-4 py-2 rounded-lg shadow-sm border border-cyan-200 transition-colors">
                    Quitar Filtro
                </button>
            </div>

            <div x-show="currentView === 'board'"
                class="bg-slate-50 border border-gray-200 rounded-xl pt-4 pb-2 overflow-hidden flex flex-col">
                <div class="px-6 pb-4 border-b border-gray-200 mb-4 flex gap-4 items-center">
                    <div class="relative w-full max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input x-model="searchQuery" :disabled="lineageFilterId !== null" type="text"
                            class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-400 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan sm:text-sm transition-colors shadow-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                            placeholder="Buscar en tablero por nombre...">
                    </div>
                </div>

                <div x-ref="scrollContainer" @mousedown="startDrag" @mouseleave="endDrag" @mouseup="endDrag"
                    @mousemove="onDrag"
                    class="flex flex-nowrap overflow-x-auto px-6 pb-4 gap-4 snap-x cursor-grab active:cursor-grabbing board-scrollbar">
                    @foreach($groupedUnits as $level => $units)
                        <div wire:key="col-level-{{ $level }}"
                            class="snap-start shrink-0 w-64 bg-gray-100/60 rounded-lg p-2 border border-gray-200 flex flex-col h-[65vh]">
                            <div class="flex items-center justify-between border-b border-gray-200 pb-2 mb-2 px-1 shrink-0">
                                <h3
                                    class="font-bold text-gray-700 font-secondary uppercase tracking-wide text-[11px] flex items-center gap-2">
                                    @if($level == 99) ⚠️ Error @else <span
                                        class="bg-gray-200 text-gray-500 px-1.5 rounded text-[9px]">L{{ $level }}</span>
                                    Nivel {{ $level }} @endif
                                </h3>
                                <span
                                    class="bg-gray-200 text-gray-600 text-[9px] font-bold px-2 py-0.5 rounded-full">{{ count($units) }}</span>
                            </div>

                            <div class="overflow-y-auto flex-1 space-y-2 pr-1 col-scrollbar pb-4">
                                @foreach($units as $unit)
                                    <div wire:key="unit-{{ $unit->id }}" x-show="isVisible({{ $unit->id }})"
                                        x-transition.opacity.duration.200ms @mouseenter="hoverNode({{ $unit->id }})"
                                        @mouseleave="clearHover()" @click.prevent="openContextMenu($event, {{ $unit->id }})"
                                        :class="getNodeClass({{ $unit->id }})"
                                        class="relative bg-white p-2.5 rounded shadow-sm border transition-all duration-200 cursor-pointer group select-none">
                                        <span x-show="isParent({{ $unit->id }})" x-cloak
                                            class="absolute -top-2 right-1 bg-amber-100 text-amber-800 border border-amber-200 text-[8px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider shadow-sm z-10">Padre</span>
                                        <span x-show="isChild({{ $unit->id }})" x-cloak
                                            class="absolute -top-2 right-1 bg-emerald-100 text-emerald-800 border border-emerald-200 text-[8px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider shadow-sm z-10">Hijo</span>

                                        <div
                                            class="text-[8px] font-bold uppercase tracking-wider text-gray-400 mb-0.5 leading-none">
                                            {{ $unit->type->description ?? 'Sin Tipo' }}</div>
                                        <div class="font-bold text-gray-800 text-[13px] font-secondary leading-tight">
                                            {{ $unit->alias }}</div>

                                        @if($unit->clinical_specialty_id)
                                            <div
                                                class="mt-1 text-[9px] text-brand-cyan font-medium flex items-center gap-1 truncate">
                                                <svg class="w-2.5 h-2.5 shrink-0" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                                </svg>
                                                <span class="truncate">{{ $unit->specialty->name ?? '' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div x-show="currentView === 'graph'" style="display: none;"
            class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm relative h-[75vh]" wire:ignore>
            <div id="hospital-network" class="w-full h-full cursor-grab active:cursor-grabbing bg-slate-50/50"></div>

            <div
                class="absolute bottom-6 right-6 bg-white border border-gray-200 shadow-md rounded-lg p-1 flex gap-1 z-10">
                <button onclick="network.moveTo({scale: network.getScale() + 0.2})"
                    class="p-2 text-gray-500 hover:text-brand-cyan hover:bg-cyan-50 rounded transition-colors"
                    title="Acercar">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
                <button onclick="network.moveTo({scale: network.getScale() - 0.2})"
                    class="p-2 text-gray-500 hover:text-brand-cyan hover:bg-cyan-50 rounded transition-colors"
                    title="Alejar">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </button>
                <div class="w-px bg-gray-200 my-1 mx-1"></div>
                <button onclick="network.fit()"
                    class="p-2 text-gray-500 hover:text-brand-cyan hover:bg-cyan-50 rounded transition-colors"
                    title="Centrar todo el árbol">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                </button>
            </div>
        </div>

    </div>

    <div x-data="{ show: @entangle('showPanel') }" x-show="show" class="fixed inset-0 overflow-hidden z-[100]" x-cloak
        aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 overflow-hidden">
            <div x-show="show" x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm"
                @click="$wire.closePanel()"></div>

            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-md w-full pl-10">
                <div x-show="show" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                    x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                    x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                    class="pointer-events-auto w-screen max-w-md flex flex-col bg-white shadow-2xl">

                    <div class="bg-brand-cyan px-6 py-6 shadow-sm z-10">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold text-white font-secondary uppercase tracking-wide"
                                id="slide-over-title">
                                {{ $is_editing ? 'Editar Unidad' : 'Nueva Unidad' }}
                            </h2>
                            <button @click="$wire.closePanel()" type="button"
                                class="text-cyan-100 hover:text-white focus:outline-none transition-colors">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="relative flex-1 px-6 py-6 overflow-y-auto">
                        <form wire:submit="save" id="unit-form" class="space-y-6">
                            <div>
                                <label
                                    class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Nombre
                                    de la Unidad</label>
                                <input wire:model="alias" type="text"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary transition-colors">
                                @error('alias') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <x-searchable-select wire:model.live="type_id" label="Categoría / Tipo"
                                    :options="$typesOptions" placeholder="Buscar categoría..."
                                    defaultText="Seleccione..." />
                                @error('type_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            @if($isServicioSelected)
                                <div class="p-4 bg-cyan-50 rounded-md border border-cyan-100">
                                    <label
                                        class="block text-[11px] font-bold text-cyan-800 font-secondary uppercase tracking-wider mb-1">Especialidad
                                        Médica</label>
                                    <select wire:model="clinical_specialty_id"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary">
                                        <option value="">No aplica...</option>
                                        @foreach($specialties as $specialty)
                                            <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <hr class="border-gray-100">

                            <div>
                                <label
                                    class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-2">Dependencia
                                    Funcional (Padres)</label>
                                <div class="mb-2">
                                    <input wire:model.live="search_parents" type="text"
                                        placeholder="Filtrar por nombre..."
                                        class="block w-full rounded-md border-gray-200 bg-gray-50 text-sm py-1.5 focus:bg-white focus:border-brand-cyan focus:ring-brand-cyan transition-colors font-secondary">
                                </div>
                                <div
                                    class="max-h-48 overflow-y-auto border border-gray-200 rounded-md bg-white p-2 space-y-1">
                                    @forelse($formSearchUnits as $unit)
                                        @if(!$is_editing || $unit->id !== $unit_id)
                                            <label wire:key="parent-chk-{{ $unit->id }}"
                                                class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer transition-colors">
                                                <input wire:model="parent_ids" type="checkbox" value="{{ $unit->id }}"
                                                    class="rounded border-gray-300 text-brand-cyan focus:ring-brand-cyan w-4 h-4">
                                                <span
                                                    class="ml-3 text-sm text-gray-700 font-secondary">{{ $unit->alias }}</span>
                                            </label>
                                        @endif
                                    @empty
                                        <div class="text-xs text-gray-400 text-center py-2">No se encontraron unidades.
                                        </div>
                                    @endforelse
                                </div>
                                @error('parent_ids') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <hr class="border-gray-100">

                            <div class="pb-24">
                                <x-searchable-select wire:model="hierarchical_unit_id_to_report"
                                    label="Reporta Estadísticamente a" :options="$serviceUnitsOptions"
                                    placeholder="Buscar servicio de reporte..." defaultText="Ninguno..." />
                            </div>
                        </form>
                    </div>

                    <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 z-20">
                        <div class="flex flex-col gap-3">
                            <button type="submit" form="unit-form"
                                class="w-full justify-center items-center px-4 py-2 border border-transparent text-sm font-bold rounded-md text-white bg-brand-cyan hover:bg-cyan-600 focus:outline-none transition-colors uppercase tracking-wide shadow-sm font-secondary">
                                {{ $is_editing ? 'Guardar Cambios' : 'Confirmar y Crear' }}
                            </button>

                            @if($is_editing)
                                <button type="button" wire:click="delete"
                                    wire:confirm="¿Confirmas eliminar esta unidad? Se borrará de la estructura."
                                    class="w-full justify-center items-center px-4 py-2 border border-gray-300 text-sm font-bold rounded-md text-red-600 bg-white hover:bg-red-50 focus:outline-none transition-colors uppercase tracking-wide font-secondary">
                                    Eliminar Unidad
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let network;
    let allNetworkData = null;

    // --- LÓGICA DEL TABLERO Y MENÚ (Alpine) ---
    document.addEventListener('alpine:init', () => {
        Alpine.data('hospitalBoard', (initialMap, unitsData) => ({
            relations: initialMap,
            unitsDict: unitsData,
            searchQuery: '',
            
            lineageFilterId: null,
            contextMenu: { show: false, x: 0, y: 0, unitId: null },
            
            activeNode: null, activeParents: [], activeChildren: [],
            isDragging: false, startX: 0, scrollLeft: 0,

            init() {
                window.addEventListener('relations-updated', (e) => {
                    this.relations = e.detail.map;
                    this.clearHover();
                    if(this.lineageFilterId) this.syncGraphFilter(); 
                });

                window.addEventListener('open-context-menu', (e) => {
                    let x = e.detail.x + 5;
                    let y = e.detail.y + 5;
                    if (x + 220 > window.innerWidth) x = e.detail.x - 220 - 5;
                    if (y + 150 > window.innerHeight) y = e.detail.y - 150 - 5;

                    this.contextMenu.unitId = e.detail.id;
                    this.contextMenu.x = x;
                    this.contextMenu.y = y;
                    this.contextMenu.show = true;
                });

                // NUEVO: Escuchamos el evento de cierre seguro
                window.addEventListener('close-context-menu', () => {
                    this.closeContextMenu();
                });
            },

            openContextMenu(event, id) {
                let x = event.clientX + 5;
                let y = event.clientY + 5;
                if (x + 220 > window.innerWidth) x = event.clientX - 220 - 5;
                if (y + 150 > window.innerHeight) y = event.clientY - 150 - 5;

                this.contextMenu.unitId = id;
                this.contextMenu.x = x;
                this.contextMenu.y = y;
                this.contextMenu.show = true;
            },
            
            closeContextMenu() {
                this.contextMenu.show = false;
            },

            getLineage(startId) {
                let lineage = new Set();
                lineage.add(startId);

                let upQueue = [startId];
                while(upQueue.length > 0) {
                    let curr = upQueue.shift();
                    if(this.relations[curr]) {
                        this.relations[curr].parents.forEach(p => {
                            if(!lineage.has(p)) { lineage.add(p); upQueue.push(p); }
                        });
                    }
                }

                let downQueue = [startId];
                while(downQueue.length > 0) {
                    let curr = downQueue.shift();
                    if(this.relations[curr]) {
                        this.relations[curr].children.forEach(c => {
                            if(!lineage.has(c)) { lineage.add(c); downQueue.push(c); }
                        });
                    }
                }
                return lineage;
            },

            setLineageFilter(id) {
                this.lineageFilterId = id;
                this.searchQuery = ''; 
                this.closeContextMenu();
                this.syncGraphFilter(); 
            },

            clearLineageFilter() {
                this.lineageFilterId = null;
                this.syncGraphFilter(); 
            },

            syncGraphFilter() {
                let visibleIds = this.lineageFilterId ? Array.from(this.getLineage(this.lineageFilterId)) : [];
                window.dispatchEvent(new CustomEvent('filter-graph', { detail: { visibleIds: visibleIds } }));
            },

            get visibleNodes() {
                if (this.lineageFilterId) return this.getLineage(this.lineageFilterId);
                
                if (this.searchQuery) {
                    const query = this.searchQuery.toLowerCase();
                    let matched = new Set();
                    for (const [id, unit] of Object.entries(this.unitsDict)) {
                        if (unit.alias.includes(query)) {
                            matched.add(parseInt(id));
                            if(this.relations[id]) {
                                this.relations[id].parents.forEach(p => matched.add(p));
                                this.relations[id].children.forEach(c => matched.add(c));
                            }
                        }
                    }
                    return matched;
                }
                return null;
            },

            isVisible(id) {
                if (!this.lineageFilterId && !this.searchQuery) return true;
                return this.visibleNodes.has(id);
            },

            startDrag(e) { this.isDragging = true; this.startX = e.pageX - this.$refs.scrollContainer.offsetLeft; this.scrollLeft = this.$refs.scrollContainer.scrollLeft; },
            endDrag() { this.isDragging = false; },
            onDrag(e) { if (!this.isDragging) return; e.preventDefault(); const x = e.pageX - this.$refs.scrollContainer.offsetLeft; this.$refs.scrollContainer.scrollLeft = this.scrollLeft - (x - this.startX) * 1.5; },

            hoverNode(id) { 
                if(this.contextMenu.show) return; 
                this.activeNode = id; this.activeParents = this.relations[id]?.parents || []; this.activeChildren = this.relations[id]?.children || []; 
            },
            clearHover() { this.activeNode = null; this.activeParents = []; this.activeChildren = []; },
            isParent(id) { return this.activeParents.includes(id); },
            isChild(id) { return this.activeChildren.includes(id); },
            
            getNodeClass(id) {
                if (this.activeNode === null && this.contextMenu.unitId !== id) return 'border-gray-200 opacity-100 hover:border-brand-cyan hover:shadow-md';
                if (this.activeNode === id || this.contextMenu.unitId === id) return 'border-brand-cyan ring-2 ring-brand-cyan/20 opacity-100 z-10';
                if (this.isParent(id)) return 'border-amber-400 opacity-100 shadow-md ring-1 ring-amber-400/50';
                if (this.isChild(id)) return 'border-emerald-400 opacity-100 shadow-md ring-1 ring-emerald-400/50';
                return 'border-gray-100 opacity-30 grayscale-[50%]'; 
            }
        }))
    });

    // --- LÓGICA DEL GRAFO (Vis.js) ---
    document.addEventListener('livewire:initialized', () => {
        allNetworkData = @json($networkData);
        
        // Usamos el objeto global vis inyectado desde app.js
        const nodes = new vis.DataSet(allNetworkData.nodes);
        const edges = new vis.DataSet(allNetworkData.edges);
        
        const container = document.getElementById('hospital-network');
        const data = { nodes: nodes, edges: edges };
        
        const options = {
            layout: { hierarchical: { enabled: true, direction: 'LR', sortMethod: 'directed', nodeSpacing: 70, levelSeparation: 350, treeSpacing: 120, parentCentralization: true } },
            physics: { enabled: false }, 
            nodes: {
                shape: 'box', 
                margin: { top: 12, right: 18, bottom: 12, left: 18 },
                font: { 
                    face: 'Inter, system-ui, sans-serif', 
                    multi: 'html',
                    bold: { size: 14, color: '#1e293b' }, 
                    ital: { size: 10, color: '#64748b', mod: 'normal' }
                },
                color: { 
                    background: '#ffffff', 
                    border: '#06b6d4', 
                    highlight: { background: '#ecfeff', border: '#0891b2' }, 
                    hover: { background: '#f8fafc', border: '#0ea5e9' } 
                },
                borderWidth: 2, 
                borderWidthSelected: 3, 
                shapeProperties: { borderRadius: 6 },
                shadow: { enabled: true, color: 'rgba(0,0,0,0.05)', size: 6, x: 0, y: 3 }
            },
            edges: { 
                arrows: { to: { enabled: true, scaleFactor: 0.7 } }, 
                color: { color: '#cbd5e1', highlight: '#0d9488', hover: '#94a3b8' }, 
                smooth: { type: 'cubicBezier', forceDirection: 'horizontal', roundness: 0.6 }, 
                width: 1.5, hoverWidth: 2, selectionWidth: 2 
            },
            interaction: { hover: true, dragNodes: false, zoomView: true, dragView: true }
        };

        network = new vis.Network(container, data, options);

        // NUEVO: Clic maneja apertura/cierre de forma segura
        network.on("click", function (params) {
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                const DOMCoord = params.pointer.DOM;
                const rect = container.getBoundingClientRect();
                
                window.dispatchEvent(new CustomEvent('open-context-menu', {
                    detail: { id: nodeId, x: DOMCoord.x + rect.left, y: DOMCoord.y + rect.top }
                }));
            } else {
                window.dispatchEvent(new Event('close-context-menu'));
            }
        });

        // NUEVO: Si arrastrás el mapa, también se cierra el menú
        network.on("dragStart", function () {
            window.dispatchEvent(new Event('close-context-menu'));
        });

        window.addEventListener('network-updated', (event) => {
            allNetworkData = event.detail.data;
            document.querySelector('[x-data]').__x.$data.syncGraphFilter();
        });

        window.addEventListener('filter-graph', (e) => {
            const visibleIds = e.detail.visibleIds;
            if (!visibleIds || visibleIds.length === 0) {
                nodes.clear(); edges.clear();
                nodes.add(allNetworkData.nodes); edges.add(allNetworkData.edges);
            } else {
                const filteredNodes = allNetworkData.nodes.filter(n => visibleIds.includes(n.id));
                const filteredEdges = allNetworkData.edges.filter(edge => visibleIds.includes(edge.from) && visibleIds.includes(edge.to));
                nodes.clear(); edges.clear();
                nodes.add(filteredNodes); edges.add(filteredEdges);
            }
            network.fit();
        });
    });
</script>