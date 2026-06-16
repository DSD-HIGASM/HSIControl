<?php

use App\Models\Agent;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public $user;
    public $agent;

    public function mount()
    {
        $this->user = Auth::user();
        $this->agent = Agent::where('dni', $this->user->dni)->first();
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-bold text-gray-800 font-secondary uppercase tracking-wide">
            Información del Perfil
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Tus datos personales están vinculados a tu legajo de agente y son de solo lectura. Si necesitas modificarlos, contacta a administración.
        </p>
    </header>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-[11px] font-bold text-gray-500 font-secondary uppercase tracking-wider mb-1">DNI Usuario</label>
            <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-md text-gray-700 sm:text-sm">
                {{ $user->dni }}
            </div>
        </div>

        @if($agent)
            <div>
                <label class="block text-[11px] font-bold text-gray-500 font-secondary uppercase tracking-wider mb-1">Nombre Completo</label>
                <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-md text-gray-700 sm:text-sm">
                    {{ $agent->last_name }} {{ $agent->second_last_name }}, {{ $agent->first_name }} {{ $agent->second_first_name }}
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-500 font-secondary uppercase tracking-wider mb-1">Correo Electrónico</label>
                <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-md text-gray-700 sm:text-sm">
                    {{ $agent->email }}
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-500 font-secondary uppercase tracking-wider mb-1">Teléfono</label>
                <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-md text-gray-700 sm:text-sm">
                    {{ $agent->phone }}
                </div>
            </div>
        @else
            <div class="md:col-span-2 px-4 py-3 bg-amber-50 text-amber-700 border border-amber-200 rounded-md text-sm">
                No se encontró un legajo de agente vinculado a este número de DNI.
            </div>
        @endif
    </div>
</section>