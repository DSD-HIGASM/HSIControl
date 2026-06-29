<div class="min-h-screen bg-gray-50 py-8 relative" x-data="{ activeTab: @entangle('tab') }">
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center gap-2 text-sm font-secondary text-gray-500">
                <a href="{{ route('agents.index') }}" wire:navigate
                    class="hover:text-brand-cyan transition-colors flex items-center gap-1 font-bold">
                    <x-heroicon-m-arrow-left class="w-4 h-4" /> Volver al Padrón
                </a>
                <x-heroicon-m-chevron-right class="w-4 h-4 text-gray-400" />
                <span class="text-gray-900 font-bold truncate">
                    Legajo #{{ $agent->id }} - {{ mb_strtoupper($agent->last_name) }},
                    {{ mb_strtoupper($agent->first_name) }}
                </span>
            </div>

            <div class="flex items-center gap-3">
                @if (($agent->gender) && ($agent->gender->value == 'pendiente'))
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 border border-red-200 text-red-700 text-xs font-bold rounded-lg shadow-sm">
                        <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-red-500" />
                        Género pendiente
                    </span>
                @endif

                @if($agent->agentProfessions && $agent->agentProfessions->contains('profession_id', 17))
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold rounded-lg shadow-sm"
                        title="Esta jefatura está cargada como profesión y debe corregirse">
                        <x-heroicon-s-user class="w-4 h-4 text-blue-500" />
                        Jefe de Servicio (A Corregir)
                    </span>
                @endif

                <a href="{{ route('agents.print_ficha', $agent->id) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-colors shadow-sm font-secondary group">
                    <x-heroicon-o-printer class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-colors" />
                    Imprimir Ficha
                </a>
            </div>
        </div>

        @php
            $initials = mb_substr($agent->first_name, 0, 1) . mb_substr($agent->last_name, 0, 1);
            $statusValue = is_object($agent->status) ? $agent->status->value : $agent->status;
            $isActive = mb_strtoupper($statusValue) === 'ACTIVO';
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1.5 {{ $agent->status->color() ?? 'bg-gray-400' }}"></div>
            <div class="flex flex-col md:flex-row gap-6 items-start md:items-center">
                <div class="h-20 w-20 rounded-full bg-white flex items-center justify-center font-bold text-2xl border-4 shrink-0 {{ $agent->status->color() ?? 'border-gray-200 text-gray-500' }}">
                    {{ strtoupper($initials) }}
                </div>

                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-1">
                        <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-tight">
                            {{ $agent->last_name }} {{ $agent->second_last_name }}, {{ $agent->first_name }} {{ $agent->second_first_name }}
                        </h1>
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-[10px] font-bold ring-1 ring-inset font-secondary tracking-wider {{ $agent->status->color() ?? 'bg-gray-100' }}">
                            {{ strtoupper($statusValue) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-y-3 gap-x-6 mt-4 text-sm font-secondary">
                        <div class="flex items-center gap-2 text-gray-600">
                            <x-heroicon-o-identification class="w-4 h-4 text-gray-400" />
                            <span class="font-bold text-gray-800">DNI:</span> {{ number_format($agent->dni, 0, ',', '.') }}
                        </div>

                        @if($agent->person_id)
                            <a href="https://shc.ms.gba.gov.ar/institucion/484/pacientes/profile/{{ $agent->person_id }}"
                                target="_blank"
                                class="flex items-center gap-2 text-brand-cyan hover:text-brand-cyan-dark transition-colors group">
                                <x-heroicon-o-finger-print class="w-4 h-4" />
                                <span class="font-bold text-gray-800 group-hover:text-brand-cyan-dark">ID HSI:</span> {{ $agent->person_id }}
                            </a>
                        @else
                            <div class="flex items-center gap-2 text-gray-400 italic">
                                <x-heroicon-o-finger-print class="w-4 h-4" />
                                <span class="font-bold">ID HSI:</span> No asignado
                            </div>
                        @endif

                        <div class="flex items-center gap-2 text-gray-600">
                            <x-heroicon-o-envelope class="w-4 h-4 text-gray-400" />
                            <span class="font-bold text-gray-800">Email:</span> {{ $agent->email ?? 'No registrado' }}
                        </div>

                        <a href="https://wa.me/549{{ preg_replace('/[^0-9]/', '', $agent->phone) }}" target="_blank"
                            class="flex items-center gap-2 text-green-600 hover:text-green-700 transition-colors group">
                            <x-heroicon-o-phone class="w-4 h-4" />
                            <span class="font-bold text-gray-800 group-hover:text-green-700">Tel:</span> {{ $agent->phone }}
                        </a>

                        <div class="flex items-center gap-2 text-gray-600 md:col-span-4">
                            <x-heroicon-o-building-office-2 class="w-4 h-4 text-gray-400" />
                            <span class="font-bold text-gray-800">Servicio Base:</span> {{ $agent->service->name ?? 'Sin asignar' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                <a href="#" @click.prevent="activeTab = 'personal'"
                    :class="activeTab === 'personal' ? 'border-brand-cyan text-brand-cyan-dark font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors font-secondary flex items-center gap-2">
                    <x-heroicon-o-user class="w-4 h-4" /> Datos Personales
                </a>
                <a href="#" @click.prevent="activeTab = 'profesional'"
                    :class="activeTab === 'profesional' ? 'border-brand-cyan text-brand-cyan-dark font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors font-secondary flex items-center gap-2">
                    <x-heroicon-o-briefcase class="w-4 h-4" /> Perfil Profesional
                </a>
                <a href="#" @click.prevent="activeTab = 'hsi'"
                    :class="activeTab === 'hsi' ? 'border-brand-cyan text-brand-cyan-dark font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors font-secondary flex items-center gap-2">
                    <x-heroicon-o-shield-check class="w-4 h-4" /> Accesos HSI
                </a>
                <a href="#" @click.prevent="activeTab = 'documentos'"
                    :class="activeTab === 'documentos' ? 'border-brand-cyan text-brand-cyan-dark font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors font-secondary flex items-center gap-2 relative">
                    <x-heroicon-o-folder-open class="w-4 h-4" /> Legajo Digital
                    @if($missingMandatoryTypes->count() > 0)
                        <span class="absolute top-3 -right-2 flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-pink opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-pink"></span>
                        </span>
                    @endif
                </a>
                <a href="#" @click.prevent="activeTab = 'notas'"
                    :class="activeTab === 'notas' ? 'border-brand-cyan text-brand-cyan-dark font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors font-secondary flex items-center gap-2 relative">
                    <x-fluentui-notepad-edit-20-o class="w-4 h-4" /> Notas del agente
                </a>
            </nav>
        </div>

        <!-- CONTENEDOR SLIDER CON ANIMACIÓN DINÁMICA DE ALTURA -->
        <div class="pb-10" x-data="{
            tabs: ['personal', 'profesional', 'hsi', 'documentos', 'notas'],
            get activeIndex() {
                let idx = this.tabs.indexOf(this.activeTab);
                return idx === -1 ? 0 : idx;
            },
            updateHeight() {
                this.$nextTick(() => {
                    let activeEl = this.$refs['tab_' + this.activeTab];
                    if (activeEl) {
                        this.$refs.tabContainer.style.height = activeEl.offsetHeight + 'px';
                    }
                });
            },
            initTabsSlider() {
                this.updateHeight();
                this.$watch('activeTab', () => { setTimeout(() => this.updateHeight(), 50); });
                const resizeObserver = new ResizeObserver(() => this.updateHeight());
                this.tabs.forEach(tab => {
                    let el = this.$refs['tab_' + tab];
                    if (el) resizeObserver.observe(el);
                });
            }
        }" x-init="initTabsSlider()">
        
            <div class="relative w-full overflow-hidden transition-[height] duration-500 ease-in-out" x-ref="tabContainer" x-cloak>
                <div class="flex transition-transform duration-500 ease-in-out items-start w-full" :style="`transform: translateX(-${activeIndex * 100}%)`">

                    <!-- TAB: PERSONAL -->
                    <div x-ref="tab_personal" class="w-full flex-shrink-0 px-1 pb-4" :inert="activeTab !== 'personal'">
                        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden max-w-4xl mx-auto md:ml-0">
                            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                                <h3 class="text-sm font-bold text-gray-900 uppercase font-secondary flex items-center gap-2">
                                    <x-heroicon-o-identification class="w-5 h-5 text-gray-400" /> Información Base
                                </h3>
                                @can('editar.informacion')
                                    <button wire:click="openEditModal"
                                        class="text-brand-cyan hover:text-brand-cyan-dark text-xs font-bold font-secondary transition-colors border border-brand-cyan px-3 py-1 rounded bg-white shadow-sm">
                                        Editar Datos
                                    </button>
                                @endcan
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div><span class="block text-xs font-secondary text-gray-500 uppercase tracking-wider mb-1">Nombres</span><span class="block text-sm font-bold text-gray-900 uppercase">{{ $agent->first_name }} {{ $agent->second_first_name }}</span></div>
                                <div><span class="block text-xs font-secondary text-gray-500 uppercase tracking-wider mb-1">Apellidos</span><span class="block text-sm font-bold text-gray-900 uppercase">{{ $agent->last_name }} {{ $agent->second_last_name }}</span></div>
                                <div><span class="block text-xs font-secondary text-gray-500 uppercase tracking-wider mb-1">DNI</span><span class="block text-sm font-bold text-gray-900">{{ number_format($agent->dni, 0, ',', '.') }}</span></div>
                                <div><span class="block text-xs font-secondary text-gray-500 uppercase tracking-wider mb-1">Género</span><span class="block text-sm font-bold text-gray-900 uppercase">{{ is_object($agent->gender) ? $agent->gender->value : $agent->gender }}</span></div>
                                <div><span class="block text-xs font-secondary text-gray-500 uppercase tracking-wider mb-1">Teléfono (Celular)</span><span class="block text-sm font-bold text-gray-900">{{ $agent->phone }}</span></div>
                                <div><span class="block text-xs font-secondary text-gray-500 uppercase tracking-wider mb-1">Correo Electrónico</span><span class="block text-sm font-bold text-gray-900">{{ $agent->email }}</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: PROFESIONAL -->
                    <div x-ref="tab_profesional" class="w-full flex-shrink-0 px-1 pb-4" :inert="activeTab !== 'profesional'">
                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                            <div class="space-y-6">
                                <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                                        <h3 class="text-sm font-bold text-gray-900 uppercase font-secondary flex items-center gap-2">
                                            <x-heroicon-o-academic-cap class="w-5 h-5 text-brand-cyan" /> Profesiones y Especialidades
                                        </h3>
                                        @can('editar.profesiones')
                                            <button wire:click="$set('showProfModal', true)" class="text-brand-cyan hover:text-brand-cyan-dark text-xs font-bold font-secondary transition-colors">
                                                + Asignar
                                            </button>
                                        @endcan
                                    </div>
                                    <div class="p-5 space-y-4">
                                        @forelse($agent->agentProfessions as $vinculacion)
                                            <div class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm">
                                                <div class="flex items-start justify-between mb-3">
                                                    <div>
                                                        <p class="text-sm font-bold text-gray-900 uppercase">
                                                            {{ $vinculacion->profession->name ?? 'Sin nombre' }}
                                                        </p>
                                                        @if($vinculacion->specialty)
                                                            <p class="mt-0.5 text-xs text-gray-500 font-secondary">
                                                                Especialidad: <span class="font-bold text-gray-700">{{ $vinculacion->specialty->name }}</span>
                                                            </p>
                                                        @endif
                                                    </div>
                                                    @can('editar.profesiones')
                                                        <button wire:click="confirmAction('profession', {{ $vinculacion->id }}, '¿Seguro que deseas eliminar esta profesión y todas sus matrículas?')"
                                                            class="text-gray-400 transition-colors hover:text-brand-pink">
                                                            <x-heroicon-o-trash class="w-4 h-4" />
                                                        </button>
                                                    @endcan
                                                </div>

                                                <div class="bg-gray-50 rounded-lg p-3 border border-gray-100 mt-3">
                                                    <div class="flex justify-between items-center mb-2">
                                                        <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Matrículas Vinculadas</h4>
                                                        @can('editar.profesiones')
                                                            <button wire:click="openRegistrationModal({{ $vinculacion->id }})"
                                                                class="text-brand-cyan hover:text-brand-cyan-dark text-[10px] font-bold font-secondary transition-colors">
                                                                + Cargar Matrícula
                                                            </button>
                                                        @endcan
                                                    </div>

                                                    @if($vinculacion->registrations->isNotEmpty())
                                                        <div class="space-y-2">
                                                            @foreach($vinculacion->registrations as $reg)
                                                                <div class="flex items-center justify-between p-2.5 text-sm bg-white border border-gray-200 rounded shadow-sm">
                                                                    <div>
                                                                        <div class="font-bold text-brand-blue">{{ $reg->number }}</div>
                                                                        <div class="text-[10px] text-gray-500 uppercase font-secondary">
                                                                            {{ is_object($reg->scope) ? $reg->scope->value : $reg->scope }} |
                                                                            {{ is_object($reg->type) ? $reg->type->value : $reg->type }}
                                                                        </div>
                                                                    </div>
                                                                    @can('editar.profesiones')
                                                                        <button wire:click="confirmAction('registration', {{ $reg->id }}, '¿Seguro que deseas borrar esta matrícula?')"
                                                                            class="text-gray-400 transition-colors hover:text-brand-pink">
                                                                            <x-heroicon-o-trash class="w-4 h-4" />
                                                                        </button>
                                                                    @endcan
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <p class="text-[11px] text-gray-400 font-secondary italic mt-1">No hay matrículas registradas.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-xs text-gray-500 font-secondary text-center py-4">No hay profesiones cargadas.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-6">
                                <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden {{ $agent->residencies->isEmpty() ? 'border-dashed' : '' }}">
                                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                                        <h3 class="text-sm font-bold text-gray-900 uppercase font-secondary flex items-center gap-2">
                                            <x-heroicon-o-clock class="w-5 h-5 text-gray-400" /> Formación / Residencias
                                        </h3>
                                        @can('editar.profesiones')
                                            <button wire:click="$set('showResModal', true)" class="text-brand-cyan hover:text-brand-cyan-dark text-xs font-bold font-secondary transition-colors">
                                                + Cargar
                                            </button>
                                        @endcan
                                    </div>
                                    <div class="p-5">
                                        @if($agent->residencies->isNotEmpty())
                                            <div class="space-y-3">
                                                @foreach($agent->residencies as $residency)
                                                    <div class="flex items-start justify-between p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                                                        <div>
                                                            <p class="text-sm font-bold text-gray-900 uppercase">{{ $residency->program_name }}</p>
                                                            <div class="flex items-center gap-2 mt-1.5">
                                                                <span class="inline-flex items-center rounded-md bg-brand-cyan/10 px-2 py-0.5 text-[10px] font-bold text-brand-cyan-dark ring-1 ring-inset ring-brand-cyan/20 uppercase">
                                                                    {{ $residency->current_year }}
                                                                </span>
                                                                <span class="text-[11px] text-gray-500 font-secondary">Unidad HSI: <span class="font-bold">{{ $residency->currentUnit->alias ?? $residency->currentUnit->name ?? 'N/A' }}</span></span>
                                                            </div>
                                                        </div>
                                                        @can('editar.profesiones')
                                                            <button wire:click="confirmAction('residency', {{ $residency->id }}, '¿Seguro que deseas borrar esta residencia?')" class="text-gray-400 transition-colors hover:text-brand-pink">
                                                                <x-heroicon-o-trash class="w-4 h-4" />
                                                            </button>
                                                        @endcan
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <x-heroicon-o-user-minus class="w-10 h-10 mx-auto text-gray-300 mb-2" />
                                                <p class="text-sm font-secondary text-gray-500">No se encuentra en ningún programa de residencia activo.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                                        <h3 class="text-sm font-bold text-gray-900 uppercase font-secondary flex items-center gap-2">
                                            <x-heroicon-o-star class="w-5 h-5 text-amber-500" /> Jefaturas de Servicio
                                        </h3>
                                        @can('editar.profesiones')
                                            <button wire:click="$set('showBossModal', true)" class="text-brand-cyan hover:text-brand-cyan-dark text-xs font-bold font-secondary transition-colors">
                                                + Asignar Jefatura
                                            </button>
                                        @endcan
                                    </div>
                                    <div class="p-5">
                                        @if($agent->serviceBosses->isNotEmpty())
                                            <div class="space-y-3">
                                                @foreach($agent->serviceBosses as $boss)
                                                    <div class="flex items-center justify-between p-3 border rounded-lg bg-amber-50 border-amber-100">
                                                        <div class="flex items-center gap-3">
                                                            <div class="p-2 rounded-md bg-amber-100"><x-heroicon-s-star class="w-5 h-5 text-amber-600" /></div>
                                                            <div>
                                                                <p class="text-sm font-bold text-gray-900 uppercase">{{ $boss->service->name ?? 'Servicio' }}</p>
                                                                <p class="mt-0.5 text-[11px] text-amber-700 font-secondary">Jefe de Servicio Designado</p>
                                                            </div>
                                                        </div>
                                                        @can('editar.profesiones')
                                                            <button wire:click="confirmAction('boss', {{ $boss->id }}, '¿Seguro que deseas revocar esta jefatura?')" class="transition-colors text-amber-600 hover:text-brand-pink">
                                                                <x-heroicon-o-trash class="w-4 h-4" />
                                                            </button>
                                                        @endcan
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-xs text-gray-500 font-secondary text-center py-4">No registra jefaturas.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: HSI -->
                    <div x-ref="tab_hsi" class="w-full flex-shrink-0 px-1 pb-4" :inert="activeTab !== 'hsi'">
                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                            <div class="bg-white shadow-sm rounded-xl border border-brand-cyan/40 overflow-hidden flex flex-col">
                                <div class="px-5 py-4 border-b border-gray-100 bg-brand-cyan/5 flex justify-between items-center">
                                    <h3 class="text-sm font-bold text-gray-900 uppercase font-secondary flex items-center gap-2">
                                        <x-heroicon-o-shield-check class="w-5 h-5 text-brand-cyan" /> Roles Asignados (HSI)
                                    </h3>
                                    @can('editar.accesos')
                                        <button wire:click="$set('showRoleModal', true)" class="text-brand-cyan hover:text-brand-cyan-dark text-xs font-bold font-secondary transition-colors">
                                            + Asignar Rol
                                        </button>
                                    @endcan
                                </div>
                                <div class="flex-1 flex flex-col">
                                    @if($agent->hsiRoles->isNotEmpty())
                                        <table class="min-w-full divide-y divide-gray-100">
                                            <tbody class="bg-white divide-y divide-gray-50 font-secondary text-sm">
                                                @foreach($agent->hsiRoles as $role)
                                                    <tr class="hover:bg-gray-50 transition-colors">
                                                        <td class="px-5 py-3.5 font-bold text-gray-900">{{ $role->name }}</td>
                                                        <td class="px-5 py-3.5 text-right">
                                                            @can('editar.accesos')
                                                                <button wire:click="confirmAction('role', {{ $role->id }}, '¿Seguro que deseas quitar este rol del sistema HSI?')" class="text-gray-400 hover:text-brand-pink transition-colors">
                                                                    <x-heroicon-o-trash class="w-4 h-4" />
                                                                </button>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-xs text-gray-500 font-secondary text-center py-8">No posee roles HSI vinculados.</p>
                                    @endif

                                    <div class="mt-auto p-4 border-t flex justify-between items-center {{ str_contains($agent->hsi_access_status['label'] ?? '', 'SIN') ? 'bg-amber-50 border-amber-100' : 'bg-gray-50 border-gray-100' }}">
                                        <div class="flex gap-3 items-center">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold ring-1 ring-inset font-secondary {{ $agent->hsi_access_status['color'] ?? 'bg-gray-50 text-gray-500' }}">
                                                ESTADO: {{ $agent->hsi_access_status['label'] ?? 'DESCONOCIDO' }}
                                            </span>
                                            @if(empty($agent->user_id) && empty($agent->user))
                                                <p class="text-[11px] text-gray-600 font-secondary">Falta vincular usuario de login.</p>
                                            @else
                                                <p class="text-[11px] text-gray-600 font-secondary">User: <strong class="text-brand-cyan-dark">{{ $agent->user ?? 'N/A' }}</strong> (ID: {{ $agent->user_id ?? 'N/A' }})</p>
                                            @endif
                                        </div>
                                        @can('editar.accesos')
                                            <button wire:click="openHsiModal" class="text-brand-cyan hover:text-brand-cyan-dark text-xs font-bold font-secondary transition-colors border border-brand-cyan/30 px-3 py-1.5 rounded-md bg-white shadow-sm">
                                                Vincular Credenciales
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden flex flex-col">
                                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                                    <h3 class="text-sm font-bold text-gray-900 uppercase font-secondary flex items-center gap-2">
                                        <x-heroicon-o-building-office-2 class="w-5 h-5 text-gray-500" /> Unidades Jerárquicas
                                    </h3>
                                    @can('editar.accesos')
                                        <button wire:click="$set('showUnitModal', true)" class="text-brand-cyan hover:text-brand-cyan-dark text-xs font-bold font-secondary transition-colors">
                                            + Vincular Unidad
                                        </button>
                                    @endcan
                                </div>
                                <div class="p-5 flex-1">
                                    @if($agent->hierarchicalUnits->isNotEmpty())
                                        @foreach($agent->hierarchicalUnits as $unit)
                                            <div class="relative pl-4 mb-4 border-l-2 border-brand-cyan">
                                                <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-brand-cyan"></div>
                                                <div class="flex items-start justify-between">
                                                    <div>
                                                        <p class="text-sm font-bold text-gray-900 uppercase">
                                                            {{ $unit->alias ?? $unit->name ?? 'Unidad' }}
                                                        </p>
                                                        <div class="flex items-center gap-2 mt-1.5">
                                                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold text-gray-600 bg-gray-100 rounded-md font-secondary">
                                                                ID HSI: {{ $unit->id }}
                                                            </span>
                                                            @if($unit->pivot->responsible ?? false)
                                                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold text-amber-700 uppercase rounded-md bg-amber-50 ring-1 ring-inset ring-amber-600/20 font-secondary">
                                                                    Responsable
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @can('editar.accesos')
                                                        <button wire:click="confirmAction('unit', {{ $unit->id }}, '¿Seguro que deseas desvincular esta unidad jerárquica?')" class="transition-colors text-gray-400 hover:text-brand-pink">
                                                            <x-heroicon-o-trash class="w-4 h-4" />
                                                        </button>
                                                    @endcan
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-xs text-gray-500 font-secondary text-center py-4">Sin unidades jerárquicas asignadas.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: DOCUMENTOS -->
                    <div x-ref="tab_documentos" class="w-full flex-shrink-0 px-1 pb-4" :inert="activeTab !== 'documentos'">
                        <div class="max-w-4xl mx-auto">
                            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden mb-6">
                                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                                    <div>
                                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                            <x-heroicon-o-folder-open class="w-6 h-6 text-brand-blue" /> Matriz Requerida para HSI
                                        </h3>
                                        <p class="text-xs font-secondary text-gray-500 mt-1">Exigidos obligatoriamente según los Roles que tiene asignados.</p>
                                    </div>
                                    @can('editar.documentos')
                                        <button wire:click="$set('showDocModal', true)" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm font-secondary flex items-center gap-1">
                                            <x-heroicon-o-cloud-arrow-up class="w-4 h-4" /> Subir PDF
                                        </button>
                                    @endcan
                                </div>

                                <div class="divide-y divide-gray-100">
                                    @foreach($missingMandatoryTypes as $type)
                                        <div class="p-5 flex items-center justify-between bg-red-50/30 border-l-4 border-brand-pink">
                                            <div class="flex items-start gap-4">
                                                <div class="bg-red-100 p-2 rounded-lg shrink-0"><x-heroicon-o-exclamation-triangle class="w-6 h-6 text-brand-pink" /></div>
                                                <div>
                                                    <p class="text-sm font-bold text-brand-pink uppercase">{{ $type->name }}</p>
                                                    <p class="text-[11px] text-gray-500 font-secondary mt-1">Falta digitalizar archivo.</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    @foreach($uploadedMandatoryDocs as $doc)
                                        <div class="p-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                            <div class="flex items-start gap-4">
                                                <div class="bg-green-100 p-2 rounded-lg shrink-0"><x-heroicon-s-check-circle class="w-6 h-6 text-green-600" /></div>
                                                <div>
                                                    <p class="text-sm font-bold text-gray-900 uppercase">
                                                        {{ $doc->type->name ?? 'Documento' }}
                                                    </p> 
                                                    @if($doc->other_type)
                                                        <p class="text-[11px] text-gray-500 font-secondary mt-0.5">Tipo declarado: <span class="font-bold text-gray-700">{{ $doc->other_type }}</span></p>
                                                    @endif
                                                    <p class="text-[11px] text-gray-500 font-secondary mt-1">Verificado. Subido el {{ $doc->created_at->format('d/m/Y') }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <a href="{{ Storage::url($doc->path) }}" target="_blank"
                                                    class="text-brand-cyan hover:text-brand-cyan-dark text-xs font-bold font-secondary flex items-center gap-1">
                                                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" /> Bajar
                                                </a>
                                                @can('editar.documentos')
                                                    <button wire:click="confirmAction('document', {{ $doc->id }}, '¿Seguro que deseas eliminar este documento comprobante?')" class="text-gray-400 hover:text-brand-pink">
                                                        <x-heroicon-o-trash class="w-4 h-4" />
                                                    </button>
                                                @endcan
                                            </div>
                                        </div>
                                    @endforeach

                                    @if($missingMandatoryTypes->isEmpty() && $uploadedMandatoryDocs->isEmpty())
                                        <div class="p-8 text-center text-gray-500 font-secondary text-sm">No posee roles que exijan documentos.</div>
                                    @endif
                                </div>
                            </div>

                            @if($historicalDocs->isNotEmpty())
                                <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                                        <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2"><x-heroicon-o-archive-box class="w-5 h-5 text-gray-400" /> Histórico / Otros Archivos</h3>
                                        <p class="text-[11px] text-gray-500 font-secondary mt-1">Archivos del legajo que no interfieren con la validación de interoperabilidad de HSI actual.</p>
                                    </div>
                                    <div class="divide-y divide-gray-100">
                                        @foreach($historicalDocs as $doc)
                                            <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                                                <div class="flex items-start gap-3">
                                                    <div class="bg-gray-100 p-1.5 rounded-md text-gray-500 shrink-0"><x-heroicon-o-document class="w-5 h-5" /></div>
                                                    <div>
                                                        <p class="text-sm font-bold text-gray-700 uppercase">
                                                            {{ $doc->type->name ?? $doc->other_type ?? 'Documento Extra' }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <a href="{{ Storage::url($doc->path) }}" target="_blank"
                                                        class="text-gray-500 hover:text-brand-cyan text-xs font-bold font-secondary flex items-center gap-1">
                                                        <x-heroicon-o-eye class="w-4 h-4" /> Ver
                                                    </a>
                                                    <button wire:click="confirmAction('document', {{ $doc->id }}, '¿Seguro que deseas eliminar este archivo de su legajo histórico?')" class="text-gray-400 hover:text-brand-pink">
                                                        <x-heroicon-o-trash class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- TAB: NOTAS -->
                    <div x-ref="tab_notas" class="w-full flex-shrink-0 px-1 pb-4" :inert="activeTab !== 'notas'">
                        <div class="max-w-6xl mx-auto">
                            @if($notes->isNotEmpty())
                                <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                                        <div>
                                            <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                                                <x-heroicon-o-document-text class="w-5 h-5 text-gray-400" />
                                                Notas del Agente
                                            </h3>
                                            <p class="text-[11px] text-gray-500 font-secondary mt-1">
                                                Registro de observaciones e información adicional.
                                            </p>
                                        </div>

                                        <button wire:click="createNote" class="text-xs font-bold bg-brand-cyan text-white px-3 py-1.5 rounded-md hover:bg-cyan-600 transition-colors flex items-center gap-1 shadow-sm">
                                            <x-heroicon-o-plus class="w-4 h-4" /> Nueva Nota
                                        </button>
                                    </div>

                                    <div class="divide-y divide-gray-100">
                                        @foreach($notes as $note)
                                            <div class="p-4 flex items-start justify-between hover:bg-gray-50 transition-colors">
                                                <div class="flex items-start gap-3 w-full pr-4">
                                                    <div class="bg-amber-50 p-1.5 rounded-md text-amber-500 shrink-0">
                                                        <x-heroicon-o-chat-bubble-bottom-center-text class="w-5 h-5" />
                                                    </div>
                                                    <div class="flex-1">
                                                        <h4 class="text-sm font-bold text-gray-700 uppercase">{{ $note->title }}</h4>
                                                        <p class="text-xs text-gray-600 mt-1 line-clamp-2 leading-relaxed">{{ $note->content }}</p>
                                                        <span class="text-[10px] text-gray-400 font-secondary mt-2 block">
                                                            Registrada el: {{ $note->created_at ? $note->created_at->format('d/m/Y H:i') : 'Sin fecha' }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-3 shrink-0 mt-1">
                                                    <button wire:click="editNote({{ $note->id }})" class="text-gray-500 hover:text-brand-cyan text-xs font-bold font-secondary flex items-center gap-1">
                                                        <x-heroicon-o-pencil class="w-4 h-4" /> Editar
                                                    </button>
                                                    <button wire:click="confirmAction('note', {{ $note->id }}, '¿Estás seguro de que querés borrar permanentemente esta nota?')" class="text-gray-400 hover:text-brand-pink">
                                                        <x-heroicon-o-trash class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="text-center p-8 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                                    <x-heroicon-o-document-text class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                                    <p class="text-sm text-gray-500">No hay notas registradas para este agente.</p>
                                    <button wire:click="createNote" class="mt-3 text-xs font-bold text-brand-cyan hover:underline">
                                        + Agregar la primera nota
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- ZONA DE MODALES FUERA DEL FLUJO DE LAS PESTAÑAS -->
    
    <!-- MODAL DE CONFIRMACIÓN (REEMPLAZA AL DE HTML NATIVO) -->
    <div x-data="{ show: @entangle('showConfirmModal') }" x-show="show" class="fixed inset-0 z-[200] overflow-y-auto"
        x-cloak aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-50 backdrop-blur-sm" aria-hidden="true"
                @click="show = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-md sm:w-full sm:p-6 border-t-4 border-brand-pink">
                
                <div class="sm:flex sm:items-start">
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-brand-pink" />
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg font-bold leading-6 text-gray-900 font-secondary uppercase tracking-wide" id="modal-title">
                            Confirmar Acción
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 font-secondary">{{ $confirmingMessage }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="executeAction"
                        class="inline-flex justify-center w-full px-4 py-2 text-sm font-bold text-white transition-colors bg-brand-pink border border-transparent rounded-md shadow-sm hover:bg-red-600 focus:outline-none sm:ml-3 sm:w-auto font-secondary uppercase">
                        Sí, Confirmar
                    </button>
                    <button type="button" wire:click="$set('showConfirmModal', false)"
                        class="inline-flex justify-center w-full px-4 py-2 mt-3 text-sm font-bold text-gray-700 transition-colors bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto font-secondary uppercase">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>


    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="$set('showEditModal', false)"></div><span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border-t-4 border-brand-cyan">
                    <form wire:submit="updateAgent">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><x-heroicon-o-pencil-square
                                    class="w-6 h-6 text-brand-cyan" /> Editar Datos del Agente</h3>
                        </div>

                        <div class="bg-gray-50 px-6 py-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">

                                <div
                                    class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 pb-4 border-b border-gray-200">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Estado del usuario *</label>
                                        <select wire:model="edit_status"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary uppercase" required>
                                            @foreach($statuses as $status)
                                                <option value="{{ $status->value }}">{{ $status->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-searchable-select wire:model="edit_service_id" label="Servicio Base (Planta)"
                                            placeholder="Escriba para buscar..." :options="$services->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()->toArray()" required />
                                    </div>
                                </div>

                                <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 pb-4 border-b border-gray-200">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">DNI *</label>
                                        <input type="number" wire:model="edit_dni"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary" required>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Género Registrado *</label>
                                        <select wire:model="edit_gender"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary uppercase" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($genders as $gender)
                                                <option value="{{ $gender->value }}">{{ $gender->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Primer Nombre *</label>
                                    <input type="text" wire:model="edit_first_name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary uppercase" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Segundo Nombre</label>
                                    <input type="text" wire:model="edit_second_first_name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary uppercase">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Primer Apellido *</label>
                                    <input type="text" wire:model="edit_last_name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary uppercase" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Segundo Apellido</label>
                                    <input type="text" wire:model="edit_second_last_name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary uppercase">
                                </div>

                                <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Teléfono de Contacto *</label>
                                        <input type="text" wire:model="edit_phone"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary" required>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Correo Electrónico *</label>
                                        <input type="email" wire:model="edit_email"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary lowercase" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-white flex justify-end gap-3 border-t">
                            <button type="button" wire:click="$set('showEditModal', false)"
                                class="px-4 py-2 text-sm font-bold bg-gray-100 rounded-md">Cancelar</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-bold text-white bg-brand-cyan rounded-md">Actualizar Datos</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showHsiModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="$set('showHsiModal', false)"></div><span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-t-4 border-brand-cyan">
                    <form wire:submit="saveHsiData">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><x-heroicon-o-link class="w-5 h-5 text-brand-cyan" /> Vincular Credenciales HSI</h3>
                            <p class="text-xs text-gray-500 font-secondary mt-1">Atención: Estos datos son identificadores técnicos de interoperabilidad con el sistema HSI.</p>
                        </div>
                        <div class="p-6 space-y-4 bg-gray-50">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 font-secondary uppercase">Person ID (ID de Persona HSI)</label>
                                <input type="number" wire:model="hsi_person_id" placeholder="Ej: 12345"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 font-secondary uppercase">User ID HSI</label>
                                    <input type="number" wire:model="hsi_user_id" placeholder="Ej: 123"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 font-secondary uppercase">Username HSI</label>
                                    <input type="text" wire:model="hsi_user" placeholder="Ej: san.martin"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary">
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-white flex justify-end gap-3 border-t">
                            <button type="button" wire:click="$set('showHsiModal', false)"
                                class="px-4 py-2 text-sm font-bold bg-gray-100 rounded-md">Cancelar</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-bold text-white bg-brand-cyan rounded-md">Guardar Credenciales</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showProfModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="$set('showProfModal', false)"></div><span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-t-4 border-brand-cyan">
                    <form wire:submit="saveProfession">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">Asignar Profesión</h3>
                        </div>
                        <div class="p-6 space-y-4 bg-gray-50">
                            <x-searchable-select wire:model="prof_profession_id" label="Ocupación/Profesión *"
                                placeholder="Buscar ocupación..." defaultText="Seleccione..."
                                :options="$occupations->map(fn($occ) => ['id' => $occ->id, 'name' => $occ->name])->values()->toArray()" />

                            <x-searchable-select wire:model="prof_specialty_id" label="Especialidad (Opcional)"
                                placeholder="Buscar especialidad..." defaultText="Ninguna..."
                                :options="$specialities->map(fn($spec) => ['id' => $spec->id, 'name' => $spec->name])->values()->toArray()" />
                        </div>
                        <div class="px-6 py-4 bg-white flex justify-end gap-3 border-t">
                            <button type="button" wire:click="$set('showProfModal', false)"
                                class="px-4 py-2 text-sm font-bold bg-gray-100 rounded-md">Cancelar</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-bold text-white bg-brand-cyan rounded-md">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showRegModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="$set('showRegModal', false)"></div><span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-t-4 border-brand-cyan">
                    <form wire:submit="saveRegistration">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">Cargar Matrícula</h3>
                        </div>
                        <div class="p-6 space-y-4 bg-gray-50">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 font-secondary uppercase">Número de Matrícula *</label>
                                <input type="text" wire:model="reg_number"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 font-secondary uppercase">Ámbito (Scope) *</label>
                                <select wire:model="reg_scope"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($registrationScopes as $scope)
                                        <option value="{{ $scope->value }}">{{ strtoupper($scope->value) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 font-secondary uppercase">Tipo *</label>
                                <select wire:model="reg_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($registrationTypes as $type)
                                        <option value="{{ $type->value }}">{{ strtoupper($type->value) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-white flex justify-end gap-3 border-t">
                            <button type="button" wire:click="$set('showRegModal', false)"
                                class="px-4 py-2 text-sm font-bold bg-gray-100 rounded-md">Cancelar</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-bold text-white bg-brand-cyan rounded-md">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showResModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="$set('showResModal', false)"></div><span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-t-4 border-brand-cyan">
                    <form wire:submit="saveResidency">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">Cargar Residencia</h3>
                        </div>
                        <div class="p-6 space-y-4 bg-gray-50">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 font-secondary uppercase">Programa Académico *</label>
                                <input type="text" wire:model="res_program_name" placeholder="Ej: Tocoginecología"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary" required>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 font-secondary uppercase">Año Actual *</label>
                                    <input type="text" wire:model="res_current_year" placeholder="Ej: R1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 font-secondary uppercase">Fecha de Egreso</label>
                                    <input type="date" wire:model="res_end_date"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary">
                                </div>
                            </div>
                            <div>
                                <x-searchable-select wire:model="res_current_unit_id" label="Unidad HSI Rotación Actual"
                                    placeholder="Buscar unidad..." defaultText="Ninguna..."
                                    :options="$hierarchicalUnits->map(fn($unit) => ['id' => $unit->id, 'name' => ($unit->alias ?? $unit->name) . ' (ID: ' . $unit->id . ')'])->values()->toArray()" />
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-white flex justify-end gap-3 border-t">
                            <button type="button" wire:click="$set('showResModal', false)"
                                class="px-4 py-2 text-sm font-bold bg-gray-100 rounded-md">Cancelar</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-bold text-white bg-brand-cyan rounded-md">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showBossModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="$set('showBossModal', false)"></div><span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full border-t-4 border-amber-500">
                    <form wire:submit="saveServiceBoss">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">Asignar Jefatura</h3>
                        </div>
                        <div class="p-6 space-y-4 bg-gray-50">
                            <div>
                                <x-searchable-select wire:model="boss_service_id" label="Servicio a Cargo *"
                                    placeholder="Buscar servicio..." defaultText="Seleccione..."
                                    :options="$services->map(fn($serv) => ['id' => $serv->id, 'name' => $serv->name])->values()->toArray()" />
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-white flex justify-end gap-3 border-t">
                            <button type="button" wire:click="$set('showBossModal', false)"
                                class="px-4 py-2 text-sm font-bold bg-gray-100 rounded-md">Cancelar</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-bold text-white bg-amber-500 rounded-md">Asignar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showRoleModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="$set('showRoleModal', false)"></div><span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full border-t-4 border-brand-cyan">
                    <form wire:submit="saveRole">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">Asignar Rol HSI</h3>
                        </div>
                        <div class="p-6 bg-gray-50">
                            <x-searchable-select wire:model="role_id" label="Rol del Sistema *" placeholder="Buscar rol..."
                                defaultText="Seleccione..." :options="$hsiRoles->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->values()->toArray()" />
                        </div>
                        <div class="px-6 py-4 bg-white flex justify-end gap-3 border-t">
                            <button type="button" wire:click="$set('showRoleModal', false)"
                                class="px-4 py-2 text-sm font-bold bg-gray-100 rounded-md">Cancelar</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-bold text-white bg-brand-cyan rounded-md">Asignar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showUnitModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="$set('showUnitModal', false)"></div><span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-t-4 border-brand-cyan">
                    <form wire:submit="saveUnit">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">Vincular a Unidad Jerárquica</h3>
                        </div>
                        <div class="p-6 space-y-4 bg-gray-50">
                            <div>
                                <x-searchable-select wire:model="unit_id" label="Unidad HSI *"
                                    placeholder="Buscar unidad..." defaultText="Seleccione..."
                                    :options="$hierarchicalUnits->map(fn($unit) => ['id' => $unit->id, 'name' => ($unit->alias ?? $unit->name) . ' (ID: ' . $unit->id . ')'])->values()->toArray()" />
                            </div>
                            <div class="flex items-center mt-4">
                                <input type="checkbox" wire:model="unit_responsible" id="unit_resp"
                                    class="h-4 w-4 text-brand-cyan focus:ring-brand-cyan border-gray-300 rounded">
                                <label for="unit_resp" class="ml-2 block text-sm text-gray-900 font-secondary">Es Responsable de la Unidad</label>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-white flex justify-end gap-3 border-t">
                            <button type="button" wire:click="$set('showUnitModal', false)"
                                class="px-4 py-2 text-sm font-bold bg-gray-100 rounded-md">Cancelar</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-bold text-white bg-brand-cyan rounded-md">Vincular</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showDocModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="$set('showDocModal', false)"></div><span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-t-4 border-brand-cyan">
                    <form wire:submit="saveDocument">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><x-heroicon-o-cloud-arrow-up class="w-5 h-5 text-brand-cyan" /> Subir Documento</h3>
                        </div>
                        <div class="p-6 space-y-4 bg-gray-50">
                            <div>
                                <x-searchable-select wire:model="doc_type_id" label="Tipo de Documento *"
                                    placeholder="Buscar documento..." defaultText="Seleccione..."
                                    :options="$documentTypes->map(fn($type) => ['id' => $type->id, 'name' => $type->name])->values()->toArray()" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 font-secondary uppercase">Descripción Extra (Opcional)</label>
                                <input type="text" wire:model="doc_other_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan sm:text-sm font-secondary">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 font-secondary uppercase">Archivo (PDF/IMG, Max 5MB) *</label>
                                <input type="file" wire:model="doc_file"
                                    class="mt-1 block w-full text-sm text-gray-500 font-secondary file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-brand-cyan/10 file:text-brand-cyan-dark hover:file:bg-brand-cyan/20"
                                    required>
                                <div wire:loading wire:target="doc_file" class="text-xs text-brand-cyan font-bold mt-2">Cargando archivo...</div>
                                @error('doc_file') <span class="text-xs text-brand-pink font-bold mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-white flex justify-end gap-3 border-t">
                            <button type="button" wire:click="$set('showDocModal', false)"
                                class="px-4 py-2 text-sm font-bold bg-gray-100 rounded-md">Cancelar</button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-bold text-white bg-brand-cyan rounded-md disabled:opacity-50">Subir y Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL DE NOTAS -->
    <div x-data="{ show: @entangle('showNoteModal') }" x-show="show" class="fixed inset-0 z-[100] overflow-y-auto"
        x-cloak aria-labelledby="modal-title" role="dialog" aria-modal="true">

        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-50 backdrop-blur-sm" aria-hidden="true"
                @click="show = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-gray-200">

                <form wire:submit="saveNote">
                    <div>
                        <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                            <h3 class="text-lg font-bold leading-6 text-gray-800 font-secondary uppercase tracking-wide flex items-center gap-2" id="modal-title">
                                <x-heroicon-o-document-text class="w-5 h-5 text-brand-cyan" />
                                {{ $note_id ? 'Editar Nota' : 'Nueva Nota' }}
                            </h3>
                            <button type="button" @click="show = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="mt-2 space-y-5">
                            <div>
                                <label for="note_title" class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Título / Asunto</label>
                                <input type="text" wire:model="note_title" id="note_title"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary transition-colors"
                                    placeholder="Ej: Observación sobre legajo">
                                @error('note_title') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="note_content" class="block text-[11px] font-bold text-gray-700 font-secondary uppercase tracking-wider mb-1">Contenido de la nota</label>
                                <textarea wire:model="note_content" id="note_content" rows="5"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm font-secondary transition-colors"
                                    placeholder="Escribe el detalle completo aquí..."></textarea>
                                @error('note_content') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 sm:mt-8 sm:flex sm:flex-row-reverse gap-3 bg-gray-50 -mx-6 -mb-6 px-6 py-4 border-t border-gray-100">
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-bold text-white transition-colors border border-transparent rounded-md shadow-sm bg-brand-cyan hover:bg-cyan-600 focus:outline-none sm:w-auto uppercase tracking-wide font-secondary">
                            Guardar Nota
                        </button>
                        <button type="button" @click="show = false" class="mt-3 w-full inline-flex justify-center items-center px-4 py-2 text-sm font-bold text-gray-700 transition-colors bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto uppercase tracking-wide font-secondary">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>