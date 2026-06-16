<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-bold text-gray-800 font-secondary uppercase tracking-wide">
            Actualizar Contraseña
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Asegúrate de usar una contraseña larga y segura para proteger tu acceso al sistema.
        </p>
    </header>

    <form wire:submit="updatePassword" class="mt-6 space-y-6 max-w-xl">
        <div>
            <label for="update_password_current_password" class="block text-[11px] font-bold text-gray-500 font-secondary uppercase tracking-wider mb-1">
                Contraseña Actual
            </label>
            <input wire:model="current_password" id="update_password_current_password" type="password" 
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm transition-colors" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="block text-[11px] font-bold text-gray-500 font-secondary uppercase tracking-wider mb-1">
                Nueva Contraseña
            </label>
            <input wire:model="password" id="update_password_password" type="password" 
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm transition-colors" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-[11px] font-bold text-gray-500 font-secondary uppercase tracking-wider mb-1">
                Confirmar Nueva Contraseña
            </label>
            <input wire:model="password_confirmation" id="update_password_password_confirmation" type="password" 
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-cyan focus:ring-brand-cyan sm:text-sm transition-colors" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" 
                class="inline-flex justify-center items-center px-6 py-2 border border-transparent text-sm font-bold rounded-md text-white bg-brand-cyan hover:bg-cyan-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-cyan transition-colors uppercase tracking-wide">
                Guardar
            </button>

            <x-action-message class="me-3" on="password-updated">
                <div class="px-3 py-2 bg-green-50 text-green-700 border border-green-200 rounded-md text-xs font-bold flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Actualizada
                </div>
            </x-action-message>
        </div>
    </form>
</section>