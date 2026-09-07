@php
    // Paleta cromática equilibrada para diferenciar servicios en salud
    $servicePalette = [
    ['border' => '#e61919', 'bg' => '#fde7e7', 'text' => '#760a0a'],
    ['border' => '#e65719', 'bg' => '#fdeee7', 'text' => '#762a0a'],
    ['border' => '#e63819', 'bg' => '#fdebe7', 'text' => '#761a0a'],
    ['border' => '#e69419', 'bg' => '#fdf4e7', 'text' => '#764b0a'],
    ['border' => '#e67519', 'bg' => '#fdf1e7', 'text' => '#763a0a'],
    ['border' => '#e6d119', 'bg' => '#fdfbe7', 'text' => '#766b0a'],
    ['border' => '#e6b319', 'bg' => '#fdf8e7', 'text' => '#765b0a'],
    ['border' => '#bde619', 'bg' => '#f9fde7', 'text' => '#60760a'],
    ['border' => '#dbe619', 'bg' => '#fcfde7', 'text' => '#71760a'],
    ['border' => '#80e619', 'bg' => '#f2fde7', 'text' => '#40760a'],
    ['border' => '#9ee619', 'bg' => '#f6fde7', 'text' => '#50760a'],
    ['border' => '#42e619', 'bg' => '#ecfde7', 'text' => '#1f760a'],
    ['border' => '#61e619', 'bg' => '#effde7', 'text' => '#2f760a'],
    ['border' => '#19e62e', 'bg' => '#e7fdea', 'text' => '#0a7614'],
    ['border' => '#24e619', 'bg' => '#e8fde7', 'text' => '#0f760a'],
    ['border' => '#19e66b', 'bg' => '#e7fdf0', 'text' => '#0a7635'],
    ['border' => '#19e64d', 'bg' => '#e7fded', 'text' => '#0a7625'],
    ['border' => '#19e6a8', 'bg' => '#e7fdf7', 'text' => '#0a7655'],
    ['border' => '#19e68a', 'bg' => '#e7fdf3', 'text' => '#0a7645'],
    ['border' => '#19e6e6', 'bg' => '#e7fdfd', 'text' => '#0a7676'],
    ['border' => '#19e6c7', 'bg' => '#e7fdfa', 'text' => '#0a7666'],
    ['border' => '#19a8e6', 'bg' => '#e7f7fd', 'text' => '#0a5576'],
    ['border' => '#19c7e6', 'bg' => '#e7fafd', 'text' => '#0a6676'],
    ['border' => '#196be6', 'bg' => '#e7f0fd', 'text' => '#0a3576'],
    ['border' => '#198ae6', 'bg' => '#e7f3fd', 'text' => '#0a4576'],
    ['border' => '#192ee6', 'bg' => '#e7eafd', 'text' => '#0a1476'],
    ['border' => '#194de6', 'bg' => '#e7edfd', 'text' => '#0a2576'],
    ['border' => '#4219e6', 'bg' => '#ece7fd', 'text' => '#1f0a76'],
    ['border' => '#2419e6', 'bg' => '#e8e7fd', 'text' => '#0f0a76'],
    ['border' => '#8019e6', 'bg' => '#f2e7fd', 'text' => '#400a76'],
    ['border' => '#6119e6', 'bg' => '#efe7fd', 'text' => '#2f0a76'],
    ['border' => '#bd19e6', 'bg' => '#f9e7fd', 'text' => '#600a76'],
    ['border' => '#9e19e6', 'bg' => '#f6e7fd', 'text' => '#500a76'],
    ['border' => '#e619d1', 'bg' => '#fde7fb', 'text' => '#760a6b'],
    ['border' => '#db19e6', 'bg' => '#fce7fd', 'text' => '#710a76'],
    ['border' => '#e61994', 'bg' => '#fde7f4', 'text' => '#760a4b'],
    ['border' => '#e619b3', 'bg' => '#fde7f8', 'text' => '#760a5b'],
    ['border' => '#e61957', 'bg' => '#fde7ee', 'text' => '#760a2a'],
    ['border' => '#e61975', 'bg' => '#fde7f1', 'text' => '#760a3a'],
    ['border' => '#e61938', 'bg' => '#fde7eb', 'text' => '#760a1a'],
];

    // Mapeo de unidades planas y asignación de colores
    $allUnitsFlat = [];
    $serviceColorIndex = [];
    $colorCounter = 0;

    foreach ($groupedUnits as $level => $uList) {
        foreach ($uList as $u) {
            $allUnitsFlat[$u->id] = $u;
            $sId = $u->closest_service_id ?? ($u->type_id == 8 ? $u->id : null);
            if ($sId && !isset($serviceColorIndex[$sId])) {
                $serviceColorIndex[$sId] = $colorCounter % count($servicePalette);
                $colorCounter++;
            }
        }
    }

    $getUnitColor = function($unit) use ($servicePalette, $serviceColorIndex) {
        $sId = $unit->closest_service_id ?? ($unit->type_id == 8 ? $unit->id : null);
        if ($sId && isset($serviceColorIndex[$sId])) {
            return $servicePalette[$serviceColorIndex[$sId]];
        }
        if ($unit->type_id == 1) { // Dirección
            return ['border' => '#0f172a', 'bg' => '#f8fafc', 'text' => '#0f172a'];
        }
        if ($unit->type_id == 7) { // Departamento
            return ['border' => '#0369a1', 'bg' => '#f0f9ff', 'text' => '#0369a1'];
        }
        return ['border' => '#cbd5e1', 'bg' => '#ffffff', 'text' => '#475569'];
    };

    // Metadata para inyectar estilo y colores en el Vis.js Graph
    $networkMeta = [];
    foreach ($allUnitsFlat as $id => $u) {
        $c = $getUnitColor($u);
        $networkMeta[$id] = [
            'service_id' => $u->closest_service_id ?? ($u->type_id == 8 ? $u->id : null),
            'type_id' => $u->type_id,
            'is_service' => $u->type_id == 8,
            'border' => $c['border'],
            'bg' => $c['bg'],
        ];
    }
