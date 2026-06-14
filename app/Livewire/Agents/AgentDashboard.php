<?php

namespace App\Livewire\Agents;

use App\Models\Agent;
use App\Models\AgentProfessionSpecialty;
use App\Models\Registration;
use App\Models\Residency;
use App\Models\ServiceBoss;
use App\Models\HsiRoleAgent;
use App\Models\AgentDocument;
use App\Models\Occupation;
use App\Models\Speciality;
use App\Models\Service;
use App\Models\HierarchicalUnit;
use App\Models\HsiRole;
use App\Models\DocumentType;
use App\Enums\RegistrationScope;
use App\Enums\RegistrationType;
use App\Enums\AgentGender;
use App\Enums\AgentStatus;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class AgentDashboard extends Component
{
    use WithFileUploads;

    public Agent $agent;

    #[Url]
    public string $tab = 'personal';

    // --- ESTADOS DE LOS MODALES ---
    public bool $showEditModal = false;
    public bool $showProfModal = false;
    public bool $showRegModal = false;
    public bool $showResModal = false;
    public bool $showBossModal = false;
    public bool $showRoleModal = false;
    public bool $showUnitModal = false;
    public bool $showDocModal = false;
    public bool $showHsiModal = false; // Nuevo modal para Credenciales HSI

    // --- VARIABLES DE FORMULARIOS ---
    
    // Edición de Agente
    public $edit_first_name, $edit_second_first_name, $edit_last_name, $edit_second_last_name;
    public $edit_dni, $edit_gender, $edit_phone, $edit_email, $edit_status, $edit_service_id;

    // Profesión
    public $prof_profession_id, $prof_specialty_id;
    
    // Matrícula
    public $reg_assignment_id, $reg_number, $reg_scope, $reg_type;
    
    // Residencia
    public $res_program_name, $res_current_year, $res_current_unit_id, $res_end_date;
    
    // Jefatura
    public $boss_service_id;
    
    // Rol HSI
    public $role_id;
    
    // Unidad HSI
    public $unit_id;
    public bool $unit_responsible = false;
    
    // Documento
    public $doc_type_id, $doc_other_type;
    public $doc_file; 

    // Credenciales HSI
    public $hsi_person_id, $hsi_user_id, $hsi_user;

    public function mount(Agent $agent)
    {
        $this->agent = $agent;
        $this->refreshAgentData();
    }

    public function refreshAgentData()
    {
        $this->agent->load([
            'service',
            'agentProfessions.profession',
            'agentProfessions.specialty',
            'agentProfessions.registrations',
            'documents.type',
            'hsiRoles.documentTypes',
            'residencies.currentUnit',
            'serviceBosses.service',
            'hierarchicalUnits'
        ]);
    }

    // ==========================================
    // LÓGICA: EDICIÓN DATOS PERSONALES
    // ==========================================
    public function openEditModal()
    {
        $this->resetValidation();
        $this->edit_first_name = $this->agent->first_name;
        $this->edit_second_first_name = $this->agent->second_first_name;
        $this->edit_last_name = $this->agent->last_name;
        $this->edit_second_last_name = $this->agent->second_last_name;
        $this->edit_dni = $this->agent->dni;
        $this->edit_gender = is_object($this->agent->gender) ? $this->agent->gender->value : $this->agent->gender;
        $this->edit_phone = $this->agent->phone;
        $this->edit_email = $this->agent->email;
        $this->edit_status = is_object($this->agent->status) ? $this->agent->status->value : $this->agent->status;
        $this->edit_service_id = $this->agent->service_id;
        
        $this->showEditModal = true;
    }

    public function updateAgent()
    {
        $this->validate([
            'edit_first_name'        => 'required|string|max:255',
            'edit_second_first_name' => 'nullable|string|max:255',
            'edit_last_name'         => 'required|string|max:255',
            'edit_second_last_name'  => 'nullable|string|max:255',
            'edit_dni'               => 'required|numeric',
            'edit_gender'            => 'required|in:masculino,femenino,x',
            'edit_phone'             => 'required|string|max:255',
            'edit_email'             => 'required|email|max:255',
            'edit_status'            => 'required|in:activo,inactivo,pendiente',
            'edit_service_id'        => 'nullable|exists:services,id',
        ]);

        $this->agent->update([
            'first_name'        => $this->edit_first_name,
            'second_first_name' => $this->edit_second_first_name,
            'last_name'         => $this->edit_last_name,
            'second_last_name'  => $this->edit_second_last_name,
            'dni'               => $this->edit_dni,
            'gender'            => $this->edit_gender,
            'phone'             => $this->edit_phone,
            'email'             => $this->edit_email,
            'status'            => $this->edit_status,
            'service_id'        => $this->edit_service_id ?: null,
        ]);

        $this->showEditModal = false;
        $this->refreshAgentData();
    }

    // ==========================================
    // LÓGICA: CREDENCIALES HSI
    // ==========================================
    public function openHsiModal()
    {
        $this->resetValidation();
        $this->hsi_person_id = $this->agent->person_id;
        $this->hsi_user_id = $this->agent->user_id;
        $this->hsi_user = $this->agent->user;
        $this->showHsiModal = true;
    }

    public function saveHsiData()
    {
        $this->validate([
            'hsi_person_id' => 'nullable|integer',
            'hsi_user_id'   => 'nullable|integer',
            'hsi_user'      => 'nullable|string|max:255',
        ]);

        $this->agent->update([
            'person_id' => $this->hsi_person_id ?: null,
            'user_id'   => $this->hsi_user_id ?: null,
            'user'      => $this->hsi_user ?: null,
        ]);

        $this->showHsiModal = false;
        $this->refreshAgentData();
    }

    // ==========================================
    // LÓGICA: PROFESIONES
    // ==========================================
    public function saveProfession()
    {
        $this->validate([
            'prof_profession_id' => 'required|exists:occupations,id',
            'prof_specialty_id'  => 'nullable|exists:specialities,id',
        ]);

        AgentProfessionSpecialty::create([
            'agent_id'      => $this->agent->id,
            'profession_id' => $this->prof_profession_id,
            'specialty_id'  => $this->prof_specialty_id ?: null,
        ]);

        $this->reset(['prof_profession_id', 'prof_specialty_id', 'showProfModal']);
        $this->refreshAgentData();
    }

    public function deleteProfession($id)
    {
        AgentProfessionSpecialty::find($id)?->delete();
        $this->refreshAgentData();
    }

    // ==========================================
    // LÓGICA: MATRÍCULAS
    // ==========================================
    public function openRegistrationModal($assignment_id)
    {
        $this->resetValidation();
        $this->reset(['reg_number', 'reg_scope', 'reg_type']);
        $this->reg_assignment_id = $assignment_id;
        $this->showRegModal = true;
    }

    public function saveRegistration()
    {
        $this->validate([
            'reg_assignment_id' => 'required|exists:agent_profession_specialty,id',
            'reg_number'        => 'required|string|max:255',
            'reg_scope'         => 'required|string',
            'reg_type'          => 'required|string',
        ]);

        Registration::create([
            'assignment_id' => $this->reg_assignment_id,
            'number'        => $this->reg_number,
            'scope'         => $this->reg_scope,
            'type'          => $this->reg_type,
        ]);

        $this->reset(['reg_assignment_id', 'reg_number', 'reg_scope', 'reg_type', 'showRegModal']);
        $this->refreshAgentData();
    }

    public function deleteRegistration($id)
    {
        Registration::find($id)?->delete();
        $this->refreshAgentData();
    }

    // ==========================================
    // LÓGICA: RESIDENCIAS
    // ==========================================
    public function saveResidency()
    {
        $this->validate([
            'res_program_name'    => 'required|string|max:255',
            'res_current_year'    => 'required|string|max:255',
            'res_current_unit_id' => 'nullable|exists:hierarchical_units,id',
            'res_end_date'        => 'nullable|date',
        ]);

        Residency::create([
            'agent_id'        => $this->agent->id,
            'program_name'    => $this->res_program_name,
            'current_year'    => $this->res_current_year,
            'current_unit_id' => $this->res_current_unit_id ?: null,
            'end_date'        => $this->res_end_date ?: null,
        ]);

        $this->reset(['res_program_name', 'res_current_year', 'res_current_unit_id', 'res_end_date', 'showResModal']);
        $this->refreshAgentData();
    }

    public function deleteResidency($id)
    {
        Residency::find($id)?->delete();
        $this->refreshAgentData();
    }

    // ==========================================
    // LÓGICA: JEFATURAS
    // ==========================================
    public function saveServiceBoss()
    {
        $this->validate(['boss_service_id' => 'required|exists:services,id']);

        ServiceBoss::firstOrCreate([
            'agent_id'   => $this->agent->id,
            'service_id' => $this->boss_service_id,
        ]);

        $this->reset(['boss_service_id', 'showBossModal']);
        $this->refreshAgentData();
    }

    public function deleteServiceBoss($id)
    {
        ServiceBoss::find($id)?->delete();
        $this->refreshAgentData();
    }

    // ==========================================
    // LÓGICA: ROLES HSI
    // ==========================================
    public function saveRole()
    {
        $this->validate(['role_id' => 'required|exists:hsi_roles,id']);

        HsiRoleAgent::firstOrCreate([
            'agent_id'    => $this->agent->id,
            'hsi_role_id' => $this->role_id,
        ]);

        $this->reset(['role_id', 'showRoleModal']);
        $this->refreshAgentData();
    }

    public function deleteRole($id)
    {
        HsiRoleAgent::where('agent_id', $this->agent->id)->where('hsi_role_id', $id)->delete();
        $this->refreshAgentData();
    }

    // ==========================================
    // LÓGICA: UNIDADES JERÁRQUICAS
    // ==========================================
    public function saveUnit()
    {
        $this->validate(['unit_id' => 'required|exists:hierarchical_units,id']);

        $this->agent->hierarchicalUnits()->syncWithoutDetaching([
            $this->unit_id => ['responsible' => $this->unit_responsible]
        ]);

        $this->reset(['unit_id', 'unit_responsible', 'showUnitModal']);
        $this->refreshAgentData();
    }

    public function deleteUnit($id)
    {
        $this->agent->hierarchicalUnits()->detach($id);
        $this->refreshAgentData();
    }

    // ==========================================
    // LÓGICA: DOCUMENTOS
    // ==========================================
    public function saveDocument()
    {
        $this->validate([
            'doc_type_id' => 'required|exists:document_types,id',
            'doc_file'    => 'required|file|max:5120',
        ]);

        $path = $this->doc_file->store('agent_documents', 'public');

        AgentDocument::create([
            'agent_id'   => $this->agent->id,
            'type_id'    => $this->doc_type_id,
            'other_type' => $this->doc_other_type,
            'path'       => $path,
        ]);

        $this->reset(['doc_type_id', 'doc_other_type', 'doc_file', 'showDocModal']);
        $this->refreshAgentData();
    }

    public function deleteDocument($id)
    {
        $doc = AgentDocument::find($id);
        if ($doc) {
            $doc->delete();
        }
        $this->refreshAgentData();
    }

    public function render()
    {
        $mandatoryTypes = collect();
        foreach ($this->agent->hsiRoles as $role) {
            foreach ($role->documentTypes as $type) {
                if ($type->pivot->is_mandatory) {
                    $mandatoryTypes->push($type);
                }
            }
        }
        $mandatoryTypes = $mandatoryTypes->unique('id');

        $uploadedDocs = $this->agent->documents;
        $uploadedTypeIds = $uploadedDocs->pluck('type_id')->toArray();

        $missingMandatoryTypes = $mandatoryTypes->whereNotIn('id', $uploadedTypeIds);
        $uploadedMandatoryDocs = $uploadedDocs->whereIn('type_id', $mandatoryTypes->pluck('id')->toArray());
        $historicalDocs = $uploadedDocs->whereNotIn('type_id', $mandatoryTypes->pluck('id')->toArray());

        return view('livewire.agents.agent-dashboard', [
            'occupations'       => Occupation::orderBy('name')->get(),
            'specialities'      => Speciality::orderBy('name')->get(),
            'services'          => Service::orderBy('name')->get(),
            'hierarchicalUnits' => HierarchicalUnit::orderBy('alias')->get(),
            'hsiRoles'          => HsiRole::orderBy('name')->get(),
            'documentTypes'     => DocumentType::orderBy('name')->get(),
            'registrationScopes'=> RegistrationScope::cases(),
            'registrationTypes' => RegistrationType::cases(),
            'genders'           => AgentGender::cases(),
            'statuses'          => AgentStatus::cases(),
            
            'missingMandatoryTypes' => $missingMandatoryTypes,
            'uploadedMandatoryDocs' => $uploadedMandatoryDocs,
            'historicalDocs'        => $historicalDocs,
        ]);
    }
}