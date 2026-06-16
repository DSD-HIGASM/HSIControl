<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Identificador del Sistema (Sin logos de HSI) -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 group">
                        <div class="p-1.5 rounded-lg transition-colors">
                            <x-logos.hospital-color class="h-14 w-14 text-white" />
                        </div>
                        <span class="font-extrabold text-2xl tracking-tight">
                            <span class="text-brand-cyan">HSI</span><span class="text-black">Control</span>
                        </span>

                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboarda')"
                        class="font-secondary text-brand-gray-custom focus:text-brand-cyan-dark hover:text-brand-cyan transition-colors">
                        Panel Principal
                    </x-nav-link>
                    <x-nav-link :href="route('agents.index')" :active="request()->routeIs('agents.*')"
                        class="font-secondary text-brand-gray-custom focus:text-brand-cyan-dark hover:text-brand-cyan transition-colors">
                        Padrón de personal
                    </x-nav-link>

                    @canany(['configurar.documentos', 'configurar.roles', 'configurar.profesiones', 'configurar.especialidades', 'configurar.usuarios', 'configurar.servicios'])
                        <x-nav-link :href="route('system.config')" :active="request()->routeIs(['system.config', 'system.document-types', 'system.occupations', 'system.specialties', 'system.users', 'system.permissions', 'system.hsi-roles'])"
                            class="font-secondary text-brand-gray-custom focus:text-brand-cyan-dark hover:text-brand-cyan transition-colors">
                            Configuración
                        </x-nav-link>
                    @endcanany

                    @can('ver.logs')
                        <x-nav-link :href="route('system.activity-logs')"
                            :active="request()->routeIs('system.activity-logs')"
                            class="font-secondary text-brand-gray-custom focus:text-brand-cyan-dark hover:text-brand-cyan transition-colors">
                            Logs
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-secondary font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                                x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate class="font-secondary">
                            Mi Perfil
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-left">
                            <x-dropdown-link class="font-secondary text-brand-pink hover:bg-brand-pink/10">
                                Cerrar Sesión
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Panel Principal
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800"
                    x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                    x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->dni ?? auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    Mi Perfil
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-left">
                    <x-responsive-nav-link>
                        Cerrar Sesión
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>