@endphp

<div x-data="{
    currentView: 'board',
    initTabs() {
        this.$watch('currentView', val => {
            if(val === 'graph' && typeof network !== 'undefined') {
                setTimeout(() => network.fit(), 80);
            }
        });
    }
}" x-init="initTabs()">

    <div class="max-w-[1400px] mx-auto space-y-6 relative">

        <style>
            .board-scrollbar::-webkit-scrollbar { height: 8px; }
            .board-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; margin: 0 10px; }
            .board-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
            .board-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

            .col-scrollbar::-webkit-scrollbar { width: 4px; }
            .col-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .col-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
            .col-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        </style>

        <!-- HEADER PRINCIPAL -->
        <div class="bg-white p-6 sm:px-8 rounded-xl shadow-sm border border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 font-secondary uppercase tracking-wide flex items-center gap-3">
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
                    <p class="text-sm text-gray-500 mt-1 font-secondary">Explorá la jerarquía organizativa, dependencias y servicios del hospital.</p>
                </div>
                @can('gestionar.unidades_jerarquicas')
                <div class="flex items-center gap-3">
                    <button wire:click="openImportModal"
                        class="shrink-0 inline-flex justify-center items-center px-4 py-2 text-sm font-bold rounded-md text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors uppercase tracking-wide gap-2 shadow-sm font-secondary">
                        <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Importar CSV
                    </button>

                    <button wire:click="openPanel"
                        class="shrink-0 inline-flex justify-center items-center px-5 py-2 text-sm font-bold rounded-md text-white bg-brand-cyan hover:bg-cyan-600 transition-colors uppercase tracking-wide gap-2 shadow-sm font-secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nueva Unidad Raíz
                    </button>
                </div>
                @endcan
            </div>

            @if (session('status'))
                <div class="mt-4 px-4 py-3 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg text-sm font-bold flex items-center gap-2 font-secondary">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ session('status') }}
                </div>
            @endif
        </div>

        <!-- CONTENEDOR BOARD & GRAFO -->
        <div x-data="hospitalBoard(@js($relationsMap), @js($unitsData))">

            <!-- MENÚ CONTEXTUAL (CLICK DERECHO O SIMPLE) -->
            <div x-show="contextMenu.show" @click.away="closeContextMenu()" x-transition.opacity.duration.150ms x-cloak
                :style="`top: ${contextMenu.y}px; left: ${contextMenu.x}px;`"
                class="fixed z-[150] bg-white rounded-xl shadow-2xl border border-gray-200 p-2 flex flex-col gap-1 min-w-[220px]">
                <div class="text-[10px] font-bold text-gray-400 uppercase border-b border-gray-100 pb-1.5 mb-1 px-2 text-center truncate"
                    x-text="unitsDict[contextMenu.unitId]?.alias"></div>

                @can('gestionar.unidades_jerarquicas')
                <button @click="$wire.openPanel(contextMenu.unitId); closeContextMenu()"
                    class="w-full text-left px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-cyan-50 hover:text-brand-cyan rounded-md transition-colors flex items-center gap-2 font-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    Editar Unidad
                </button>

                <button @click="$wire.createChild(contextMenu.unitId); closeContextMenu()"
                    class="w-full text-left px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-md transition-colors flex items-center gap-2 font-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Agregar Hija aquí
                </button>
                @endcan

                <button @click="setLineageFilter(contextMenu.unitId)"
                    class="w-full text-left px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-amber-50 hover:text-amber-700 rounded-md transition-colors flex items-center gap-2 font-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Aislar Línea (Padres/Hijos)
                </button>
            </div>

            <!-- BANNER DE AISLAMIENTO -->
            <div x-show="lineageFilterId" x-cloak
                class="bg-gradient-to-r from-brand-cyan/10 via-cyan-50 to-brand-blue/10 border border-brand-cyan/20 p-4 rounded-xl mb-4 flex flex-col sm:flex-row justify-between items-center gap-4 shadow-sm">
                <div class="text-sm text-brand-cyan font-medium flex items-center gap-2 font-secondary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span><strong>Aislamiento Jerárquico:</strong> Mostrando árbol genealógico completo de <strong class="uppercase text-brand-blue" x-text="unitsDict[lineageFilterId]?.alias"></strong></span>
                </div>
                <button @click="clearLineageFilter()"
                    class="text-brand-cyan hover:text-white font-bold text-xs uppercase bg-white hover:bg-brand-cyan px-4 py-2 rounded-lg shadow-sm border border-cyan-200 transition-colors font-secondary">
                    Quitar Filtro
                </button>
            </div>

            <!-- VISTA 1: TABLERO (COLUMNAS KANBAN) -->
            <div x-show="currentView === 'board'" class="bg-slate-50 border border-gray-200 rounded-xl pt-4 pb-2 overflow-hidden flex flex-col">
                <div class="px-6 pb-4 border-b border-gray-200 mb-4 flex gap-4 items-center">
                    <div class="relative w-full max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input x-model="searchQuery" :disabled="lineageFilterId !== null" type="text"
                            class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan sm:text-sm transition-colors shadow-sm disabled:bg-gray-100 font-secondary"
                            placeholder="Buscar unidad o servicio por nombre...">
                    </div>
                </div>

                <div x-ref="scrollContainer" @mousedown="startDrag" @mouseleave="endDrag" @mouseup="endDrag" @mousemove="onDrag"
                    class="flex flex-nowrap overflow-x-auto px-6 pb-4 gap-4 snap-x cursor-grab active:cursor-grabbing board-scrollbar">
                    @foreach($groupedUnits as $level => $units)
                        <div wire:key="col-level-{{ $level }}"
                            class="snap-start shrink-0 w-72 bg-gray-100/70 rounded-xl p-2.5 border border-gray-200/80 flex flex-col h-[68vh]">
                            <div class="flex items-center justify-between border-b border-gray-200 pb-2 mb-2 px-1 shrink-0">
                                <h3 class="font-bold text-gray-700 font-secondary uppercase tracking-wider text-[11px] flex items-center gap-2">
                                    @if($level == 99)
                                        ⚠️ Error
                                    @else
                                        <span class="bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded text-[9px] font-bold">L{{ $level }}</span>
                                        Nivel {{ $level }}
                                    @endif
                                </h3>
                                <span class="bg-gray-200 text-gray-600 text-[9px] font-bold px-2 py-0.5 rounded-full">{{ count($units) }}</span>
                            </div>

                            <div class="overflow-y-auto flex-1 space-y-2 pr-1 col-scrollbar pb-3">
                                @foreach($units as $unit)
                                    @php
                                        $uColor = $getUnitColor($unit);
                                        $serviceName = ($unit->closest_service_id && isset($allUnitsFlat[$unit->closest_service_id]))
                                            ? $allUnitsFlat[$unit->closest_service_id]->alias
                                            : null;
                                    @endphp

                                    <div wire:key="unit-{{ $unit->id }}"
                                        x-show="isVisible({{ $unit->id }})"
                                        x-transition.opacity.duration.200ms
                                        @mouseenter="hoverNode({{ $unit->id }})"
                                        @mouseleave="clearHover()"
                                        @click.prevent="openContextMenu($event, {{ $unit->id }})"
                                        :class="getNodeClass({{ $unit->id }})"
                                        style="border-left-color: {{ $uColor['border'] }}; border-left-width: 4px;"
                                        class="relative bg-white p-3 rounded-lg shadow-sm border border-gray-200/80 transition-all duration-200 cursor-pointer group select-none">

                                        <!-- BADGES DE PADRE / HIJO EN HOVER -->
                                        <span x-show="isParent({{ $unit->id }})" x-cloak
                                            class="absolute -top-2 right-2 bg-amber-100 text-amber-800 border border-amber-200 text-[8px] px-1.5 py-0.5 rounded-full font-bold uppercase tracking-wider shadow-sm z-10">Padre</span>
                                        <span x-show="isChild({{ $unit->id }})" x-cloak
                                            class="absolute -top-2 right-2 bg-emerald-100 text-emerald-800 border border-emerald-200 text-[8px] px-1.5 py-0.5 rounded-full font-bold uppercase tracking-wider shadow-sm z-10">Hijo</span>

                                        <div class="flex items-center justify-between gap-1 mb-1">
                                            <span class="text-[8px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-gray-100 text-gray-600">
                                                {{ $unit->type->description ?? 'Sin Tipo' }}
                                            </span>
                                            @if($unit->type_id == 8)
                                                <span class="text-[8px] font-extrabold uppercase tracking-wider px-1.5 py-0.5 rounded" style="background-color: {{ $uColor['bg'] }}; color: {{ $uColor['border'] }};">
                                                    Servicio Base
                                                </span>
                                            @endif
                                        </div>

                                        <div class="font-bold text-gray-800 text-[13px] font-secondary leading-snug">
                                            {{ $unit->alias }}
                                        </div>

                                        <!-- INDICADOR DEL SERVICIO PADRE -->
                                        @if($serviceName && $unit->type_id != 8)
                                            <div class="mt-1.5 flex items-center gap-1.5 text-[10px] text-gray-500 font-secondary truncate">
                                                <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $uColor['border'] }};"></span>
                                                <span class="truncate">{{ $serviceName }}</span>
                                            </div>
                                        @endif

                                        @if($unit->clinical_specialty_id)
                                            <div class="mt-1 text-[9px] text-brand-cyan font-semibold flex items-center gap-1 truncate font-secondary">
                                                <svg class="w-2.5 h-2.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
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

            <!-- VISTA 2: MAPA EN GRAFO (VIS.JS) -->
            <div x-show="currentView === 'graph'" style="display: none;"
                class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm relative h-[78vh]" wire:ignore>

                <!-- TOP BAR DENTRO DEL GRAFO -->
                <div class="absolute top-4 left-4 z-10 bg-white/90 backdrop-blur-sm border border-gray-200/80 px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-gray-700 font-secondary uppercase tracking-wider">Grafo Dinámico</span>
                    <span class="text-[10px] text-gray-400">| Flechas con color de servicio</span>
                </div>

                <div id="hospital-network" class="w-full h-full cursor-grab active:cursor-grabbing bg-slate-50/60"></div>

                <!-- DOCK FLOTANTE DE CONTROLES -->
                <div class="absolute bottom-6 right-6 bg-white/95 backdrop-blur-sm border border-gray-200 shadow-xl rounded-xl p-1.5 flex items-center gap-1 z-10">
                    <button onclick="network.moveTo({scale: network.getScale() + 0.25})"
                        class="p-2 text-gray-600 hover:text-brand-cyan hover:bg-cyan-50 rounded-lg transition-colors"
                        title="Acercar">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                    <button onclick="network.moveTo({scale: network.getScale() - 0.25})"
                        class="p-2 text-gray-600 hover:text-brand-cyan hover:bg-cyan-50 rounded-lg transition-colors"
                        title="Alejar">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                    </button>
                    <div class="w-px h-5 bg-gray-200 mx-1"></div>
                    <button onclick="network.fit({animation: {duration: 600, easingFunction: 'easeInOutQuad'}})"
                        class="p-2 text-gray-600 hover:text-brand-cyan hover:bg-cyan-50 rounded-lg transition-colors"
                        title="Centrar todo el árbol">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- SLIDE-OVER: EDICIÓN / NUEVA UNIDAD -->
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
                                <h2 class="text-lg font-bold text-white font-secondary uppercase tracking-wide" id="slide-over-title">
                                    {{ $is_editing ? 'Editar Unidad' : 'Nueva Unidad' }}
                                </h2>
                                <button @click="$wire.closePanel()" type="button" class="text-cyan-100 hover:text-white focus:outline-none transition-colors">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="relative flex-1 px-6 py-6 overflow-y-auto">
                            <form wire:submit="save" id="unit-form" class="space-y-6">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Nombre de la Unidad</label>
                                    <input wire:model="alias" type="text"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary transition-colors">
                                    @error('alias') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <x-searchable-select wire:model.live="type_id" label="Categoría / Tipo"
                                        :options="$typesOptions" placeholder="Buscar categoría..." defaultText="Seleccione..." />
                                    @error('type_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                @if($isServicioSelected)
                                    <div class="p-4 bg-cyan-50 rounded-md border border-cyan-100">
                                        <label class="block text-[11px] font-bold text-cyan-800 font-secondary uppercase tracking-wider mb-1">Especialidad Médica</label>
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
                                    <x-searchable-select wire:model="hierarchical_unit_id_to_report"
                                        label="Reporta Estadísticamente a" :options="$serviceUnitsOptions"
                                        placeholder="Buscar servicio de reporte..." defaultText="Ninguno..." />
                                </div>

                                <hr class="border-gray-100">

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-2">Dependencia Funcional (Padres)</label>
                                    <div class="mb-2">
                                        <input wire:model.live="search_parents" type="text" placeholder="Filtrar por nombre..."
                                            class="block w-full rounded-md border-gray-200 bg-gray-50 text-sm py-1.5 focus:bg-white focus:border-brand-cyan focus:ring-brand-cyan transition-colors font-secondary">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-md bg-white p-2 space-y-1">
                                        @forelse($formSearchUnits as $unit)
                                            @if(!$is_editing || $unit->id !== $unit_id)
                                                <label wire:key="parent-chk-{{ $unit->id }}" class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer transition-colors">
                                                    <input wire:model="parent_ids" type="checkbox" value="{{ $unit->id }}"
                                                        class="rounded border-gray-300 text-brand-cyan focus:ring-brand-cyan w-4 h-4">
                                                    <span class="ml-3 text-sm text-gray-700 font-secondary">{{ $unit->alias }}</span>
                                                </label>
                                            @endif
                                        @empty
                                            <div class="text-xs text-gray-400 text-center py-2">No se encontraron unidades.</div>
                                        @endforelse
                                    </div>
                                    @error('parent_ids') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
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

        <!-- MODAL DE IMPORTACIÓN CON SIMILITUD DE ESPECIALIDADES -->
        <div x-data="{ showModal: @entangle('showImportModal') }"
             x-show="showModal"
             x-cloak
             class="fixed inset-0 z-[160] overflow-y-auto"
             role="dialog"
             aria-modal="true">

            <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>

            <div class="flex min-h-screen items-center justify-center p-4 relative z-10 pointer-events-none">
                <div class="pointer-events-auto relative w-full {{ ($importStep ?? 'upload') === 'mapping' ? 'max-w-3xl' : 'max-w-lg' }} transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all border-t-4 border-brand-cyan">

                    @if(($importStep ?? 'upload') !== 'mapping')
                        <form wire:submit="analyzeCsv">
                            <div class="bg-white px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 font-secondary">
                                    <svg class="w-6 h-6 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                    Importar Unidades Jerárquicas (HSI)
                                </h3>
                                <p class="text-xs text-gray-500 font-secondary mt-1">
                                    Seleccioná el archivo CSV. Los servicios y unidades se vincularán y se mapearán por similitud.
                                </p>
                            </div>

                            <div class="bg-gray-50 px-6 py-5 space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2 font-secondary">Archivo CSV *</label>
                                    <input type="file" wire:model="csv_file" accept=".csv,.txt"
                                        class="block w-full text-sm text-gray-500 font-secondary file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-brand-cyan/10 file:text-brand-cyan-dark hover:file:bg-brand-cyan/20 cursor-pointer">

                                    <div wire:loading wire:target="csv_file" class="text-xs text-brand-cyan font-bold mt-2 animate-pulse font-secondary">
                                        Cargando archivo...
                                    </div>

                                    @error('csv_file')
                                        <span class="text-xs text-brand-pink font-bold mt-1 block font-secondary">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="pt-2 border-t border-gray-200">
                                    <label class="flex items-start gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="clean_tables"
                                            class="mt-0.5 rounded border-gray-300 text-brand-cyan focus:ring-brand-cyan w-4 h-4">
                                        <span class="text-xs text-gray-600 font-secondary">
                                            <strong class="text-gray-800">Vaciar tablas antes de importar</strong><br>
                                            Elimina las unidades previas para construir la jerarquía limpia desde cero.
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="bg-white px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-3">
                                <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold rounded-md font-secondary">
                                    Cancelar
                                </button>
                                <button type="submit" wire:loading.attr="disabled" wire:target="csv_file, analyzeCsv"
                                    class="inline-flex items-center gap-2 px-5 py-2 bg-brand-cyan hover:bg-cyan-600 text-white text-sm font-bold rounded-md font-secondary shadow-sm disabled:opacity-50">
                                    <span wire:loading.remove wire:target="analyzeCsv">Analizar e Importar</span>
                                    <span wire:loading wire:target="analyzeCsv">Analizando similitudes...</span>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="bg-white px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 font-secondary">
                                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                Confirmar Especialidades Clínicas
                            </h3>
                            <p class="text-xs text-gray-500 font-secondary mt-1">
                                Las siguientes unidades requieren confirmación manual. Elegí la especialidad más adecuada:
                            </p>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 max-h-[58vh] overflow-y-auto space-y-3">
                            @foreach($unresolvedSpecialties as $unitId => $item)
                                <div wire:key="unresolved-{{ $unitId }}" class="bg-white p-3.5 rounded-lg border border-gray-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="sm:w-1/2">
                                        <div class="text-xs font-bold text-gray-800 font-secondary">{{ $item['alias'] }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">
                                            Sugerencia: <span class="font-semibold text-brand-cyan">{{ $item['suggested_name'] }}</span>
                                            @if($item['similarity'] > 0)
                                                <span class="text-[10px] text-gray-400">({{ $item['similarity'] }}%)</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="sm:w-1/2">
                                        <select wire:model="unresolvedSpecialties.{{ $unitId }}.selected_id"
                                            class="block w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-brand-cyan focus:ring-brand-cyan font-secondary">
                                            <option value="">-- Sin Especialidad Asignada --</option>
                                            @foreach($specialties as $spec)
                                                <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="bg-white px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                            <button type="button" wire:click="$set('importStep', 'upload')"
                                class="text-xs text-gray-500 hover:text-gray-800 font-bold uppercase font-secondary">
                                ← Volver
                            </button>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold rounded-md font-secondary">
                                    Cancelar
                                </button>
                                <button type="button" wire:click="executeImport" wire:loading.attr="disabled"
                                    class="px-5 py-2 bg-brand-cyan hover:bg-cyan-600 text-white text-sm font-bold rounded-md shadow-sm font-secondary disabled:opacity-50">
                                    <span wire:loading.remove wire:target="executeImport">Confirmar e Importar</span>
                                    <span wire:loading wire:target="executeImport">Guardando unidades...</span>
                                </button>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>
</div>

<script>
    let network;
    let allNetworkData = null;

    // --- TABLERO INTERACTIVO (ALPINE) ---
    document.addEventListener('alpine:init', () => {
        Alpine.data('hospitalBoard', (initialMap, unitsData) => ({
            relations: initialMap,
            unitsDict: unitsData,
            searchQuery: '',

            lineageFilterId: null,
            contextMenu: { show: false, x: 0, y: 0, unitId: null },

            activeNode: null,
            activeParents: [],
            activeChildren: [],
            isDragging: false,
            startX: 0,
            scrollLeft: 0,

            init() {
                window.addEventListener('relations-updated', (e) => {
                    this.relations = e.detail.map;
                    if (e.detail.unitsData) {
                        this.unitsDict = e.detail.unitsData;
                    }
                    this.clearHover();
                    if (this.lineageFilterId) this.syncGraphFilter();
                });

                window.addEventListener('open-context-menu', (e) => {
                    let x = e.detail.x + 5;
                    let y = e.detail.y + 5;
                    if (x + 230 > window.innerWidth) x = e.detail.x - 230 - 5;
                    if (y + 160 > window.innerHeight) y = e.detail.y - 160 - 5;

                    this.contextMenu.unitId = e.detail.id;
                    this.contextMenu.x = x;
                    this.contextMenu.y = y;
                    this.contextMenu.show = true;
                });

                window.addEventListener('close-context-menu', () => {
                    this.closeContextMenu();
                });
            },

            openContextMenu(event, id) {
                let x = event.clientX + 5;
                let y = event.clientY + 5;
                if (x + 230 > window.innerWidth) x = event.clientX - 230 - 5;
                if (y + 160 > window.innerHeight) y = event.clientY - 160 - 5;

                this.contextMenu.unitId = id;
                this.contextMenu.x = x;
                this.contextMenu.y = y;
                this.contextMenu.show = true;
            },

            closeContextMenu() {
                this.contextMenu.show = false;
            },

            getLineage(startId) {
                let lineage = new Set([startId]);
                let upQueue = [startId];
                while (upQueue.length > 0) {
                    let curr = upQueue.shift();
                    if (this.relations[curr]) {
                        this.relations[curr].parents.forEach(p => {
                            if (!lineage.has(p)) { lineage.add(p); upQueue.push(p); }
                        });
                    }
                }

                let downQueue = [startId];
                while (downQueue.length > 0) {
                    let curr = downQueue.shift();
                    if (this.relations[curr]) {
                        this.relations[curr].children.forEach(c => {
                            if (!lineage.has(c)) { lineage.add(c); downQueue.push(c); }
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
                            if (this.relations[id]) {
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

            startDrag(e) {
                this.isDragging = true;
                this.startX = e.pageX - this.$refs.scrollContainer.offsetLeft;
                this.scrollLeft = this.$refs.scrollContainer.scrollLeft;
            },
            endDrag() { this.isDragging = false; },
            onDrag(e) {
                if (!this.isDragging) return;
                e.preventDefault();
                const x = e.pageX - this.$refs.scrollContainer.offsetLeft;
                this.$refs.scrollContainer.scrollLeft = this.scrollLeft - (x - this.startX) * 1.5;
            },

            hoverNode(id) {
                if (this.contextMenu.show) return;
                this.activeNode = id;
                this.activeParents = this.relations[id]?.parents || [];
                this.activeChildren = this.relations[id]?.children || [];
            },
            clearHover() {
                this.activeNode = null;
                this.activeParents = [];
                this.activeChildren = [];
            },
            isParent(id) { return this.activeParents.includes(id); },
            isChild(id) { return this.activeChildren.includes(id); },

            getNodeClass(id) {
                if (this.activeNode === null && this.contextMenu.unitId !== id) return 'opacity-100 hover:shadow-md';
                if (this.activeNode === id || this.contextMenu.unitId === id) return 'ring-2 ring-brand-cyan opacity-100 z-10 shadow-md';
                if (this.isParent(id)) return 'ring-2 ring-amber-400 opacity-100 shadow-md';
                if (this.isChild(id)) return 'ring-2 ring-emerald-400 opacity-100 shadow-md';
                return 'opacity-25 grayscale-[60%]';
            }
        }));
    });

    // --- MOTOR DEL GRAFO (VIS.JS) ---
    document.addEventListener('livewire:initialized', () => {
        allNetworkData = @json($networkData);
        const networkMeta = @json($networkMeta);

        function formatNodes(rawNodes) {
            return rawNodes.map(node => {
                const meta = networkMeta[node.id] || {};
                const borderColor = meta.border || '#06b6d4';
                const isService = meta.is_service || false;

                // Normaliza las etiquetas para que cada renglón tenga sus tags balanceados
                let cleanLabel = node.label;
                if (cleanLabel && cleanLabel.includes('<b>')) {
                    const lines = cleanLabel.split('\n');
                    cleanLabel = lines.map(line => {
                        let l = line.trim();
                        if (l.startsWith('<i>')) return l;
                        l = l.replace(/<\/?b>/g, ''); // Quitamos tags rotos
                        return `<b>${l}</b>`;         // Envolvemos la línea completa
                    }).join('\n');
                }

                return {
                    ...node,
                    label: cleanLabel,
                    borderWidth: isService ? 3 : 2,
                    borderWidthSelected: 4,
                    shapeProperties: { borderRadius: 8 },
                    color: {
                        background: '#ffffff',
                        border: borderColor,
                        highlight: {
                            background: meta.bg || '#f0f9ff',
                            border: borderColor
                        },
                        hover: {
                            background: meta.bg || '#f8fafc',
                            border: borderColor
                        }
                    }
                };
            });
        }

        function formatEdges(rawEdges) {
            return rawEdges.map(edge => ({
                ...edge,
                color: {
                    inherit: 'from', // Las conexiones heredan el color del servicio de origen
                    opacity: 0.75
                }
            }));
        }

        const { Network, DataSet } = window.vis;
        const nodes = new DataSet(formatNodes(allNetworkData.nodes));
        const edges = new DataSet(formatEdges(allNetworkData.edges));

        const container = document.getElementById('hospital-network');
        const data = { nodes: nodes, edges: edges };

        const options = {
            layout: {
                hierarchical: {
                    enabled: true,
                    direction: 'LR',
                    sortMethod: 'directed',
                    nodeSpacing: 100,
                    levelSeparation: 320,
                    treeSpacing: 140,
                    parentCentralization: true,
                    blockShifting: true,
                    edgeMinimization: true
                }
            },
            physics: { enabled: false },
            nodes: {
                shape: 'box',
                margin: { top: 12, right: 18, bottom: 12, left: 18 },
                font: {
                    multi: 'html',
                    face: 'system-ui, -apple-system, sans-serif',
                    bold: { size: 13, color: '#0f172a', face: 'system-ui, sans-serif' },
                    ital: { size: 10, color: '#64748b', face: 'system-ui, sans-serif', mod: 'normal' }
                },
                shadow: {
                    enabled: true,
                    color: 'rgba(15, 23, 42, 0.08)',
                    size: 8,
                    x: 0,
                    y: 3
                }
            },
            edges: {
                arrows: {
                    to: { enabled: true, scaleFactor: 0.75, type: 'arrow' }
                },
                smooth: {
                    type: 'cubicBezier',
                    forceDirection: 'horizontal',
                    roundness: 0.5
                },
                width: 2,
                hoverWidth: 3,
                selectionWidth: 3.5
            },
            interaction: {
                hover: true,
                dragNodes: false,
                zoomView: true,
                dragView: true
            }
        };

        network = new Network(container, data, options);

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

        network.on("dragStart", function () {
            window.dispatchEvent(new Event('close-context-menu'));
        });

        window.addEventListener('network-updated', (event) => {
            allNetworkData = event.detail.data;
            nodes.clear();
            edges.clear();
            nodes.add(formatNodes(allNetworkData.nodes));
            edges.add(formatEdges(allNetworkData.edges));
            network.fit();
        });

        window.addEventListener('filter-graph', (e) => {
            const visibleIds = e.detail.visibleIds;
            if (!visibleIds || visibleIds.length === 0) {
                nodes.clear();
                edges.clear();
                nodes.add(formatNodes(allNetworkData.nodes));
                edges.add(formatEdges(allNetworkData.edges));
            } else {
                const filteredNodes = formatNodes(allNetworkData.nodes).filter(n => visibleIds.includes(n.id));
                const filteredEdges = formatEdges(allNetworkData.edges).filter(edge => visibleIds.includes(edge.from) && visibleIds.includes(edge.to));
                nodes.clear();
                edges.clear();
                nodes.add(filteredNodes);
                edges.add(filteredEdges);
            }
            network.fit();
        });
    });
</script>
