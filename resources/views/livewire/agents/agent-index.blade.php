<div class="py-8 relative min-h-screen bg-gray-50">
    <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Padrón de Personal</h2>
                <p class="font-secondary text-gray-500 mt-1">Gestión central de agentes, legajos digitales y accesos a
                    HSI.</p>
            </div>
            <div class="flex items-center gap-3">
                <button wire:click="$set('showExportModal', true)"
                    class="inline-flex items-center gap-1 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-bold rounded-md transition-colors font-secondary shadow-sm">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-gray-500" />
                    Exportar
                </button>
                @can('crear.agente')
                    <button wire:click="$set('showCreateModal', true)"
                        class="inline-flex items-center gap-1 px-4 py-2 bg-brand-cyan hover:bg-brand-cyan-dark text-white text-sm font-bold rounded-md transition-colors font-secondary shadow-sm">
                        <x-heroicon-o-user-plus class="w-4 h-4" />
                        Nuevo Agente
                    </button>
                @endcan
            </div>
        </div>

        <div
            class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6 flex flex-wrap lg:flex-nowrap gap-4 items-end">

            <div class="w-full lg:w-72">
                <label for="search"
                    class="block text-[11px] font-bold text-gray-500 font-secondary uppercase tracking-wider mb-1">
                    Buscar Agente
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="h-4 w-4 text-gray-400" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" id="search" type="text"
                        placeholder="DNI, Apellidos, Nombres..."
                        class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary transition-colors">
                </div>
            </div>

            <div class="w-full flex-1 min-w-[200px]">
                <x-searchable-select wire:model.live="service_id" label="Servicio Base" placeholder="Buscar servicio..."
                    defaultText="Todos los servicios" :options="$services->map(fn($service) => ['id' => $service->id, 'name' => $service->name])->values()->toArray()" />
            </div>

            <div class="w-full flex-1 min-w-[200px]">
                <x-searchable-select wire:model.live="profession_id" label="Profesión" placeholder="Buscar profesión..."
                    defaultText="Todas" :options="collect($professions ?? [])->map(fn($profession) => ['id' => $profession->id, 'name' => $profession->name])->values()->toArray()" />
            </div>

            <div class="w-full flex-1 min-w-[200px]">
                <x-searchable-select wire:model.live="status" label="Estado" placeholder="Buscar estado..."
                    defaultText="Todos" :options="collect(\App\Enums\AgentStatus::cases())->map(fn($status) => ['id' => $status->value, 'name' => $status->name])->values()->toArray()" />
            </div>

        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider font-secondary">
                                Agente</th>
                            <th scope="col"
                                class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider font-secondary">
                                Servicio y Profesión</th>
                            <th scope="col"
                                class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider font-secondary">
                                Documentos Requeridos</th>
                            <th scope="col"
                                class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider font-secondary">
                                Roles HSI</th>
                            <th scope="col"
                                class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider font-secondary">
                                Acceso HSI</th>
                            <th scope="col"
                                class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wider font-secondary">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">

                        @forelse($agents as $agent)
                            @php
                                $initials = mb_substr($agent->first_name, 0, 1) . mb_substr($agent->last_name, 0, 1);
                                $statusEnum = $agent->status instanceof \App\Enums\AgentStatus ? $agent->status : \App\Enums\AgentStatus::tryFrom($agent->status);
                                $isActive = $statusEnum !== \App\Enums\AgentStatus::INACTIVO;
                            @endphp

                            <tr
                                class="hover:bg-gray-50/80 transition-colors group {{ !$isActive ? 'opacity-75 grayscale-[50%]' : '' }}">

                                <td class="px-5 py-4 whitespace-nowrap align-middle">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-9 w-9 rounded-full bg-white flex items-center justify-center font-bold text-sm border-2 {{ $statusEnum?->color() ?? 'bg-gray-100 text-gray-500 border-gray-200' }}">
                                            {{ strtoupper($initials) }}
                                        </div>
                                        <div>
                                            <div
                                                class="text-sm font-bold text-gray-900 uppercase group-hover:text-brand-cyan transition-colors">
                                                {{ $agent->last_name }}, {{ $agent->first_name }}
                                            </div>
                                            <div class="text-xs text-gray-500 font-secondary mt-0.5">
                                                DNI: {{ number_format($agent->dni, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap align-middle">
                                    <div class="text-sm text-gray-900 font-medium mb-1">
                                        {{ $agent->service ? $agent->service->name : 'Sin Servicio Base' }}
                                    </div>
                                    <div class="text-xs text-gray-500 font-secondary flex flex-col gap-0.5">
                                        @forelse ($agent->agentProfessions as $vinculacion)
                                            <div>
                                                <span
                                                    class="text-gray-700 font-semibold">{{ $vinculacion->profession?->name ?? 'Sin profesión' }}</span>
                                                @if($vinculacion->specialty?->name)
                                                    <span class="text-gray-400">- {{ $vinculacion->specialty->name }}</span>
                                                @endif
                                                @if(!empty($vinculacion->license))
                                                    <span class="text-[10px] text-brand-blue ml-1 font-mono">(MP:
                                                        {{ $vinculacion->license }})</span>
                                                @endif
                                            </div>
                                        @empty
                                            <span class="italic text-gray-400">Sin profesión asignada</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td class="px-5 py-4 align-middle max-w-xs">
                                    @if(($agent->document_status['state'] ?? '') === 'N/A')
                                        <span class="text-xs text-gray-400 font-secondary italic">Sin requisitos</span>
                                    @else
                                        <div class="flex flex-wrap gap-1.5 items-center">
                                            @foreach($agent->document_status['uploaded_docs'] ?? [] as $docName)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded bg-green-50 px-2 py-0.5 text-[10px] text-green-700 font-secondary font-bold uppercase ring-1 ring-inset ring-green-600/20 border border-green-200 whitespace-nowrap">
                                                    <x-heroicon-s-check-circle class="w-3.5 h-3.5 shrink-0" /> {{ $docName }}
                                                </span>
                                            @endforeach
                                            @foreach($agent->document_status['missing_docs'] ?? [] as $docName)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded bg-red-50 px-2 py-0.5 text-[10px] text-brand-pink font-secondary font-bold uppercase ring-1 ring-inset ring-brand-pink/20 border border-red-100 whitespace-nowrap">
                                                    <x-heroicon-s-x-circle class="w-3.5 h-3.5 shrink-0" /> {{ $docName }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-4 align-middle">
                                    @if($isActive && $agent->hsiRoles->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 items-center">
                                            @foreach($agent->hsiRoles as $role)
                                                <span
                                                    class="inline-flex items-center rounded bg-gray-100 px-1.5 py-0.5 text-[9px] font-bold text-gray-700 border border-gray-200 uppercase font-secondary whitespace-nowrap">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 font-secondary italic">Sin roles</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap align-middle">
                                    @php
                                        // Si usas el estado directo del modelo, es más limpio: $status = $agent->status;
                                        // Si dependes del array 'hsi_access_status', mantén esta lógica:
                                        $statusValue = $agent->hsi_access_status['value'] ?? null;
                                        $status = null;
                                        if ($statusValue !== null) {
                                            try {
                                                $status = \App\Enums\AgentStatus::tryFrom($statusValue);
                                            } catch (\Throwable $e) {
                                                $status = null;
                                            }
                                        }
                                    @endphp
                                    <span
                                        class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold ring-1 ring-inset font-secondary {{ $status ? $status->color() : ($agent->hsi_access_status['color'] ?? 'bg-gray-50 text-gray-500') }}">
                                        {{ $status ? $status->label() : ($agent->hsi_access_status['label'] ?? 'DESCONOCIDO') }}
                                    </span>
                                </td>


                                <td class="px-5 py-4 whitespace-nowrap text-right align-middle">
                                    <div class="flex items-center justify-end gap-2">
                                        @if(($agent->document_status['state'] ?? '') === 'FALTANTES')
                                            @can('editar.documentos')
                                                <a href="{{ route('agents.show', $agent->id) }}?tab=documentos" wire:navigate
                                                    class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-brand-pink hover:bg-[#c2145d] text-white rounded-md text-xs font-bold transition-colors font-secondary shadow-sm">
                                                    Completar
                                                </a>
                                            @endcan
                                        @endif
                                        <a href="{{ route('agents.show', $agent->id) }}" wire:navigate
                                            class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-xs font-bold transition-colors font-secondary shadow-sm">
                                            <x-heroicon-o-folder-open class="w-4 h-4" /> Legajo
                                        </a>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    class="px-5 py-16 text-center text-gray-500 font-secondary text-sm bg-gray-50/50">
                                    <x-heroicon-o-users class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                                    No se encontraron agentes en el padrón con los filtros actuales.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            @if($agents->hasPages())
                <div class="bg-gray-50 px-5 py-4 border-t border-gray-200">
                    {{ $agents->links() }}
                </div>
            @endif
        </div>

    </div>

    @if($showExportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"
                    wire:click="$set('showExportModal', false)" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-t-4 border-brand-cyan">

                    <form action="{{ route('agents.print') }}" target="_blank" method="GET">
                        <div class="bg-white px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <x-heroicon-o-printer class="w-6 h-6 text-brand-cyan" />
                                Exportar Padrón
                            </h3>
                            <p class="text-xs text-gray-500 font-secondary mt-1">
                                Seleccioná los filtros para generar la vista de impresión en una nueva pestaña.
                            </p>
                        </div>

                        <div class="bg-gray-50 px-6 py-5 space-y-4">
                            <div>
                                <label
                                    class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Estado
                                    a Exportar</label>
                                <select name="status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary">
                                    <option value="">Todos</option>
                                    <option value="Activos">Solo Activos</option>
                                    <option value="Inactivos">Solo Inactivos</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Servicio
                                    Base</label>
                                <select name="service_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary">
                                    <option value="">Todos los servicios</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Profesión</label>
                                <select name="profession_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary">
                                    <option value="">Todas</option>
                                    @foreach($professions as $profession)
                                        <option value="{{ $profession->id }}">{{ $profession->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="bg-white px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-3">
                            <button type="button" wire:click="$set('showExportModal', false)"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold rounded-md transition-colors font-secondary">
                                Cancelar
                            </button>
                            <button type="submit" wire:click="$set('showExportModal', false)"
                                class="inline-flex items-center gap-1 px-4 py-2 bg-brand-cyan hover:bg-brand-cyan-dark text-white text-sm font-bold rounded-md transition-colors font-secondary shadow-sm">
                                <x-heroicon-o-document-text class="w-4 h-4" />
                                Generar Reporte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"
                    wire:click="$set('showCreateModal', false)" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border-t-4 border-brand-cyan">

                    <form wire:submit="saveAgent">
                        <div class="bg-white px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <x-heroicon-o-user-plus class="w-6 h-6 text-brand-cyan" />
                                Alta de Agente
                            </h3>
                            <p class="text-xs text-gray-500 font-secondary mt-1">
                                Completá los datos formales del agente. Si el DNI ya se encuentra registrado (incluso si fue
                                dado de baja), el sistema reactivará su ficha automáticamente.
                            </p>
                        </div>

                        <div class="bg-gray-50 px-6 py-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">

                                <div
                                    class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 pb-4 border-b border-gray-200">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">DNI
                                            *</label>
                                        <input type="number" wire:model="new_dni"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary"
                                            required>
                                        @error('new_dni') <span
                                        class="text-xs text-brand-pink font-bold mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Género
                                            Registrado *</label>
                                        <select wire:model="new_gender"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary uppercase"
                                            required>
                                            <option value="">Seleccione...</option>
                                            @foreach($genders as $gender)
                                                <option value="{{ $gender->value }}">{{ $gender->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('new_gender') <span
                                        class="text-xs text-brand-pink font-bold mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-span-2">
                                        <x-searchable-select wire:model="new_service_id" label="Servicio Base (Planta)"
                                            placeholder="Escriba para buscar..." :options="$services->map(function ($s) {
            return ['id' => $s->id, 'name' => $s->name]; })->values()->toArray()" required />
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Primer
                                        Nombre *</label>
                                    <input type="text" wire:model="new_first_name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary uppercase"
                                        required>
                                    @error('new_first_name') <span
                                    class="text-xs text-brand-pink font-bold mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Segundo
                                        Nombre</label>
                                    <input type="text" wire:model="new_second_first_name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary uppercase">
                                    @error('new_second_first_name') <span
                                    class="text-xs text-brand-pink font-bold mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Primer
                                        Apellido *</label>
                                    <input type="text" wire:model="new_last_name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary uppercase"
                                        required>
                                    @error('new_last_name') <span
                                    class="text-xs text-brand-pink font-bold mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Segundo
                                        Apellido</label>
                                    <input type="text" wire:model="new_second_last_name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary uppercase">
                                    @error('new_second_last_name') <span
                                    class="text-xs text-brand-pink font-bold mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div
                                    class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Teléfono
                                            de Contacto *</label>
                                        <input type="text" wire:model="new_phone"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary"
                                            required>
                                        @error('new_phone') <span
                                        class="text-xs text-brand-pink font-bold mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Correo
                                            Electrónico *</label>
                                        <input type="email" wire:model="new_email"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary lowercase"
                                            required>
                                        @error('new_email') <span
                                        class="text-xs text-brand-pink font-bold mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="bg-white px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-3">
                            <button type="button" wire:click="$set('showCreateModal', false)"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold rounded-md transition-colors font-secondary">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="inline-flex items-center gap-1 px-4 py-2 bg-brand-cyan hover:bg-brand-cyan-dark text-white text-sm font-bold rounded-md transition-colors font-secondary shadow-sm">
                                Guardar y Continuar
                                <x-heroicon-m-arrow-right class="w-4 h-4" />
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>