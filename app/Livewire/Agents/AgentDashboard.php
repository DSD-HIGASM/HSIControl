<?php

namespace App\Livewire\Agents;

use App\Enums\AgentGender;
use App\Enums\AgentStatus;
use App\Enums\RegistrationScope;
use App\Enums\RegistrationType;
use App\Mail\AcademicAgentActivatedMail;
use App\Mail\AcademicAgentDeactivatedMail;
use App\Mail\AcademicAgentExpiringMail;
use App\Mail\AcademicAgentReactivatedMail;
use App\Models\AcademicAgent;
use App\Models\Agent;
use App\Models\AgentDocument;
use App\Models\AgentNotes;
use App\Models\AgentProfessionSpecialty;
use App\Models\DocumentType;
use App\Models\HierarchicalUnit;
use App\Models\HsiRole;
use App\Models\HsiRoleAgent;
use App\Models\Occupation;
use App\Models\Registration;
use App\Models\Residency;
use App\Models\Service;
use App\Models\ServiceBoss;
use App\Models\Speciality;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    public bool $showAcademicEditModal = false;

    public bool $showCreateAcademicModal = false;

    public bool $showProfModal = false;

    public bool $showRegModal = false;

    public bool $showResModal = false;

    public bool $showBossModal = false;

    public bool $showRoleModal = false;

    public bool $showUnitModal = false;

    public bool $showDocModal = false;

    public bool $showHsiModal = false;

    public bool $showNoteModal = false;

    public bool $showConfirmModal = false;

    // --- VARIABLES DE CONFIRMACIÓN GENÉRICA ---
    public ?string $confirmingAction = null;

    public ?int $confirmingId = null;

    public string $confirmingMessage = '';

    public bool $showAccessModal = false;

    public array $selected_access_units = [];

    // --- CAMPOS UNIVERSIDAD UNLP ---
    public string $academic_unlp_email = '';

    public string $academic_facultad = 'Facultad de Ciencias Médicas';

    public string $academic_carrera_catedra = '';

    public string $academic_materia = '';

    public string $academic_cargo_docente = 'Profesor Adjunto';

    public string $academic_legajo_unlp = '';

    public string $academic_autoridad_nombre = '';

    public string $academic_autoridad_cargo = 'Decano/a';

    public string $academic_expires_at = '';

    public string $academic_hsi_username = '';

    public string $academic_hsi_link = '';

    // --- VARIABLES DE FORMULARIOS ---

    // Edición de Agente
    public $edit_first_name;

    public $edit_second_first_name;

    public $edit_last_name;

    public $edit_second_last_name;

    public $edit_dni;

    public $edit_gender;

    public $edit_phone;

    public $edit_email;

    public $edit_status;

    public $edit_service_id;

    // Profesión
    public $prof_profession_id;

    public $prof_specialty_id;

    // Matrícula
    public $reg_assignment_id;

    public $reg_number;

    public $reg_scope;

    public $reg_type;

    // Residencia
    public $res_program_name;

    public $res_current_year;

    public $res_current_unit_id;

    public $res_end_date;

    // Jefatura
    public $boss_service_id;

    // Rol HSI
    public $role_id;

    // Unidad HSI
    public $unit_id;

    public bool $unit_responsible = false;

    // Documento
    public $doc_type_id;

    public $doc_other_type;

    public $doc_file;

    // Credenciales HSI
    public $hsi_person_id;

    public $hsi_user_id;

    public $hsi_user;

    // Notas
    public $note_id;

    public $note_title;

    public $note_content;

    public function mount(Agent $agent)
    {
        $this->agent = $agent;
        $this->refreshAgentData();
    }

    public function refreshAgentData()
    {
        $this->agent->load([
            'service',
            'academicProfile',
            'agentProfessions.profession',
            'agentProfessions.specialty',
            'agentProfessions.registrations',
            'documents.type',
            'hsiRoles.documentTypes',
            'residencies.currentUnit',
            'serviceBosses.service',
            'hierarchicalUnits',
            'accessibleHierarchicalUnits',
            'notes',
        ]);
    }

    // ==========================================
    // LÓGICA: PERFIL ACADÉMICO / UNLP
    // ==========================================
    public function openCreateAcademicModal(): void
    {
        $this->resetValidation();
        $this->academic_unlp_email = '';
        $this->academic_facultad = 'Facultad de Ciencias Médicas';
        $this->academic_carrera_catedra = '';
        $this->academic_materia = '';
        $this->academic_cargo_docente = 'Profesor Adjunto';
        $this->academic_legajo_unlp = '';
        $this->academic_autoridad_nombre = '';
        $this->academic_autoridad_cargo = 'Decano/a';
        $this->academic_expires_at = now()->addMonths(6)->format('Y-m-d');
        $this->showCreateAcademicModal = true;
    }

    public function saveAcademicProfile(): void
    {
        $this->validate([
            'academic_unlp_email' => 'required|email',
            'academic_facultad' => 'required|string',
            'academic_carrera_catedra' => 'required|string',
            'academic_materia' => 'required|string',
            'academic_cargo_docente' => 'required|string',
            'academic_autoridad_nombre' => 'required|string',
            'academic_autoridad_cargo' => 'required|string',
            'academic_expires_at' => 'required|date',
        ]);

        AcademicAgent::updateOrCreate(
            ['agent_id' => $this->agent->id],
            [
                'unlp_email' => strtolower($this->academic_unlp_email),
                'personal_email' => strtolower($this->agent->email),
                'facultad' => $this->academic_facultad,
                'carrera_catedra' => $this->academic_carrera_catedra,
                'materia' => $this->academic_materia,
                'cargo_docente' => $this->academic_cargo_docente,
                'legajo_unlp' => $this->academic_legajo_unlp,
                'anexo_version' => '1.0',
                'anexo_signed_at' => now(),
                'autoridad_firmante_nombre' => $this->academic_autoridad_nombre,
                'autoridad_firmante_cargo' => $this->academic_autoridad_cargo,
                'hsi_status' => 'pendiente_alta',
                'expires_at' => $this->academic_expires_at,
            ]
        );

        $this->showCreateAcademicModal = false;
        $this->refreshAgentData();
        $this->tab = 'universidad';
        session()->flash('status', 'Perfil docente registrado correctamente (pendiente de activación).');
    }

    public function notifyAcademicExpiring(): void
    {
        $acad = $this->agent->academicProfile;

        if ($acad) {
            try {
                Mail::to($acad->unlp_email)
                    ->cc($acad->personal_email)
                    ->send(new AcademicAgentExpiringMail($acad));

                Log::info("Mail de Aviso por Vencer UNLP enviado a: {$acad->unlp_email}");
                session()->flash('status', 'Aviso de próximo vencimiento enviado al docente.');
            } catch (\Throwable $e) {
                Log::error("Error enviando Mail por Vencer UNLP a {$acad->unlp_email}: ".$e->getMessage());
                session()->flash('status', 'Ocurrió un error al enviar la notificación de vencimiento.');
            }
        }
    }

    public function openAcademicEditModal(): void
    {
        if ($this->agent->academicProfile) {
            $acad = $this->agent->academicProfile;
            $this->academic_unlp_email = $acad->unlp_email;
            $this->academic_facultad = $acad->facultad;
            $this->academic_carrera_catedra = $acad->carrera_catedra;
            $this->academic_materia = $acad->materia;
            $this->academic_cargo_docente = $acad->cargo_docente;
            $this->academic_legajo_unlp = $acad->legajo_unlp ?? '';
            $this->academic_autoridad_nombre = $acad->autoridad_firmante_nombre;
            $this->academic_autoridad_cargo = $acad->autoridad_firmante_cargo;
            $this->academic_expires_at = $acad->expires_at->format('Y-m-d');
            $this->academic_hsi_username = $acad->hsi_username ?? '';
            $this->showAcademicEditModal = true;
        }
    }

    public function updateAcademicProfile(): void
{
    $this->validate([
        'academic_unlp_email' => 'required|email',
        'academic_facultad' => 'required|string',
        'academic_carrera_catedra' => 'required|string',
        'academic_materia' => 'required|string',
        'academic_cargo_docente' => 'required|string',
        'academic_autoridad_nombre' => 'required|string',
        'academic_autoridad_cargo' => 'required|string',
        'academic_expires_at' => 'required|date',
    ]);

    $acad = $this->agent->academicProfile;
    $acad->update([
        'unlp_email' => strtolower($this->academic_unlp_email),
        'facultad' => $this->academic_facultad,
        'carrera_catedra' => $this->academic_carrera_catedra,
        'materia' => $this->academic_materia,
        'cargo_docente' => $this->academic_cargo_docente,
        'legajo_unlp' => $this->academic_legajo_unlp,
        'autoridad_firmante_nombre' => $this->academic_autoridad_nombre,
        'autoridad_firmante_cargo' => $this->academic_autoridad_cargo,
        'expires_at' => $this->academic_expires_at,
        'expiration_warning_sent' => false, // Reset para permitir el aviso del nuevo ciclo
    ]);

    $this->showAcademicEditModal = false;
    $this->refreshAgentData();
    session()->flash('status', 'Datos de vinculación UNLP actualizados.');
}

    public function saveAcademicHsiCredentials(): void
    {
        if ($this->academic_hsi_link && ! preg_match('~^(?:f|ht)tps?://~i', $this->academic_hsi_link)) {
            $this->academic_hsi_link = 'https://'.$this->academic_hsi_link;
        }

        $this->validate([
            'academic_hsi_link' => 'required|url',
        ], [
            'academic_hsi_link.required' => 'Debés ingresar o pegar el enlace de HSI.',
            'academic_hsi_link.url' => 'El formato del enlace no es válido (ejemplo: https://hsi.gba.gov.ar/...)',
        ]);

        try {
            $acad = $this->agent->academicProfile()->first();

            if (! $acad) {
                session()->flash('error', 'No se encontró el perfil académico asociado a este agente.');

                return;
            }

            // Actualizar perfil académico
            $acad->update([
                'hsi_status' => 'activo',
            ]);

            // Sincronizar el estado general del Agente a ACTIVO
            $this->agent->update([
                'status' => AgentStatus::ACTIVO,
            ]);

            // Enviar notificación por correo
            Mail::to($acad->unlp_email)
                ->cc($acad->personal_email)
                ->send(new AcademicAgentActivatedMail($acad, $this->academic_hsi_link));

            Log::info("Mail de Activación UNLP enviado a: {$acad->unlp_email}");
            session()->flash('status', 'Acceso activado y estado del agente actualizado a ACTIVO.');

            $this->academic_hsi_link = '';
            $this->refreshAgentData();

        } catch (\Throwable $e) {
            Log::error('Error en activación HSI: '.$e->getMessage());
            session()->flash('error', 'Error al procesar la activación: '.$e->getMessage());
        }
    }

    public function revokeAcademicHsiAccess(): void
    {
        $acad = $this->agent->academicProfile;

        if ($acad) {
            // Actualizar perfil académico
            $acad->update(['hsi_status' => 'revocado']);

            // Sincronizar el estado general del Agente a INACTIVO
            $this->agent->update([
                'status' => AgentStatus::INACTIVO,
            ]);

            try {
                Mail::to($acad->unlp_email)
                    ->cc($acad->personal_email)
                    ->send(new AcademicAgentDeactivatedMail($acad));

                Log::info("Mail de Desactivación UNLP enviado a: {$acad->unlp_email}");
                session()->flash('status', 'Acceso revocado y estado del agente actualizado a INACTIVO.');
            } catch (\Throwable $e) {
                Log::error("Error enviando Mail de Desactivación UNLP a {$acad->unlp_email}: ".$e->getMessage());
                session()->flash('status', 'Acceso revocado, pero hubo un problema al enviar la notificación.');
            }

            $this->refreshAgentData();
        }
    }

    public function reactivateAcademicHsiAccess(): void
    {
        if ($this->academic_hsi_link && ! preg_match('~^(?:f|ht)tps?://~i', $this->academic_hsi_link)) {
            $this->academic_hsi_link = 'https://'.$this->academic_hsi_link;
        }

        $this->validate([
            'academic_hsi_link' => 'required|url',
        ], [
            'academic_hsi_link.required' => 'Debés ingresar o pegar el enlace de HSI.',
            'academic_hsi_link.url' => 'El formato del enlace no es válido (ejemplo: https://hsi.gba.gov.ar/...)',
        ]);

        $acad = $this->agent->academicProfile;

        if ($acad) {
            // Actualizar perfil académico
            $acad->update(['hsi_status' => 'activo']);

            // Sincronizar el estado general del Agente a ACTIVO
            $this->agent->update([
                'status' => AgentStatus::ACTIVO,
            ]);

            try {
                Mail::to($acad->unlp_email)
                    ->cc($acad->personal_email)
                    ->send(new AcademicAgentReactivatedMail($acad, $this->academic_hsi_link));

                Log::info("Mail de Reactivación UNLP enviado a: {$acad->unlp_email}");
                session()->flash('status', 'Acceso reactivado y estado del agente actualizado a ACTIVO.');
            } catch (\Throwable $e) {
                Log::error("Error enviando Mail de Reactivación UNLP a {$acad->unlp_email}: ".$e->getMessage());
                session()->flash('status', 'Acceso reactivado, pero falló el envío del correo.');
            }

            $this->academic_hsi_link = '';
            $this->refreshAgentData();
        }
    }

    // ==========================================
    // LÓGICA: CONFIRMACIÓN UNIVERSAL
    // ==========================================
    public function confirmAction($action, $id, $message)
    {
        $this->confirmingAction = $action;
        $this->confirmingId = $id;
        $this->confirmingMessage = $message;
        $this->showConfirmModal = true;
    }

    public function executeAction()
    {
        if ($this->confirmingAction) {
            switch ($this->confirmingAction) {
                case 'revoke_academic':
                    $this->revokeAcademicHsiAccess();
                    break;
                case 'profession':
                    $this->deleteProfession($this->confirmingId);
                    break;
                case 'registration':
                    $this->deleteRegistration($this->confirmingId);
                    break;
                case 'residency':
                    $this->deleteResidency($this->confirmingId);
                    break;
                case 'boss':
                    $this->deleteServiceBoss($this->confirmingId);
                    break;
                case 'role':
                    $this->deleteRole($this->confirmingId);
                    break;
                case 'unit':
                    $this->deleteUnit($this->confirmingId);
                    break;
                case 'document':
                    $this->deleteDocument($this->confirmingId);
                    break;
                case 'note':
                    $this->deleteNote($this->confirmingId);
                    break;
            }
        }
        $this->showConfirmModal = false;
        $this->reset(['confirmingAction', 'confirmingId', 'confirmingMessage']);
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
            'edit_first_name' => 'required|string|max:255',
            'edit_second_first_name' => 'nullable|string|max:255',
            'edit_last_name' => 'required|string|max:255',
            'edit_second_last_name' => 'nullable|string|max:255',
            'edit_dni' => 'required|numeric',
            'edit_gender' => 'required|in:masculino,femenino,x',
            'edit_phone' => 'required|string|max:255',
            'edit_email' => 'required|email|max:255',
            'edit_status' => 'required|in:activo,inactivo,pendiente',
            'edit_service_id' => 'required|exists:services,id',
        ]);

        $this->agent->update([
            'first_name' => $this->edit_first_name,
            'second_first_name' => $this->edit_second_first_name,
            'last_name' => $this->edit_last_name,
            'second_last_name' => $this->edit_second_last_name,
            'dni' => $this->edit_dni,
            'gender' => $this->edit_gender,
            'phone' => $this->edit_phone,
            'email' => $this->edit_email,
            'status' => $this->edit_status,
            'service_id' => $this->edit_service_id ?: null,
        ]);

        $this->showEditModal = false;
        $this->refreshAgentData();
    }

    // ==========================================
    // LÓGICA: NOTAS DEL AGENTE
    // ==========================================
    public function createNote()
    {
        $this->resetValidation();
        $this->reset(['note_id', 'note_title', 'note_content']);
        $this->showNoteModal = true;
    }

    public function editNote($id)
    {
        $this->resetValidation();
        $note = AgentNotes::find($id);

        if ($note) {
            $this->note_id = $note->id;
            $this->note_title = $note->title;
            $this->note_content = $note->content;
            $this->showNoteModal = true;
        }
    }

    public function saveNote()
    {
        $this->validate([
            'note_title' => 'required|string|max:255',
            'note_content' => 'required|string',
        ]);

        AgentNotes::updateOrCreate(
            ['id' => $this->note_id],
            [
                'agent_id' => $this->agent->id,
                'title' => $this->note_title,
                'content' => $this->note_content,
            ]
        );

        $this->reset(['note_id', 'note_title', 'note_content', 'showNoteModal']);
        $this->refreshAgentData();
    }

    public function deleteNote($id)
    {
        AgentNotes::find($id)?->delete();
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
            'hsi_user_id' => 'nullable|integer',
            'hsi_user' => 'nullable|string|max:255',
        ]);

        $this->agent->update([
            'person_id' => $this->hsi_person_id ?: null,
            'user_id' => $this->hsi_user_id ?: null,
            'user' => $this->hsi_user ?: null,
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
            'prof_specialty_id' => 'nullable|exists:specialities,id',
        ]);

        AgentProfessionSpecialty::create([
            'agent_id' => $this->agent->id,
            'profession_id' => $this->prof_profession_id,
            'specialty_id' => $this->prof_specialty_id ?: null,
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

    public function openAccessModal()
    {
        $this->selected_access_units = $this->agent->accessibleHierarchicalUnits
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        $this->showAccessModal = true;
    }

    public function saveAccessUnits()
    {
        $userId = auth()->id();

        $syncData = [];
        foreach ($this->selected_access_units as $unitId) {
            $syncData[$unitId] = [
                'created_by' => $userId,
                'updated_by' => $userId,
            ];
        }

        $this->agent->accessibleHierarchicalUnits()->sync($syncData);

        $this->showAccessModal = false;
        $this->refreshAgentData();

        session()->flash('status', 'Accesos a agendas y unidades jerárquicas actualizados.');
    }

    public function revokeAccessUnit($unitId)
    {
        $this->agent->accessibleHierarchicalUnits()->detach($unitId);
        $this->refreshAgentData();
    }

    public function saveRegistration()
    {
        $this->validate([
            'reg_assignment_id' => 'required|exists:agent_profession_specialty,id',
            'reg_number' => 'required|string|max:255',
            'reg_scope' => 'required|string',
            'reg_type' => 'required|string',
        ]);

        Registration::create([
            'assignment_id' => $this->reg_assignment_id,
            'number' => $this->reg_number,
            'scope' => $this->reg_scope,
            'type' => $this->reg_type,
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
            'res_program_name' => 'required|string|max:255',
            'res_current_year' => 'required|string|max:255',
            'res_current_unit_id' => 'nullable|exists:hierarchical_units,id',
            'res_end_date' => 'nullable|date',
        ]);

        Residency::create([
            'agent_id' => $this->agent->id,
            'program_name' => $this->res_program_name,
            'current_year' => $this->res_current_year,
            'current_unit_id' => $this->res_current_unit_id ?: null,
            'end_date' => $this->res_end_date ?: null,
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
            'agent_id' => $this->agent->id,
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
            'agent_id' => $this->agent->id,
            'hsi_role_id' => $this->role_id,
        ]);

        $this->reset(['role_id', 'showRoleModal']);
        $this->refreshAgentData();
    }

    public function deleteRole($id)
    {
        $this->agent->hsiRoles()->detach($id);
        $this->refreshAgentData();
    }

    // ==========================================
    // LÓGICA: UNIDADES JERÁRQUICAS
    // ==========================================
    public function saveUnit()
    {
        $this->validate(['unit_id' => 'required|exists:hierarchical_units,id']);

        $this->agent->hierarchicalUnits()->syncWithoutDetaching([
            $this->unit_id => ['responsible' => $this->unit_responsible],
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
            'doc_file' => 'required|file|max:100000',
        ]);

        $path = $this->doc_file->store('agent_documents', 'public');

        AgentDocument::create([
            'agent_id' => $this->agent->id,
            'type_id' => $this->doc_type_id,
            'other_type' => $this->doc_other_type,
            'path' => $path,
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

        $notes = $this->agent->notes ?? collect();

        return view('livewire.agents.agent-dashboard', [
            'occupations' => Occupation::orderBy('name')->get(),
            'specialities' => Speciality::orderBy('name')->get(),
            'services' => Service::orderBy('name')->get(),
            'hierarchicalUnits' => HierarchicalUnit::orderBy('alias')->get(),
            'hsiRoles' => HsiRole::orderBy('name')->get(),
            'documentTypes' => DocumentType::orderBy('name')->get(),
            'registrationScopes' => RegistrationScope::cases(),
            'registrationTypes' => RegistrationType::cases(),
            'genders' => AgentGender::selectableCases(),
            'statuses' => AgentStatus::cases(),

            'missingMandatoryTypes' => $missingMandatoryTypes,
            'uploadedMandatoryDocs' => $uploadedMandatoryDocs,
            'historicalDocs' => $historicalDocs,
            'notes' => $notes,
        ]);
    }
}
