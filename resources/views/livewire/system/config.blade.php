<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Mantenemos tu componente de banner -->
        <div class="mb-8">
            <x-banner text="Configuración del Sistema"></x-banner>
        </div>

        <!-- Descripción de la sección -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Parámetros Generales</h2>
            <p class="font-secondary text-brand-gray-custom mt-1">Administración de catálogos, roles y usuarios del sistema central.</p>
        </div>

        <!-- Grid de Configuración -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Tipos de Documentos -->
            <a href="{{ route('system.document-types') }}" class="block bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-brand-cyan transition-all duration-200 group overflow-hidden">
                <div class="h-1 w-full bg-gray-100 group-hover:bg-brand-cyan transition-colors"></div>
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="bg-brand-soft-100/50 p-3 rounded-lg text-brand-cyan group-hover:scale-110 transition-transform duration-200">
                            <x-heroicon-o-identification class="w-6 h-6" />
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Tipos de Documentos</h3>
                    </div>
                    <p class="font-secondary text-sm text-gray-500">Gestión de los tipos de documento que pueden cargarse.</p>
                </div>
            </a>

            <!-- Rol de HSI -->
            <a href="{{ route('system.hsi-roles') }}" class="block bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-brand-blue transition-all duration-200 group overflow-hidden">
                <div class="h-1 w-full bg-gray-100 group-hover:bg-brand-blue transition-colors"></div>
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="bg-brand-blue-light/10 p-3 rounded-lg text-brand-blue group-hover:scale-110 transition-transform duration-200">
                            <x-heroicon-o-shield-check class="w-6 h-6" />
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Roles de HSI</h3>
                    </div>
                    <p class="font-secondary text-sm text-gray-500">Mapeo de permisos y niveles de acceso a la Historia de Salud Integrada.</p>
                </div>
            </a>

            <!-- Profesión -->
            <a href="{{ route('system.occupations') }}" class="block bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-brand-pink transition-all duration-200 group overflow-hidden">
                <div class="h-1 w-full bg-gray-100 group-hover:bg-brand-pink transition-colors"></div>
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="bg-brand-pink/10 p-3 rounded-lg text-brand-pink group-hover:scale-110 transition-transform duration-200">
                            <x-heroicon-o-briefcase class="w-6 h-6" />
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Profesiones</h3>
                    </div>
                    <p class="font-secondary text-sm text-gray-500">Catálogo general de disciplinas médicas, técnicas y administrativas.</p>
                </div>
            </a>

            <!-- Especialidad -->
            <a href="{{ route('system.specialties') }}" class="block bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-brand-cyan transition-all duration-200 group overflow-hidden">
                <div class="h-1 w-full bg-gray-100 group-hover:bg-brand-cyan transition-colors"></div>
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="bg-brand-soft-100/50 p-3 rounded-lg text-brand-cyan group-hover:scale-110 transition-transform duration-200">
                            <x-heroicon-o-academic-cap class="w-6 h-6" />
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Especialidades</h3>
                    </div>
                    <p class="font-secondary text-sm text-gray-500">Ramas específicas de atención asociadas a las profesiones del hospital.</p>
                </div>
            </a>

            <!-- Usuarios -->
            <a href="{{ route('system.users') }}" class="block bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-gray-800 transition-all duration-200 group overflow-hidden">
                <div class="h-1 w-full bg-gray-100 group-hover:bg-gray-800 transition-colors"></div>
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="bg-gray-100 p-3 rounded-lg text-gray-700 group-hover:scale-110 transition-transform duration-200">
                            <x-heroicon-o-users class="w-6 h-6" />
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Usuarios</h3>
                    </div>
                    <p class="font-secondary text-sm text-gray-500">Administración de usuarios de este sistema.</p>
                </div>
            </a>

            <!-- Servicios -->
            <a href="{{ route('system.services') }}" class="block bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-brand-pink transition-all duration-200 group overflow-hidden">
                <div class="h-1 w-full bg-gray-100 group-hover:bg-brand-pink transition-colors"></div>
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="bg-brand-pink/10 p-3 rounded-lg text-brand-pink group-hover:scale-110 transition-transform duration-200">
                            <x-heroicon-o-heart class="w-6 h-6" />
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Servicios</h3>
                    </div>
                    <p class="font-secondary text-sm text-gray-500">Catálogo general de servicios del hospital.</p>
                </div>
            </a>

        </div>
    </div>
</div>