<?php

namespace App\Livewire\Agents;

use App\Enums\AgentGender;
use App\Enums\AgentStatus;
use App\Models\Agent;
use App\Models\HsiPatientSync;
use App\Models\HsiRole;
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

    public bool $showTokenModal = false;

    public ?string $generatedToken = null;

    public bool $showPendingModal = false;

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

    public function generateApiToken()
    {
        // [Inferencia] Usamos el usuario autenticado para emitir el token de Sanctum
        $tokenResult = auth()->user()->createToken('Extension HSI');

        // Mostramos el texto plano del token solo esta vez
        $this->generatedToken = $tokenResult->plainTextToken;
    }

    public function closeTokenModal()
    {
        $this->showTokenModal = false;
        $this->generatedToken = null; // Limpiamos por seguridad
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

    public function processImport($syncId)
    {
        // 1. Recuperamos el registro temporal de la tabla intermedia
        $sync = HsiPatientSync::findOrFail($syncId);

        // 2. Extraemos los bloques JSON decodificados automáticamente por los casts
        $completed = $sync->completed_data;
        $personal = $sync->personal_info;
        $user = $sync->user_data;
        $roles = $sync->roles_data;

        $dni = $sync->dni;

        // 3. Verificamos duplicados de última hora en el padrón real
        $existingAgent = Agent::withTrashed()->where('dni', $dni)->first();

        if ($existingAgent) {
            if ($existingAgent->trashed()) {
                $existingAgent->restore();
            }

            $existingAgent->update([
                'first_name' => $completed['firstName'] ?? $existingAgent->first_name,
                'second_first_name' => $completed['middleName'] ?? $existingAgent->second_first_name,
                'last_name' => $completed['lastName'] ?? $existingAgent->last_name,
                'email' => $personal['email'] ?? $existingAgent->email,
                'phone' => isset($personal['phonePrefix']) ? ($personal['phonePrefix'].$personal['phoneNumber']) : $existingAgent->phone,
                'person_id' => $completed['person']['id'] ?? $existingAgent->person_id,
                'user_id' => $user['id'] ?? $existingAgent->user_id,
                'user' => $user['username'] ?? $existingAgent->user,
                'status' => AgentStatus::ACTIVO,
            ]);

            $agent = $existingAgent;
        } else {
            // Mapeo genérico de género
            $genderDesc = strtolower($completed['gender']['description'] ?? '');
            $gender = match ($genderDesc) {
                'femenino' => AgentGender::FEMENINO->value,
                'masculino' => AgentGender::MASCULINO->value,
                default => AgentGender::X->value,
            };

            // 4. Inserción limpia en la tabla agents
            $agent = Agent::create([
                'first_name' => $completed['firstName'],
                'second_first_name' => $completed['middleName'] ?? null,
                'last_name' => $completed['lastName'],
                'second_last_name' => $completed['otherLastNames'] ?? null,
                'dni' => $dni,
                'gender' => $gender,
                'email' => $personal['email'] ?? null,
                'phone' => ($personal['phonePrefix'] ?? '').($personal['phoneNumber'] ?? ''),
                'person_id' => $completed['person']['id'] ?? null,
                'user_id' => $user['id'] ?? null,
                'user' => $user['username'] ?? null,
                'status' => AgentStatus::PENDIENTE,
            ]);
        }

        // 5. Sincronización e impacto de roles HSI ignorando mayúsculas/minúsculas
        if (! empty($roles)) {
            // Extraemos los nombres y los normalizamos a minúsculas
            $roleNames = collect($roles)->map(fn ($r) => mb_strtolower(trim($r['roleDescription'])))->toArray();

            // Cambiamos el whereRaw por un query builder limpio con funciones nativas
            $roleIds = HsiRole::where(function ($q) use ($roleNames) {
                foreach ($roleNames as $name) {
                    $q->orWhereRaw('LOWER(name) = ?', [$name]);
                }
            })->pluck('id');

            $agent->hsiRoles()->sync($roleIds);
        }

        // 6. Marcamos el registro intermedio como procesado
        $sync->update([
            'processed_at' => now(),
        ]);

        $this->showPendingModal = false;

        // 7. Redirección limpia al dashboard/legajo usando tu formato estándar con wire:navigate
        return $this->redirectRoute('agents.show', $agent->id, navigate: true);
    }

    public function render()
{
    // --- NUEVO: Filtro de visibilidad para importaciones pendientes ---
    // Trae globales (is_global = true) O individuales que correspondan al usuario logueado
    $pendingImports = HsiPatientSync::whereNull('processed_at')
        ->where(function ($query) {
            $query->where('is_global', true)
                ->orWhere(function ($subQuery) {
                    $subQuery->where('is_global', false)
                             ->where('user_id', auth()->id());
                });
        })
        ->orderBy('created_at', 'desc')
        ->get();

    // Mantenemos tu variable dinámica basándonos en la consulta filtrada
    $pendingSyncCount = $pendingImports->count();

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
        'pending_sync_count' => $pendingSyncCount,
        'pendingImports' => $pendingImports, // <-- Enviado directo a la vista del modal
    ]);
}
}
