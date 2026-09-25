<?php

namespace App\Livewire\Agents;

use App\Enums\AgentGender;
use App\Enums\AgentStatus;
use App\Models\Agent;
use App\Models\DocumentType;
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

    // --- Búsqueda Textual ---
    public string $search = '';

    // --- Filtros Multi-selección con Operadores ---
    public string $roles_operator = 'in'; // 'in' (TIENE) | 'not_in' (NO TIENE)
    public array $role_ids = [];

    public string $services_operator = 'in'; // 'in' (ES) | 'not_in' (NO ES)
    public array $service_ids = [];

    public string $professions_operator = 'in'; // 'in' (TIENE) | 'not_in' (NO TIENE)
    public array $profession_ids = [];

    public string $statuses_operator = 'in'; // 'in' (ES) | 'not_in' (NO ES)
    public array $statuses = [];

    public string $documents_operator = 'in'; // 'in' (TIENE) | 'not_in' (NO TIENE / ADEUDA)
    public array $doc_type_ids = [];

    // --- Modales ---
    public bool $showCreateModal = false;
    public bool $showExportModal = false;
    public bool $showTokenModal = false;
    public ?string $generatedToken = null;
    public bool $showPendingModal = false;

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

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedRoleIds(): void { $this->resetPage(); }
    public function updatedRolesOperator(): void { $this->resetPage(); }
    public function updatedServiceIds(): void { $this->resetPage(); }
    public function updatedServicesOperator(): void { $this->resetPage(); }
    public function updatedProfessionIds(): void { $this->resetPage(); }
    public function updatedProfessionsOperator(): void { $this->resetPage(); }
    public function updatedStatuses(): void { $this->resetPage(); }
    public function updatedStatusesOperator(): void { $this->resetPage(); }
    public function updatedDocTypeIds(): void { $this->resetPage(); }
    public function updatedDocumentsOperator(): void { $this->resetPage(); }

    public function toggleOperator(string $filter): void
    {
        $prop = $filter . '_operator';
        if (property_exists($this, $prop)) {
            $this->{$prop} = $this->{$prop} === 'in' ? 'not_in' : 'in';
            $this->resetPage();
        }
    }

    public function clearAllFilters(): void
    {
        $this->reset([
            'search',
            'role_ids',
            'roles_operator',
            'service_ids',
            'services_operator',
            'profession_ids',
            'professions_operator',
            'statuses',
            'statuses_operator',
            'doc_type_ids',
            'documents_operator',
        ]);
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return ! empty($this->search)
            || ! empty($this->role_ids)
            || ! empty($this->service_ids)
            || ! empty($this->profession_ids)
            || ! empty($this->statuses)
            || ! empty($this->doc_type_ids);
    }

    public function getHumanDescriptionProperty(): string
    {
        if (! $this->hasActiveFilters()) {
            return 'Mostrando todos los agentes sin filtros aplicados.';
        }

        $clauses = [];

        if (! empty($this->search)) {
            $clauses[] = 'cuyo nombre, apellido o DNI contenga <strong class="text-gray-900 font-bold">"'.$this->search.'"</strong>';
        }

        if (! empty($this->role_ids)) {
            $names = HsiRole::whereIn('id', $this->role_ids)->pluck('name')->map(fn ($n) => mb_strtoupper($n))->implode(', ');
            $op = $this->roles_operator === 'not_in' ? 'NO TENGAN el rol' : 'TENGAN el rol';
            $color = $this->roles_operator === 'not_in' ? 'text-brand-pink' : 'text-brand-cyan-dark';
            $clauses[] = "que <span class=\"font-bold uppercase {$color}\">{$op}</span> <strong class=\"text-gray-900\">({$names})</strong>";
        }

        if (! empty($this->service_ids)) {
            $names = Service::whereIn('id', $this->service_ids)->pluck('name')->implode(', ');
            $op = $this->services_operator === 'not_in' ? 'NO PERTENEZCAN al servicio' : 'PERTENEZCAN al servicio';
            $color = $this->services_operator === 'not_in' ? 'text-brand-pink' : 'text-brand-cyan-dark';
            $clauses[] = "que <span class=\"font-bold uppercase {$color}\">{$op}</span> <strong class=\"text-gray-900\">({$names})</strong>";
        }

        if (! empty($this->profession_ids)) {
            $names = Occupation::whereIn('id', $this->profession_ids)->pluck('name')->implode(', ');
            $op = $this->professions_operator === 'not_in' ? 'NO TENGAN la profesión' : 'TENGAN la profesión';
            $color = $this->professions_operator === 'not_in' ? 'text-brand-pink' : 'text-brand-cyan-dark';
            $clauses[] = "que <span class=\"font-bold uppercase {$color}\">{$op}</span> <strong class=\"text-gray-900\">({$names})</strong>";
        }

        if (! empty($this->statuses)) {
            $names = collect($this->statuses)->map(fn ($s) => AgentStatus::tryFrom($s)?->label() ?? $s)->implode(', ');
            $op = $this->statuses_operator === 'not_in' ? 'NO ESTÉN en estado' : 'ESTÉN en estado';
            $color = $this->statuses_operator === 'not_in' ? 'text-brand-pink' : 'text-brand-cyan-dark';
            $clauses[] = "que <span class=\"font-bold uppercase {$color}\">{$op}</span> <strong class=\"text-gray-900\">{$names}</strong>";
        }

        if (! empty($this->doc_type_ids)) {
            $names = DocumentType::whereIn('id', $this->doc_type_ids)->pluck('name')->implode(', ');
            $op = $this->documents_operator === 'not_in' ? 'ADEUDEN / NO TENGAN PRESENTADO' : 'TENGAN PRESENTADO';
            $color = $this->documents_operator === 'not_in' ? 'text-brand-pink' : 'text-brand-cyan-dark';
            $clauses[] = "que <span class=\"font-bold uppercase {$color}\">{$op}</span> <strong class=\"text-gray-900\">({$names})</strong>";
        }

        return 'Mostrando agentes '.implode(' <strong class="text-gray-400 font-bold mx-1">Y</strong> ', $clauses).'.';
    }

    public function generateApiToken()
    {
        $tokenResult = auth()->user()->createToken('Extension HSI');
        $this->generatedToken = $tokenResult->plainTextToken;
    }

    public function closeTokenModal()
    {
        $this->showTokenModal = false;
        $this->generatedToken = null;
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
        $sync = HsiPatientSync::findOrFail($syncId);

        $completed = $sync->completed_data;
        $personal = $sync->personal_info;
        $user = $sync->user_data;
        $roles = $sync->roles_data;
        $dni = $sync->dni;

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
                'person_id' => $completed['id'] ?? $existingAgent->person_id,
                'user_id' => $user['id'] ?? $existingAgent->user_id,
                'user' => $user['username'] ?? $existingAgent->user,
                'status' => AgentStatus::ACTIVO,
            ]);

            $agent = $existingAgent;
        } else {
            $genderDesc = strtolower($completed['gender']['description'] ?? '');
            $gender = match ($genderDesc) {
                'femenino' => AgentGender::FEMENINO->value,
                'masculino' => AgentGender::MASCULINO->value,
                default => AgentGender::X->value,
            };

            $agent = Agent::create([
                'first_name' => $completed['firstName'],
                'second_first_name' => $completed['middleName'] ?? null,
                'last_name' => $completed['lastName'],
                'second_last_name' => $completed['otherLastNames'] ?? null,
                'dni' => $dni,
                'gender' => $gender,
                'email' => $personal['email'] ?? null,
                'phone' => ($personal['phonePrefix'] ?? '').($personal['phoneNumber'] ?? ''),
                'person_id' => $completed['id'] ?? null,
                'user_id' => $user['id'] ?? null,
                'user' => $user['username'] ?? null,
                'status' => AgentStatus::PENDIENTE,
            ]);
        }

        if (! empty($roles)) {
            $roleNames = collect($roles)->map(fn ($r) => mb_strtolower(trim($r['roleDescription'])))->toArray();

            $roleIds = HsiRole::where(function ($q) use ($roleNames) {
                foreach ($roleNames as $name) {
                    $q->orWhereRaw('LOWER(name) = ?', [$name]);
                }
            })->pluck('id');

            $agent->hsiRoles()->sync($roleIds);
        }

        $sync->update(['processed_at' => now()]);
        $this->showPendingModal = false;

        return $this->redirectRoute('agents.show', $agent->id, navigate: true);
    }

    public function render()
    {
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

        $query = Agent::with([
            'service',
            'agentProfessions.profession',
            'agentProfessions.specialty',
            'documents',
            'hsiRoles.documentTypes',
        ]);

        $query->orderByRaw("
            CASE status
                WHEN '".AgentStatus::PENDIENTE->value."' THEN ".AgentStatus::PENDIENTE->priority()."
                WHEN '".AgentStatus::ACTIVO->value."' THEN ".AgentStatus::ACTIVO->priority()."
                WHEN '".AgentStatus::INACTIVO->value."' THEN ".AgentStatus::INACTIVO->priority().'
                ELSE 4
            END ASC
        ')->orderBy('id', 'desc');

        // 1. Roles HSI
        if (! empty($this->role_ids)) {
            if ($this->roles_operator === 'not_in') {
                $query->whereDoesntHave('hsiRoles', fn ($q) => $q->whereIn('hsi_roles.id', $this->role_ids));
            } else {
                $query->whereHas('hsiRoles', fn ($q) => $q->whereIn('hsi_roles.id', $this->role_ids));
            }
        }

        // 2. Servicio Base
        if (! empty($this->service_ids)) {
            if ($this->services_operator === 'not_in') {
                $query->where(function ($sub) {
                    $sub->whereNotIn('service_id', $this->service_ids)
                        ->orWhereNull('service_id');
                });
            } else {
                $query->whereIn('service_id', $this->service_ids);
            }
        }

        // 3. Profesión
        if (! empty($this->profession_ids)) {
            if ($this->professions_operator === 'not_in') {
                $query->whereDoesntHave('agentProfessions', fn ($q) => $q->whereIn('profession_id', $this->profession_ids));
            } else {
                $query->whereHas('agentProfessions', fn ($q) => $q->whereIn('profession_id', $this->profession_ids));
            }
        }

        // 4. Estado
        if (! empty($this->statuses)) {
            if ($this->statuses_operator === 'not_in') {
                $query->whereNotIn('status', $this->statuses);
            } else {
                $query->whereIn('status', $this->statuses);
            }
        }

        // 5. Documentación
        if (! empty($this->doc_type_ids)) {
            if ($this->documents_operator === 'not_in') {
                // Adeuda o le falta al menos uno de los documentos seleccionados
                $query->where(function ($sub) {
                    foreach ($this->doc_type_ids as $docId) {
                        $sub->orWhereDoesntHave('documents', fn ($q) => $q->where('type_id', $docId)->whereNull('deleted_at'));
                    }
                });
            } else {
                // Posee entregados TODOS los documentos seleccionados
                foreach ($this->doc_type_ids as $docId) {
                    $query->whereHas('documents', fn ($q) => $q->where('type_id', $docId)->whereNull('deleted_at'));
                }
            }
        }

        // 6. Búsqueda Textual
        if (! empty($this->search)) {
            $cleanSearch = mb_strtolower(trim($this->search), 'UTF-8');
            $cleanSearch = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'ä', 'ë', 'ï', 'ö', 'ü'],
                ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u'],
                $cleanSearch
            );

            $terms = preg_split('/\s+/', $cleanSearch);

            $normalizeSql = function ($column) {
                return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER({$column}), 'á', 'a'), 'é', 'e'), 'í', 'i'), 'ó', 'o'), 'ú', 'u'), 'Á', 'a'), 'É', 'e'), 'Í', 'i'), 'Ó', 'o'), 'Ú', 'u')";
            };

            $query->where(function ($q) use ($terms, $normalizeSql) {
                foreach ($terms as $term) {
                    $termNumerico = preg_replace('/[^0-9]/', '', $term);

                    $q->where(function ($subQ) use ($term, $termNumerico, $normalizeSql) {
                        if (! empty($termNumerico)) {
                            $subQ->where('dni', 'like', "%{$termNumerico}%");
                        } else {
                            $subQ->whereRaw('1 = 0');
                        }

                        $subQ->orWhereRaw($normalizeSql('last_name').' LIKE ?', ["%{$term}%"])
                            ->orWhereRaw($normalizeSql('second_last_name').' LIKE ?', ["%{$term}%"])
                            ->orWhereRaw($normalizeSql('first_name').' LIKE ?', ["%{$term}%"])
                            ->orWhereRaw($normalizeSql('second_first_name').' LIKE ?', ["%{$term}%"]);

                        if (! empty($termNumerico)) {
                            $subQ->orWhere('phone', 'like', "%{$termNumerico}%");
                        } else {
                            $subQ->orWhere('phone', 'like', "%{$term}%");
                        }
                    });
                }
            });
        }

        return view('livewire.agents.agent-index', [
            'agents'             => $query->paginate(15),
            'services'           => Service::orderBy('name')->get(),
            'professions'        => Occupation::orderBy('name')->get(),
            'hsiRoles'           => HsiRole::orderBy('name')->get(),
            'documentTypes'      => DocumentType::orderBy('name')->get(),
            'genders'            => AgentGender::selectableCases(),
            'pending_sync_count' => $pendingImports->count(),
            'pendingImports'     => $pendingImports,
        ]);
    }
}