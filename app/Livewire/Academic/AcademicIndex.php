<?php

namespace App\Livewire\Academic;

use App\Enums\AgentGender;
use App\Enums\AgentStatus;
use App\Models\AcademicAgent;
use App\Models\Agent;
use App\Models\HsiRole;
use App\Models\Occupation;
use App\Models\Service;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AcademicIndex extends Component
{
    use WithPagination;

    // Filtros Avanzados
    public string $search = '';
    public array $role_ids = [];
    public string $roles_operator = 'in';
    public array $service_ids = [];
    public string $services_operator = 'in';
    public string $hsi_status_filter = ''; // CORREGIDO: de 'array' a 'string'

    // Modal de Alta
    public bool $showCreateModal = false;
    public bool $dniExistsWarning = false;
    public ?string $existingAgentName = null;

    // Campos de Agente Base
    public string $new_last_name = '';
    public string $new_second_last_name = '';
    public string $new_first_name = '';
    public string $new_second_first_name = '';
    public string $new_dni = '';
    public string $new_gender = '';
    public string $new_phone = '';
    public string $new_email = '';

    // Campos Académicos UNLP
    public string $new_unlp_email = '';
    public string $new_facultad = 'Facultad de Ciencias Médicas';
    public string $new_carrera_catedra = '';
    public string $new_materia = '';
    public string $new_cargo_docente = 'Profesor Adjunto';
    public string $new_legajo_unlp = '';
    public string $new_autoridad_nombre = '';
    public string $new_autoridad_cargo = 'Decano/a';
    public string $new_expires_at = '';

    public function mount()
    {
        $this->new_expires_at = Carbon::now()->addMonths(6)->format('Y-m-d');
    }

    public function updatedNewDni($value): void
    {
        $this->dniExistsWarning = false;
        $this->existingAgentName = null;

        if (!empty($value)) {
            $existing = Agent::withTrashed()->where('dni', $value)->first();
            if ($existing) {
                $this->dniExistsWarning = true;
                $this->existingAgentName = "{$existing->last_name}, {$existing->first_name}";
            }
        }
    }

    public function toggleOperator(string $filterName): void
    {
        $prop = $filterName . '_operator';
        $this->$prop = $this->$prop === 'in' ? 'not_in' : 'in';
    }

    public function clearAllFilters(): void
    {
        $this->reset([
            'search', 'role_ids', 'service_ids', 'hsi_status_filter',
            'roles_operator', 'services_operator'
        ]);
    }

    public function hasActiveFilters(): bool
    {
        return !empty($this->search) || !empty($this->role_ids) || !empty($this->service_ids) || !empty($this->hsi_status_filter);
    }

    public function saveAcademicAgent()
    {
        $this->validate([
            'new_last_name' => 'required|string|max:100',
            'new_first_name' => 'required|string|max:100',
            'new_dni' => 'required|numeric',
            'new_gender' => 'required',
            'new_phone' => 'required|string',
            'new_email' => 'required|email',
            'new_unlp_email' => 'required|email',
            'new_facultad' => 'required|string',
            'new_carrera_catedra' => 'required|string',
            'new_materia' => 'required|string',
            'new_cargo_docente' => 'required|string',
            'new_autoridad_nombre' => 'required|string',
            'new_autoridad_cargo' => 'required|string',
            'new_expires_at' => 'required|date|after:today',
        ]);

        $unlpService = Service::firstOrCreate(
            ['name' => 'UNLP'],
            ['description' => 'Servicio de Docencia e Investigación UNLP']
        );

        $agent = Agent::withTrashed()->where('dni', $this->new_dni)->first();

        if ($agent) {
            if ($agent->trashed()) {
                $agent->restore();
            }
            $agent->update([
                'last_name' => mb_strtoupper($this->new_last_name),
                'second_last_name' => $this->new_second_last_name ? mb_strtoupper($this->new_second_last_name) : null,
                'first_name' => mb_strtoupper($this->new_first_name),
                'second_first_name' => $this->new_second_first_name ? mb_strtoupper($this->new_second_first_name) : null,
                'gender' => $this->new_gender,
                'email' => strtolower($this->new_email),
                'phone' => $this->new_phone,
                'service_id' => $unlpService->id,
                'status' => AgentStatus::ACTIVO,
            ]);
        } else {
            $agent = Agent::create([
                'last_name' => mb_strtoupper($this->new_last_name),
                'second_last_name' => $this->new_second_last_name ? mb_strtoupper($this->new_second_last_name) : null,
                'first_name' => mb_strtoupper($this->new_first_name),
                'second_first_name' => $this->new_second_first_name ? mb_strtoupper($this->new_second_first_name) : null,
                'dni' => $this->new_dni,
                'gender' => $this->new_gender,
                'email' => strtolower($this->new_email),
                'phone' => $this->new_phone,
                'service_id' => $unlpService->id,
                'status' => AgentStatus::ACTIVO,
            ]);
        }

        AcademicAgent::updateOrCreate(
            ['agent_id' => $agent->id],
            [
                'unlp_email' => strtolower($this->new_unlp_email),
                'personal_email' => strtolower($this->new_email),
                'facultad' => $this->new_facultad,
                'carrera_catedra' => $this->new_carrera_catedra,
                'materia' => $this->new_materia,
                'cargo_docente' => $this->new_cargo_docente,
                'legajo_unlp' => $this->new_legajo_unlp,
                'anexo_version' => '1.0',
                'anexo_signed_at' => Carbon::now(),
                'autoridad_firmante_nombre' => $this->new_autoridad_nombre,
                'autoridad_firmante_cargo' => $this->new_autoridad_cargo,
                'hsi_status' => 'pendiente_alta',
                'expires_at' => $this->new_expires_at,
            ]
        );

        return redirect()->route('agents.show');
    }

    public function render()
    {
        $query = Agent::whereHas('academicProfile')->with(['academicProfile', 'service', 'hsiRoles']);

        if (!empty($this->search)) {
            $s = "%{$this->search}%";
            $query->where(function ($q) use ($s) {
                $q->where('last_name', 'like', $s)
                    ->orWhere('first_name', 'like', $s)
                    ->orWhere('dni', 'like', $s)
                    ->orWhereHas('academicProfile', function ($aq) use ($s) {
                        $aq->where('unlp_email', 'like', $s)->orWhere('materia', 'like', $s);
                    });
            });
        }

        if (!empty($this->hsi_status_filter)) {
            $query->whereHas('academicProfile', fn($q) => $q->where('hsi_status', $this->hsi_status_filter));
        }

        return view('livewire.academic.academic-index', [
            'agents' => $query->orderBy('id', 'desc')->paginate(12),
            'services' => Service::orderBy('name')->get(),
            'hsiRoles' => HsiRole::orderBy('name')->get(),
            'genders' => AgentGender::cases(),
            'kpiTotal' => AcademicAgent::count(),
            'kpiActivos' => AcademicAgent::where('hsi_status', 'activo')->count(),
            'kpiPendientes' => AcademicAgent::where('hsi_status', 'pendiente_alta')->count(),
            'kpiPorVencer' => AcademicAgent::where('hsi_status', 'pendiente_renovacion')->count(),
            'kpiVencidos' => AcademicAgent::where('hsi_status', 'vencido')->count(),
        ]);
    }
}