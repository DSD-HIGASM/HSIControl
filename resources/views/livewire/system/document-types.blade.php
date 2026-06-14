<div class="py-8 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-8 flex items-center justify-between">
            <div>
                <x-banner text="Tipos de Documentos"></x-banner>
                <p class="font-secondary text-brand-gray-custom mt-2">Administración del catálogo de identificaciones
                    válidas.</p>
            </div>
            <a href="{{ route('system.config') }}" wire:navigate
                class="text-sm font-medium text-brand-cyan hover:text-brand-cyan-dark transition-colors flex items-center gap-1">
                <x-heroicon-m-arrow-left class="w-4 h-4" />
                Volver a Configuración
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-1">
                <div class="bg-white shadow-sm rounded-xl border-t-4 border-brand-cyan overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">
                            {{ $editId ? 'Editar Documento' : 'Nuevo Documento' }}
                        </h3>

                        <form wire:submit="save" class="space-y-4">
                            <div>
                                <label for="name" class="block font-secondary text-sm font-medium text-gray-700">Nombre
                                    o Sigla</label>
                                <input wire:model="name" type="text" id="name" placeholder="Ej: DNI, PASAPORTE"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring focus:ring-brand-cyan focus:ring-opacity-20 sm:text-sm transition-colors uppercase">
                                @error('name') <span
                                    class="text-sm text-brand-pink mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="pt-2 flex gap-2">
                                <button type="submit"
                                    class="flex-1 flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-cyan hover:bg-brand-cyan-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-cyan transition-colors">
                                    <span wire:loading.remove
                                        wire:target="save">{{ $editId ? 'Actualizar' : 'Guardar' }}</span>
                                    <span wire:loading wire:target="save" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Procesando...
                                    </span>
                                </button>

                                @if($editId)
                                    <button type="button" wire:click="resetForm"
                                        class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-cyan transition-colors">
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
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider font-secondary">
                                        ID</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider font-secondary">
                                        Nombre</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider font-secondary">
                                        Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($documentTypes as $type)
                                    <tr class="hover:bg-gray-50 transition-colors {{ $type->trashed() ? 'bg-gray-50/50 opacity-60' : '' }}"
                                        wire:key="{{ $type->id }}">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-brand-gray-custom font-secondary">
                                            #{{ $type->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="text-sm font-bold text-gray-900 {{ $type->trashed() ? 'line-through' : '' }}">
                                                    {{ $type->name }}</div>

                                                @if($type->trashed())
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 font-secondary">Inactivo</span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 font-secondary">Activo</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end gap-3">
                                                <button wire:click="edit({{ $type->id }})"
                                                    class="text-brand-blue hover:text-brand-blue-dark transition-colors"
                                                    title="Editar">
                                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                                </button>

                                                @if($type->trashed())
                                                    <button wire:click="restore({{ $type->id }})"
                                                        class="text-green-600 hover:text-green-800 transition-colors"
                                                        title="Reactivar">
                                                        <x-heroicon-o-arrow-path class="w-5 h-5" />
                                                    </button>
                                                @else
                                                    <button wire:click="confirmDelete({{ $type->id }})"
                                                        class="text-brand-pink hover:text-red-700 transition-colors"
                                                        title="Desactivar">
                                                        <x-heroicon-o-trash class="w-5 h-5" />
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500 font-secondary text-sm">
                                            No hay tipos de documentos cargados en el sistema.
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

                <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm"
                    wire:click="cancelDelete" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-t-4 border-brand-pink">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-brand-pink/10 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-brand-pink" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                    Eliminar Tipo de Documento
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm font-secondary text-gray-500">
                                        ¿Estás seguro de que deseás eliminar este registro? Esta acción enviará el dato a la
                                        papelera y no estará disponible para nuevos agentes.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="executeDelete"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-brand-pink text-base font-medium text-white hover:bg-[#c2145d] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-pink sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Sí, eliminar
                        </button>
                        <button type="button" wire:click="cancelDelete"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-cyan sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>