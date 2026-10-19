<div class="py-8 relative min-h-screen bg-gray-50">
    <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8">

        <!-- CABECERA DE LA SECCIÓN UNIVERSIDAD -->
        <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2 font-secondary">
                    <x-heroicon-o-academic-cap class="w-7 h-7 text-brand-cyan" />
                    Padrón Docente UNLP
                </h2>
                <p class="font-secondary text-gray-500 mt-1">Gestión de agentes docentes, convenios universitarios y vigencias de Anexo I.</p>
            </div>
        </div>

        <!-- TARJETAS DE KPIS -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs">
                <span class="text-[10px] font-bold text-gray-400 uppercase font-secondary">Total Registrados</span>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $kpiTotal }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl border border-emerald-200 shadow-2xs bg-emerald-50/20">
                <span class="text-[10px] font-bold text-emerald-600 uppercase font-secondary">Activos (Vigentes)</span>
                <p class="text-2xl font-bold text-emerald-700 mt-1">{{ $kpiActivos }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl border border-amber-200 shadow-2xs bg-amber-50/20">
                <span class="text-[10px] font-bold text-amber-600 uppercase font-secondary">Pendiente Alta HSI</span>
                <p class="text-2xl font-bold text-amber-700 mt-1">{{ $kpiPendientes }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl border border-orange-200 shadow-2xs bg-orange-50/20">
                <span class="text-[10px] font-bold text-orange-600 uppercase font-secondary">Por Vencer (≤ 15 días)</span>
                <p class="text-2xl font-bold text-orange-700 mt-1">{{ $kpiPorVencer }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl border border-rose-200 shadow-2xs bg-rose-50/20">
                <span class="text-[10px] font-bold text-brand-pink uppercase font-secondary">Vencidos / Pend. Baja</span>
                <p class="text-2xl font-bold text-brand-pink mt-1">{{ $kpiVencidos }}</p>
            </div>
        </div>

        <!-- PANEL DE FILTROS -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1.5">Buscar Docente</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <x-heroicon-o-magnifying-glass class="h-4 w-4 text-gray-400" />
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="DNI, nombre, materia, mail UNLP..."
                            class="pl-10 block w-full h-[42px] rounded-lg border-gray-300 shadow-sm focus:border-brand-cyan sm:text-xs font-secondary">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1.5">Estado HSI UNLP</label>
                    <select wire:model.live="hsi_status_filter" class="w-full h-[42px] rounded-lg border-gray-300 shadow-sm focus:border-brand-cyan sm:text-xs font-secondary">
                        <option value="">Todos los Estados</option>
                        <option value="pendiente_alta">Pendiente Alta HSI</option>
                        <option value="activo">Activo (Vigente)</option>
                        <option value="pendiente_renovacion">Por Vencer</option>
                        <option value="vencido">Vencido</option>
                        <option value="revocado">Revocado</option>
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold text-gray-700 font-secondary uppercase tracking-wider">Roles HSI</label>
                        <button type="button" wire:click="toggleOperator('roles')" class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded border {{ $roles_operator === 'in' ? 'bg-cyan-50 text-brand-cyan-dark border-cyan-200' : 'bg-rose-50 text-brand-pink border-rose-200' }}">
                            {{ $roles_operator === 'in' ? 'TIENE' : 'NO TIENE' }}
                        </button>
                    </div>
                    <x-multi-searchable-select wire:model.live="role_ids" label="" placeholder="Buscar roles..." defaultText="Todos los roles" :options="$hsiRoles->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->values()->toArray()" />
                </div>
            </div>

            @if($this->hasActiveFilters())
                <div class="pt-3 border-t border-gray-100 flex justify-end">
                    <button wire:click="clearAllFilters" type="button" class="inline-flex items-center gap-1 text-xs font-bold text-brand-pink hover:underline uppercase font-secondary">
                        <x-heroicon-o-trash class="w-4 h-4" /> Limpiar Filtros
                    </button>
                </div>
            @endif
        </div>

        <!-- TABLA DE RESULTADOS -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider font-secondary">Agente / Docente</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider font-secondary">Materia y Cátedra</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider font-secondary">Mails Institucional / Respaldo</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider font-secondary">Estado HSI & Vigencia</th>
                            <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wider font-secondary">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($agents as $agent)
                            @php
                                $acad = $agent->academicProfile;
                                $badge = $acad->hsi_status_badge;
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900 uppercase">
                                        {{ $agent->last_name }} {{ $agent->second_last_name ?? '' }}, {{ $agent->first_name }} {{ $agent->second_first_name ?? '' }}
                                    </div>
                                    <div class="text-xs text-gray-500 font-secondary mt-0.5">
                                        DNI: {{ number_format($agent->dni, 0, ',', '.') }} | Serv: <span class="font-bold text-brand-cyan-dark">UNLP</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm font-bold text-gray-800">{{ $acad->materia }}</div>
                                    <div class="text-xs text-gray-500 font-secondary">{{ $acad->carrera_catedra }} ({{ $acad->cargo_docente }})</div>
                                </td>
                                <td class="px-5 py-4 font-mono text-xs">
                                    <div class="text-brand-cyan-dark font-bold">UNLP: {{ $acad->unlp_email }}</div>
                                    <div class="text-gray-400">Pers: {{ $agent->email }}</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold ring-1 ring-inset {{ $badge['color'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                    <div class="text-[11px] font-secondary text-gray-500 mt-1 font-bold">
                                        Vence: {{ $acad->expires_at->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('agents.show', $agent->id) }}" wire:navigate
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-xs font-bold transition-colors font-secondary shadow-sm">
                                        <x-heroicon-o-folder-open class="w-4 h-4 text-brand-cyan" /> Legajo Docente
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-gray-400 font-secondary text-sm">
                                    No se encontraron docentes universitarios registrados.
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

    <!-- MODAL DE ALTA DE DOCENTE UNLP -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm" wire:click="$set('showCreateModal', false)"></div>
                <div class="relative w-full max-w-4xl bg-white rounded-xl shadow-2xl overflow-hidden border-t-4 border-brand-cyan z-10">
                    <form wire:submit="saveAcademicAgent">
                        <div class="bg-white px-6 py-5 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 font-secondary">
                                <x-heroicon-o-user-plus class="w-6 h-6 text-brand-cyan" />
                                Alta de Docente (Extensión de Agente Padrón)
                            </h3>
                            <button type="button" wire:click="$set('showCreateModal', false)" class="text-gray-400 hover:text-gray-600">
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="p-6 space-y-6 text-xs font-secondary max-h-[75vh] overflow-y-auto bg-gray-50">
                            
                            <!-- SECCIÓN 1: DATOS DE AGENTE BASE -->
                            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-2xs space-y-4">
                                <div class="flex justify-between items-center border-b pb-2">
                                    <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider text-brand-cyan-dark">
                                        1. Ficha del Agente Base
                                    </h4>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-50 text-brand-cyan-dark border border-cyan-200">
                                        Servicio: UNLP
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">DNI *</label>
                                        <input type="number" wire:model="new_dni" class="w-full rounded-md border-gray-300 shadow-2xs focus:border-brand-cyan">
                                        @error('new_dni') <span class="text-brand-pink text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Género Registrado *</label>
                                        <select wire:model="new_gender" class="w-full rounded-md border-gray-300 uppercase shadow-2xs focus:border-brand-cyan">
                                            <option value="">Seleccione...</option>
                                            @foreach($genders as $gender)
                                                <option value="{{ $gender->value }}">{{ $gender->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('new_gender') <span class="text-brand-pink text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Primer Nombre *</label>
                                        <input type="text" wire:model="new_first_name" class="w-full rounded-md border-gray-300 uppercase focus:border-brand-cyan">
                                        @error('new_first_name') <span class="text-brand-pink text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Segundo Nombre</label>
                                        <input type="text" wire:model="new_second_first_name" class="w-full rounded-md border-gray-300 uppercase focus:border-brand-cyan">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Primer Apellido *</label>
                                        <input type="text" wire:model="new_last_name" class="w-full rounded-md border-gray-300 uppercase focus:border-brand-cyan">
                                        @error('new_last_name') <span class="text-brand-pink text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Segundo Apellido</label>
                                        <input type="text" wire:model="new_second_last_name" class="w-full rounded-md border-gray-300 uppercase focus:border-brand-cyan">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Teléfono de Contacto *</label>
                                        <input type="text" wire:model="new_phone" class="w-full rounded-md border-gray-300">
                                        @error('new_phone') <span class="text-brand-pink text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Correo Electrónico Personal (Respaldo) *</label>
                                        <input type="email" wire:model="new_email" class="w-full rounded-md border-gray-300 lowercase">
                                        @error('new_email') <span class="text-brand-pink text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN 2: CAMPOS UNLP Y CONVENIO -->
                            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-2xs space-y-4">
                                <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b pb-2 text-indigo-800">
                                    2. Datos de Cátedra y Convenio UNLP
                                </h4>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Mail Institucional UNLP *</label>
                                    <input type="email" wire:model="new_unlp_email" placeholder="ejemplo@med.unlp.edu.ar" class="w-full rounded-md border-gray-300 font-mono text-xs">
                                    @error('new_unlp_email') <span class="text-brand-pink text-[10px] block mt-0.5">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Facultad *</label>
                                        <input type="text" wire:model="new_facultad" class="w-full rounded-md border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Materia *</label>
                                        <input type="text" wire:model="new_materia" class="w-full rounded-md border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Carrera / Cátedra *</label>
                                        <input type="text" wire:model="new_carrera_catedra" class="w-full rounded-md border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Cargo / Legajo UNLP *</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="text" wire:model="new_cargo_docente" placeholder="Prof. Adjunto" class="w-full rounded-md border-gray-300">
                                            <input type="text" wire:model="new_legajo_unlp" placeholder="Legajo" class="w-full rounded-md border-gray-300">
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Autoridad UNLP que Firma *</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="text" wire:model="new_autoridad_nombre" placeholder="Nombre" class="w-full rounded-md border-gray-300">
                                            <input type="text" wire:model="new_autoridad_cargo" placeholder="Cargo" class="w-full rounded-md border-gray-300">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Vigencia Hasta *</label>
                                        <input type="date" wire:model="new_expires_at" class="w-full rounded-md border-gray-300 font-mono text-xs">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                            <p class="text-[11px] text-gray-500 font-secondary">
                                * El Anexo I se sube desde la pestaña <strong>Legajo Digital</strong> del docente.
                            </p>
                            <div class="flex items-center gap-3">
                                <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 bg-white border border-gray-300 text-gray-800 text-xs font-bold rounded-md font-secondary">
                                    Cancelar
                                </button>
                                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2 bg-brand-cyan hover:bg-cyan-600 text-white text-xs font-bold rounded-md shadow-2xs font-secondary">
                                    Registrar y Abrir Legajo
                                    <x-heroicon-m-arrow-right class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>