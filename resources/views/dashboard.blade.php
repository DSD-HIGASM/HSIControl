<x-app-layout>

    <!-- Cabecera de la página -->
    <x-banner text="Visión General"></x-banner>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Mensaje de bienvenida con la línea gradiente institucional -->
            <div class="mb-10">
                <h3 class="text-3xl font-extrabold text-gray-900 mb-2">Hola
                    {{ auth()->user()->agent->first_name ?? 'Usuario' }}</h3>
                <p class="font-secondary text-lg text-gray-500 mb-4">¿Qué área vamos a gestionar hoy?</p>

                <div class="flex justify-start">
                    <span
                        class="block w-40 h-1.5 bg-gradient-to-r from-brand-cyan via-brand-blue to-brand-pink rounded-full shadow-sm"></span>
                </div>
            </div>

            <!-- Grid de Módulos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Tarjeta 1: Padrón y Accesos -->
                <a href="{{ route('agents.index') }}"
                    class="block bg-white overflow-hidden shadow-sm sm:rounded-xl border-t-4 border-brand-cyan hover:shadow-md hover:-translate-y-1 transition-all duration-200 group">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="bg-brand-cyan/10 p-3 rounded-lg group-hover:bg-brand-cyan group-hover:text-white transition-colors duration-200">
                                <x-heroicon-o-users class="w-8 h-8 text-brand-cyan group-hover:text-white" />
                            </div>
                            <x-heroicon-m-arrow-up-right
                                class="w-5 h-5 text-gray-300 group-hover:text-brand-cyan transition-colors" />
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-2">Padrón de Personal</h4>
                        <p class="font-secondary text-sm text-gray-500">Gestión de agentes y control de credenciales
                            para el sistema principal.</p>
                    </div>
                </a>

                <!-- Tarjeta 2: Estructura y Especialidades -->
                @canany(['configurar.documentos', 'configurar.roles', 'configurar.profesiones', 'configurar.especialidades', 'configurar.usuarios', 'configurar.servicios'])
                    <a href="{{ route('system.config') }}"
                        class="block bg-white overflow-hidden shadow-sm sm:rounded-xl border-t-4 border-brand-blue hover:shadow-md hover:-translate-y-1 transition-all duration-200 group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="bg-brand-blue-light/20 p-3 rounded-lg group-hover:bg-brand-blue group-hover:text-white transition-colors duration-200">
                                    <x-heroicon-o-academic-cap class="w-8 h-8 text-brand-blue group-hover:text-white" />
                                </div>
                                <!-- CORRECCIÓN: Se restauró el componente puro de Heroicon. Al no estar envuelto en otra etiqueta <a>, es completamente válido en HTML -->
                                <x-heroicon-m-arrow-up-right
                                    class="w-5 h-5 text-gray-300 group-hover:text-brand-blue transition-colors" />
                            </div>

                            <h4 class="text-xl font-bold text-gray-900 mb-2">Estructura Profesional</h4>
                            <p class="font-secondary text-sm text-gray-500">Administración de profesiones, especialidades y
                                vinculación de roles.</p>
                        </div>
                    </a>
                @endcanany


                <!-- Tarjeta 3: Auditoría y Registros -->
                @can('ver.logs')
                    <a href="{{ route('system.activity-logs') }}"
                    class="block bg-white overflow-hidden shadow-sm sm:rounded-xl border-t-4 border-brand-pink hover:shadow-md hover:-translate-y-1 transition-all duration-200 group">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="bg-brand-pink/10 p-3 rounded-lg group-hover:bg-brand-pink group-hover:text-white transition-colors duration-200">
                                <x-heroicon-o-clipboard-document-list
                                    class="w-8 h-8 text-brand-pink group-hover:text-white" />
                            </div>
                            <x-heroicon-m-arrow-up-right
                                class="w-5 h-5 text-gray-300 group-hover:text-brand-pink transition-colors" />
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-2">Auditoría del Sistema</h4>
                        <p class="font-secondary text-sm text-gray-500">Visualización de historial de actividad, cambios
                            en modelos y registros.</p>
                    </div>
                </a>
                @endcan

            </div>

        </div>
    </div>
</x-app-layout>