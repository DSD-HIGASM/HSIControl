<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $dni = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'dni' => ['required', 'integer'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['dni' => $this->dni, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'dni' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        session()->regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-10 text-center">
        <div class="flex justify-center mb-4 lg:hidden">
            <div class="bg-brand-cyan/10 p-3 rounded-full">
                <x-heroicon-s-building-office-2 class="w-10 h-10 text-brand-cyan" />
            </div>
        </div>
        
        <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Acceso al Sistema</h2>
        <p class="font-secondary text-sm text-brand-gray-custom mt-2">Ingrese sus credenciales administrativas</p>
    </div>

    <form wire:submit="login" class="space-y-6">
        
        <div>
            <label for="dni" class="block font-secondary text-sm font-medium text-gray-700">Documento (DNI)</label>
            <div class="mt-2 relative rounded-md shadow-sm group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-heroicon-o-identification class="h-5 w-5 text-gray-400 group-focus-within:text-brand-cyan transition-colors" />
                </div>
                <input wire:model="dni" id="dni" type="number" required autofocus autocomplete="username"
                    class="block w-full pl-10 rounded-lg border-gray-300 py-2.5 text-gray-900 focus:border-brand-cyan focus:ring focus:ring-brand-cyan focus:ring-opacity-20 transition duration-200 sm:text-sm placeholder:text-gray-400 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" 
                    placeholder="Ej: 12345678">
            </div>
            @error('dni') <span class="text-sm text-brand-pink mt-2 block font-medium">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password" class="block font-secondary text-sm font-medium text-gray-700">Contraseña</label>
            <div class="mt-2 relative rounded-md shadow-sm group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-heroicon-o-lock-closed class="h-5 w-5 text-gray-400 group-focus-within:text-brand-cyan transition-colors" />
                </div>
                <input wire:model="password" id="password" type="password" required autocomplete="current-password"
                    class="block w-full pl-10 rounded-lg border-gray-300 py-2.5 text-gray-900 focus:border-brand-cyan focus:ring focus:ring-brand-cyan focus:ring-opacity-20 transition duration-200 sm:text-sm placeholder:text-gray-400" 
                    placeholder="••••••••">
            </div>
            @error('password') <span class="text-sm text-brand-pink mt-2 block font-medium">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center justify-between pt-2">
            <div class="flex items-center">
                <input wire:model="remember" id="remember" type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 text-brand-cyan focus:ring-brand-cyan transition duration-200 cursor-pointer">
                <label for="remember" class="font-secondary ml-2 block text-sm text-brand-gray-custom cursor-pointer select-none">Mantener sesión activa</label>
            </div>
        </div>

        <div class="pt-4">
            <button type="submit"
                class="w-full flex justify-center items-center py-3 px-4 rounded-lg shadow-sm text-sm font-semibold text-white bg-brand-cyan hover:bg-brand-cyan-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-cyan transition-all duration-200">
                
                <span wire:loading.remove wire:target="login">Ingresar al sistema</span>
                
                <span wire:loading wire:target="login" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Verificando...
                </span>
            </button>
        </div>
    </form>
</div>