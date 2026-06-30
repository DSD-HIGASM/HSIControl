<?php

namespace App\Livewire\Agents;

use App\Enums\AgentGender;
use App\Enums\AgentStatus;
use App\Models\Agent;
use App\Models\Occupation;
use App\Models\Service;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AgentIndex extends Component
{
    use WithPagination;

    // --- Filtros de la Tabla ---
    public string $search = '';

    public string $service_id = '';

    public string $profession_id = '';

    public string $status = '';

    // --- Modales ---
    public bool $showCreateModal = false;

    public bool $showExportModal = false;

    // --- Formulario Nuevo Agente ---
    public string $new_first_name = '';

    public ?string $new_second_first_name = null;

    public string $new_last_name = '';

    public ?string $new_second_last_name = null;

    public string $new_dni = '';

    public string $new_gender = '';

    public string $new_email = '';

    public string $new_phone = '';

    public ?int $new_service_id = null;

    protected function rules()
    {
        return [
            'new_first_name' => 'required|string|max:255',
            'new_second_first_name' => 'nullable|string|max:255',
            'new_last_name' => 'required|string|max:255',
            'new_second_last_name' => 'nullable|string|max:255',
            'new_dni' => 'required|numeric|digits_between:7,9',
            'new_gender' => 'required|in:masculino,femenino,x',
            'new_email' => 'required|email|max:255',
            'new_phone' => 'required|string|max:255',
            'new_service_id' => 'required|integer|exists:services,id',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedServiceId(): void
    {
        $this->resetPage();
    }

    public function updatedProfessionId(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function saveAgent()
    {
        $this->validate();

        $existingAgent = Agent::withTrashed()->where('dni', $this->new_dni)->first();

        if ($existingAgent) {
            if ($existingAgent->trashed()) {
                $existingAgent->restore();
            }

            $existingAgent->update([
                'first_name' => $this->new_first_name,
                'second_first_name' => $this->new_second_first_name,
                'last_name' => $this->new_last_name,
                'second_last_name' => $this->new_second_last_name,
                'gender' => $this->new_gender,
                'email' => $this->new_email,
                'phone' => $this->new_phone,
                'status' => AgentStatus::ACTIVO,
                'service_id' => $this->new_service_id,
            ]);

            $agent = $existingAgent;
        } else {
            $agent = Agent::create([
                'first_name' => $this->new_first_name,
                'second_first_name' => $this->new_second_first_name,
                'last_name' => $this->new_last_name,
                'second_last_name' => $this->new_second_last_name,
                'dni' => $this->new_dni,
                'gender' => $this->new_gender,
                'email' => $this->new_email,
                'phone' => $this->new_phone,
                'status' => AgentStatus::PENDIENTE,
                'service_id' => $this->new_service_id,
            ]);
        }

        $this->showCreateModal = false;

        return $this->redirectRoute('agents.show', $agent->id, navigate: true);
    }

    public function render()
    {
        $query = Agent::with([
            'service',
            'agentProfessions.profession',
            'agentProfessions.specialty',
            'documents',
            'hsiRoles.documentTypes',
        ]);

        // --- Orden por prioridad de Enum y luego por ID ---
        $query->orderByRaw("
        CASE status
            WHEN '".AgentStatus::PENDIENTE->value."' THEN ".AgentStatus::PENDIENTE->priority()."
            WHEN '".AgentStatus::ACTIVO->value."' THEN ".AgentStatus::ACTIVO->priority()."
            WHEN '".AgentStatus::INACTIVO->value."' THEN ".AgentStatus::INACTIVO->priority().'
            ELSE 4
        END ASC
    ')->orderBy('id', 'desc');

        if (! empty($this->status) && ($status = AgentStatus::tryFrom($this->status))) {
            $query->where('status', $status->value);
        }

        if (! empty($this->service_id)) {
            $query->where('service_id', $this->service_id);
        }

        if (! empty($this->profession_id)) {
            $query->whereHas('agentProfessions', function ($q) {
                $q->where('profession_id', $this->profession_id);
            });
        }

        if (! empty($this->search)) {
            // 1. Limpiamos las tildes y pasamos a minúscula el texto del usuario en PHP
            $cleanSearch = mb_strtolower(trim($this->search), 'UTF-8');
            $cleanSearch = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'ä', 'ë', 'ï', 'ö', 'ü'],
                ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u'],
                $cleanSearch
            );

            // Separamos el texto limpio en palabras
            $terms = preg_split('/\s+/', $cleanSearch);

            // 2. Armamos un bloque SQL bruto para normalizar las columnas de la BD al vuelo.
            // Cubrimos mayúsculas y minúsculas por si el motor falla al bajar las mayúsculas con tilde.
            $normalizeSql = function ($column) {
                return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER({$column}), 'á', 'a'), 'é', 'e'), 'í', 'i'), 'ó', 'o'), 'ú', 'u'), 'Á', 'a'), 'É', 'e'), 'Í', 'i'), 'Ó', 'o'), 'Ú', 'u')";
            };

            $query->where(function ($q) use ($terms, $normalizeSql) {
                foreach ($terms as $term) {
                    $q->where(function ($subQ) use ($term, $normalizeSql) {
                        $subQ->where('dni', 'like', "%{$term}%")
                            ->orWhereRaw($normalizeSql('last_name').' LIKE ?', ["%{$term}%"])
                            ->orWhereRaw($normalizeSql('first_name').' LIKE ?', ["%{$term}%"])
                            ->orWhere('phone', 'like', "%{$term}%");
                    });
                }
            });
        }

        return view('livewire.agents.agent-index', [
            'agents' => $query->paginate(15),
            'services' => Service::orderBy('name')->get(),
            'professions' => Occupation::orderBy('name')->get(),
            'genders' => AgentGender::selectableCases(),
        ]);
    }
}
