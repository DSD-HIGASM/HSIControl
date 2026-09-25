<x-app-layout>

    <x-banner text="Visión General"></x-banner>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-10">
                <h3 class="text-3xl font-extrabold text-gray-900 mb-2">
                    Hola {{ auth()->user()->agent->first_name ?? 'Usuario' }}
                </h3>
                <p class="font-secondary text-lg text-gray-500 mb-4">¿Qué área vamos a gestionar hoy?</p>

                <div class="flex justify-start">
                    <span class="block w-40 h-1.5 bg-gradient-to-r from-brand-cyan via-brand-blue to-brand-pink rounded-full shadow-sm"></span>
                </div>
            </div>

            @php
                // 1. Mapeo de paleta de colores por columna (Col 1: Celeste | Col 2: Azul | Col 3: Rosa)
                $columnThemes = [
                    0 => [
                        'border'     => 'border-brand-cyan',
                        'icon_bg'    => 'bg-brand-cyan/10 group-hover:bg-brand-cyan',
                        'icon_text'  => 'text-brand-cyan group-hover:text-white',
                        'arrow_text' => 'group-hover:text-brand-cyan',
                    ],
                    1 => [
                        'border'     => 'border-brand-blue',
                        'icon_bg'    => 'bg-brand-blue/10 group-hover:bg-brand-blue',
                        'icon_text'  => 'text-brand-blue group-hover:text-white',
                        'arrow_text' => 'group-hover:text-brand-blue',
                    ],
                    2 => [
                        'border'     => 'border-brand-pink',
                        'icon_bg'    => 'bg-brand-pink/10 group-hover:bg-brand-pink',
                        'icon_text'  => 'text-brand-pink group-hover:text-white',
                        'arrow_text' => 'group-hover:text-brand-pink',
                    ],
                ];

                // 2. Registro dinámico de módulos con sus permisos y rutas
                $modules = collect([
                    [
                        'title'       => 'Padrón de Personal',
                        'description' => 'Gestión de agentes y control de credenciales para el sistema principal.',
                        'route'       => route('agents.index'),
                        'icon'        => 'heroicon-o-users',
                        'allowed'     => true,
                    ],
                    [
                        'title'       => 'Estructura Profesional',
                        'description' => 'Administración de profesiones, especialidades y vinculación de roles.',
                        'route'       => route('system.config'),
                        'icon'        => 'heroicon-o-academic-cap',
                        'allowed'     => auth()->user()->canany([
                            'configurar.documentos', 'configurar.roles', 'configurar.profesiones', 
                            'configurar.especialidades', 'configurar.usuarios', 'configurar.servicios'
                        ]),
                    ],
                    [
                        'title'       => 'Unidades Jerárquicas',
                        'description' => 'Gestión del tablero funcional, dependencias y mapa estructural del hospital.',
                        'route'       => route('hierarchical-units.manager'),
                        'icon'        => 'heroicon-o-rectangle-group',
                        'allowed'     => auth()->user()->canany(['ver.unidades_jerarquicas', 'gestionar.unidades_jerarquicas']),
                    ],
                    [
                        'title'       => 'Pantallas de Guardia (Miky7)',
                        'description' => 'Centro de comando, monitoreo y control remoto de televisores y llamadores.',
                        'route'       => route('miky.index'),
                        'icon'        => 'heroicon-o-tv',
                        'allowed'     => auth()->user()->can('gestionar.miky'),
                    ],
                    [
                        'title'       => 'Auditoría del Sistema',
                        'description' => 'Visualización de historial de actividad, cambios en modelos y registros.',
                        'route'       => route('system.activity-logs'),
                        'icon'        => 'heroicon-o-clipboard-document-list',
                        'allowed'     => auth()->user()->can('ver.logs'),
                    ],
                ])->filter(fn ($item) => $item['allowed']);
            @endphp

            <!-- Grilla dinámica con distribución de 3 columnas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($modules as $module)
                    @php
                        // Asignación de color según la columna (0 = Col 1 | 1 = Col 2 | 2 = Col 3)
                        $theme = $columnThemes[$loop->index % 3];
                    @endphp

                    <a href="{{ $module['route'] }}" wire:navigate
                        class="block bg-white overflow-hidden shadow-sm sm:rounded-xl border-t-4 {{ $theme['border'] }} hover:shadow-md hover:-translate-y-1 transition-all duration-200 group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="{{ $theme['icon_bg'] }} p-3 rounded-lg transition-colors duration-200">
                                    <x-dynamic-component :component="$module['icon']" class="w-8 h-8 {{ $theme['icon_text'] }} transition-colors" />
                                </div>
                                <x-heroicon-m-arrow-up-right class="w-5 h-5 text-gray-300 {{ $theme['arrow_text'] }} transition-colors" />
                            </div>

                            <h4 class="text-xl font-bold text-gray-900 mb-2">{{ $module['title'] }}</h4>
                            <p class="font-secondary text-sm text-gray-500 leading-relaxed">{{ $module['description'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>