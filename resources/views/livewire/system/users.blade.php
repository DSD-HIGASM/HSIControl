<div class="py-8 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 flex items-center justify-between">
            <div>
                <x-banner text="Accesos al Sistema"></x-banner>
                <p class="font-secondary text-brand-gray-custom mt-2">Administración de credenciales (DNI/Clave) y permisos directos.</p>
            </div>
            <a href="{{ route('system.config') }}" wire:navigate class="text-sm font-medium text-brand-cyan hover:text-brand-cyan-dark transition-colors flex items-center gap-1">
                <x-heroicon-m-arrow-left class="w-4 h-4" />
                Volver a Configuración
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white shadow-sm rounded-xl border-t-4 border-brand-blue overflow-hidden sticky top-6">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <x-heroicon-o-key class="w-5 h-5 text-brand-blue" />
                            {{ $editId ? 'Editar Credencial' : 'Nueva Credencial' }}
                        </h3>

                        <form wire:submit="save" class="space-y-5">
                            <div>
                                <label for="dni" class="block font-secondary text-sm font-medium text-gray-700">DNI (Usuario)</label>
                                <input wire:model="dni" type="number" id="dni" placeholder="Sin puntos ni espacios" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-blue focus:ring focus:ring-brand-blue focus:ring-opacity-20 sm:text-sm transition-colors [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                @error('dni') <span class="text-sm text-brand-pink mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="password" class="block font-secondary text-sm font-medium text-gray-700">Contraseña</label>
                                <input wire:model="password" type="password" id="password" placeholder="••••••••" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-blue focus:ring focus:ring-brand-blue focus:ring-opacity-20 sm:text-sm transition-colors">
                                @if($editId)
                                    <p class="text-xs text-gray-500 font-secondary mt-1">Dejá vacío para mantener la clave actual.</p>
                                @endif
                                @error('password') <span class="text-sm text-brand-pink mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="pt-2">
                                <label class="block font-secondary text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                                    <x-heroicon-o-shield-check class="w-4 h-4 text-brand-blue" />
                                    Permisos Asignados
                                </label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-2 max-h-60 overflow-y-auto space-y-1 shadow-inner">
                                    @forelse($availablePermissions as $permission)
                                        <label class="flex items-center gap-3 p-2 hover:bg-white rounded-md cursor-pointer transition-all border border-transparent hover:border-gray-200 hover:shadow-sm">
                                            <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->id }}" 
                                                class="w-4 h-4 rounded border-gray-300 text-brand-blue focus:ring-brand-blue transition-colors">
                                            <span class="text-xs font-bold text-gray-700 font-secondary uppercase tracking-wider">
                                                {{ str_replace('.', ' ', $permission->name) }}
                                            </span>
                                        </label>
                                    @empty
                                        <p class="text-xs text-gray-500 font-secondary p-2 text-center">No hay permisos creados en el sistema.</p>
                                    @endforelse
                                </div>
                                @error('selectedPermissions') <span class="text-sm text-brand-pink mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="pt-4 flex gap-2">
                                <button type="submit" class="flex-1 flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-blue hover:bg-brand-blue-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue transition-colors">
                                    <span wire:loading.remove wire:target="save">{{ $editId ? 'Actualizar' : 'Guardar' }}</span>
                                    <span wire:loading wire:target="save" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Procesando...
                                    </span>
                                </button>
                                
                                @if($editId)
                                    <button type="button" wire:click="resetForm" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-cyan transition-colors">
                                        Cancelar
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-brand-soft-100/30">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider font-secondary">DNI (Usuario)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider font-secondary">Agente / Permisos</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider font-secondary">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider font-secondary">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($users as $user)
                                    <tr class="hover:bg-gray-50 transition-colors {{ $user->trashed() ? 'bg-gray-50/50 opacity-60' : '' }}" wire:key="{{ $user->id }}">
                                        
                                        <td class="px-6 py-4 whitespace-nowrap align-top pt-5">
                                            <div class="text-sm font-bold text-brand-blue {{ $user->trashed() ? 'line-through text-gray-500' : '' }}">
                                                {{ number_format($user->dni, 0, ',', '.') }}
                                            </div>
                                        </td>

                                        <td class="px-6 py-4">
                                            @if($user->agent)
                                                <div class="text-sm font-bold text-gray-900 uppercase mb-2 flex items-center gap-2">
                                                    <x-heroicon-s-user class="w-4 h-4 text-gray-400" />
                                                    {{ $user->agent->last_name }}, {{ $user->agent->first_name }}
                                                </div>
                                            @else
                                                <div class="mb-2">
                                                    <span class="inline-flex items-center gap-1 text-xs font-secondary text-brand-pink bg-brand-pink/10 px-2 py-1 rounded-md font-medium">
                                                        <x-heroicon-m-exclamation-circle class="w-3.5 h-3.5" />
                                                        Sin padrón vinculado
                                                    </span>
                                                </div>
                                            @endif

                                            <div class="flex flex-wrap gap-1.5">
                                                @forelse($user->permissions as $perm)
                                                    <span class="inline-flex items-center rounded-md bg-brand-blue/5 px-2 py-1 text-[10px] font-medium text-brand-blue ring-1 ring-inset ring-brand-blue/20 font-secondary uppercase tracking-wider" title="{{ $perm->name }}">
                                                        {{ str_replace('.', ' ', $perm->name) }}
                                                    </span>
                                                @empty
                                                    <span class="text-xs text-gray-400 font-secondary italic flex items-center gap-1">
                                                        <x-heroicon-o-shield-exclamation class="w-4 h-4" />
                                                        Sin permisos asignados
                                                    </span>
                                                @endforelse
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap align-top pt-5">
                                            @if($user->trashed())
                                                <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 font-secondary">Inactivo</span>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-[11px] font-medium text-green-700 ring-1 ring-inset ring-green-600/20 font-secondary">Activo</span>
                                            @endif
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium align-top pt-4">
                                            <div class="flex justify-end gap-3">
                                                <button wire:click="edit({{ $user->id }})" class="text-brand-blue hover:text-brand-blue-dark transition-colors" title="Editar">
                                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                                </button>
                                                
                                                @if($user->trashed())
                                                    <button wire:click="restore({{ $user->id }})" class="text-green-600 hover:text-green-800 transition-colors" title="Reactivar">
                                                        <x-heroicon-o-arrow-path class="w-5 h-5" />
                                                    </button>
                                                @else
                                                    @if(auth()->id() !== $user->id)
                                                        <button wire:click="confirmDelete({{ $user->id }})" class="text-brand-pink hover:text-red-700 transition-colors" title="Desactivar">
                                                            <x-heroicon-o-trash class="w-5 h-5" />
                                                        </button>
                                                    @else
                                                        <span class="text-gray-300 w-5 h-5 inline-block" title="Tu usuario actual">
                                                            <x-heroicon-s-shield-check class="w-5 h-5" />
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 font-secondary text-sm">
                                            No hay credenciales cargadas en el sistema.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @if($confirmingDeletion)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" wire:click="cancelDelete" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-t-4 border-brand-pink">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-brand-pink/10 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-brand-pink" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                    Desactivar Acceso
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm font-secondary text-gray-500">
                                        ¿Estás seguro de que deseás desactivar esta credencial? El agente perderá el acceso inmediato al sistema hasta que la cuenta sea reactivada.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="executeDelete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-brand-pink text-base font-medium text-white hover:bg-[#c2145d] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-pink sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Sí, desactivar
                        </button>
                        <button type="button" wire:click="cancelDelete" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-cyan sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>