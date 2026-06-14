<div class="py-8 relative">
    <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 flex items-center justify-between">
            <div>
                <x-banner text="Logs de Auditoría"></x-banner>
                <p class="font-secondary text-brand-gray-custom mt-2">Registro inalterable de actividad y trazabilidad del sistema.</p>
            </div>
            <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-medium text-brand-cyan hover:text-brand-cyan-dark transition-colors flex items-center gap-1">
                <x-heroicon-m-arrow-left class="w-4 h-4" />
                Volver a Inicio
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <div class="lg:col-span-1">
                <div class="bg-white shadow-sm rounded-xl border-t-4 border-gray-800 overflow-hidden sticky top-6">
                    <div class="p-5">
                        <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5 text-gray-800" />
                            Filtros de Búsqueda
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label for="search" class="block font-secondary text-xs font-medium text-gray-700">Término o Módulo</label>
                                <input wire:model.live.debounce.300ms="search" type="text" id="search" placeholder="Ej: User, created..." 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-800 focus:ring focus:ring-gray-800 focus:ring-opacity-20 sm:text-sm transition-colors">
                            </div>

                            <div>
                                <label for="event" class="block font-secondary text-xs font-medium text-gray-700">Tipo de Acción</label>
                                <select wire:model.live="event" id="event" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-800 focus:ring focus:ring-gray-800 focus:ring-opacity-20 sm:text-sm font-secondary">
                                    <option value="">Todos los eventos</option>
                                    <option value="created">Creación (Created)</option>
                                    <option value="updated">Actualización (Updated)</option>
                                    <option value="deleted">Eliminación/Desactivación (Deleted)</option>
                                    <option value="restored">Restauración (Restored)</option>
                                </select>
                            </div>

                            <div>
                                <label for="dateFrom" class="block font-secondary text-xs font-medium text-gray-700">Desde la fecha</label>
                                <input wire:model.live="dateFrom" type="date" id="dateFrom" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-800 focus:ring focus:ring-gray-800 focus:ring-opacity-20 sm:text-sm font-secondary">
                            </div>

                            <div>
                                <label for="dateTo" class="block font-secondary text-xs font-medium text-gray-700">Hasta la fecha</label>
                                <input wire:model.live="dateTo" type="date" id="dateTo" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-800 focus:ring focus:ring-gray-800 focus:ring-opacity-20 sm:text-sm font-secondary">
                            </div>

                            <div class="pt-2">
                                <button type="button" wire:click="clearFilters" class="w-full flex justify-center items-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 transition-colors">
                                    <x-heroicon-m-x-mark class="w-4 h-4 mr-1" />
                                    Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-800">
                                <tr>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-white uppercase tracking-wider font-secondary">Fecha y Hora</th>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-white uppercase tracking-wider font-secondary">Usuario (Causer)</th>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-white uppercase tracking-wider font-secondary">Acción / Evento</th>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-white uppercase tracking-wider font-secondary">Módulo Afectado</th>
                                    <th scope="col" class="px-5 py-3 text-right text-xs font-bold text-white uppercase tracking-wider font-secondary">Detalles</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($logs as $log)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        
                                        <td class="px-5 py-4 whitespace-nowrap align-top">
                                            <div class="text-sm font-bold text-gray-900">{{ $log->created_at->format('d/m/Y') }}</div>
                                            <div class="text-xs text-gray-500 font-secondary">{{ $log->created_at->format('H:i:s') }} hs</div>
                                        </td>

                                        <td class="px-5 py-4 whitespace-nowrap align-top">
                                            @if($log->causer)
                                                <div class="flex flex-col">
                                                    @if($log->causer->agent)
                                                        <span class="text-sm font-bold text-gray-900 uppercase">
                                                            {{ $log->causer->agent->last_name }}, {{ $log->causer->agent->first_name }}
                                                        </span>
                                                    @endif
                                                    <span class="text-xs font-secondary text-brand-blue font-medium mt-0.5">
                                                        DNI: {{ number_format($log->causer->dni ?? 0, 0, ',', '.') }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-xs font-secondary text-gray-400 italic">Sistema / Consola</span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 whitespace-nowrap align-top">
                                            @php
                                                $badgeClasses = match($log->event) {
                                                    'created' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                    'updated' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                                    'deleted' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                    'restored' => 'bg-teal-50 text-teal-700 ring-teal-600/20',
                                                    default => 'bg-gray-50 text-gray-600 ring-gray-500/10'
                                                };
                                                
                                                $eventName = match($log->event) {
                                                    'created' => 'CREACIÓN',
                                                    'updated' => 'ACTUALIZACIÓN',
                                                    'deleted' => 'ELIMINACIÓN',
                                                    'restored' => 'RESTAURACIÓN',
                                                    default => mb_strtoupper($log->event)
                                                };
                                            @endphp
                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-[10px] font-bold ring-1 ring-inset font-secondary tracking-wider {{ $badgeClasses }}">
                                                {{ $eventName }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 align-top max-w-xs">
                                            <div class="text-xs font-bold text-gray-600 font-secondary mb-1">
                                                ID: {{ $log->subject_id }} | 
                                                {{ class_basename($log->subject_type) }}
                                            </div>
                                            <p class="text-sm text-gray-900 truncate" title="{{ $log->description }}">
                                                {{ $log->description }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 whitespace-nowrap text-right align-top">
                                            <button wire:click="showDetails({{ $log->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-xs font-bold transition-colors font-secondary">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                                Inspeccionar
                                            </button>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-10 text-center text-gray-500 font-secondary text-sm">
                                            <x-heroicon-o-document-magnifying-glass class="w-10 h-10 mx-auto text-gray-300 mb-3" />
                                            No se encontraron registros de auditoría con los filtros actuales.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($logs->hasPages())
                        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @if($showingDetails && $selectedLog)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" wire:click="closeDetails" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border-t-4 border-brand-cyan">
                    
                    <div class="bg-white px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="bg-brand-cyan/10 p-2 rounded-full">
                                <x-heroicon-o-magnifying-glass-circle class="w-6 h-6 text-brand-cyan" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 leading-tight">Auditoría del Registro #{{ $selectedLog->id }}</h3>
                                <p class="text-xs font-secondary text-gray-500">{{ class_basename($selectedLog->subject_type) }} (ID: {{ $selectedLog->subject_id }})</p>
                            </div>
                        </div>
                        <button wire:click="closeDetails" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <x-heroicon-m-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    <div class="bg-gray-50 p-6 overflow-y-auto max-h-[60vh]">
                        
                        @php
                            $rawData = $selectedLog->attribute_changes ?? $selectedLog->properties ?? [];
                            $changes = is_string($rawData) ? collect(json_decode($rawData, true)) : collect($rawData);
                            
                            $newValues = $changes->get('attributes', []);
                            $oldValues = $changes->get('old', []);
                            
                            $changedKeys = array_unique(array_merge(array_keys($newValues), array_keys($oldValues)));
                        @endphp

                        @if(count($changedKeys) > 0)
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-100 font-secondary">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700 w-1/3 border-r border-gray-200">Atributo</th>
                                            @if($selectedLog->event === 'updated' || $selectedLog->event === 'deleted')
                                                <th scope="col" class="px-4 py-3 text-left font-bold text-brand-pink w-1/3 border-r border-gray-200">Valor Anterior</th>
                                            @endif
                                            @if($selectedLog->event === 'updated' || $selectedLog->event === 'created' || $selectedLog->event === 'restored')
                                                <th scope="col" class="px-4 py-3 text-left font-bold text-brand-cyan-dark w-1/3">Valor Nuevo</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white font-mono text-xs">
                                        @foreach($changedKeys as $key)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 font-bold text-gray-600 border-r border-gray-200">
                                                    {{ $key }}
                                                </td>
                                                
                                                @if($selectedLog->event === 'updated' || $selectedLog->event === 'deleted')
                                                    <td class="px-4 py-3 text-red-600 bg-red-50/30 border-r border-gray-200 break-all">
                                                        @if(is_array($oldValues[$key] ?? null))
                                                            {{ json_encode($oldValues[$key]) }}
                                                        @else
                                                            {{ (string) ($oldValues[$key] ?? 'N/A') }}
                                                        @endif
                                                    </td>
                                                @endif

                                                @if($selectedLog->event === 'updated' || $selectedLog->event === 'created' || $selectedLog->event === 'restored')
                                                    <td class="px-4 py-3 text-green-700 bg-green-50/30 break-all">
                                                        @if(is_array($newValues[$key] ?? null))
                                                            {{ json_encode($newValues[$key]) }}
                                                        @else
                                                            {{ (string) ($newValues[$key] ?? 'N/A') }}
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <x-heroicon-o-document-text class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                                <p class="text-gray-500 font-secondary text-sm">Este evento no registró cambios detallados de atributos en la base de datos.</p>
                                <p class="text-xs text-gray-400 mt-1">Descripción original: "{{ $selectedLog->description }}"</p>
                            </div>
                        @endif

                    </div>

                    <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                        <div>
                            @if($selectedLog->event === 'updated')
                                <button type="button" 
                                    wire:click="revertLog({{ $selectedLog->id }})" 
                                    wire:confirm="¿Estás seguro de que deseás deshacer esta actualización y restaurar los valores anteriores? Esta acción generará un nuevo registro de auditoría con la reversión."
                                    class="inline-flex items-center gap-1 px-4 py-2 bg-brand-pink hover:bg-[#c2145d] text-white text-sm font-bold rounded-md transition-colors font-secondary shadow-sm">
                                    <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                                    Revertir a Valores Anteriores
                                </button>
                            @endif
                        </div>
                        <button type="button" wire:click="closeDetails" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold rounded-md transition-colors font-secondary">
                            Cerrar Panel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>