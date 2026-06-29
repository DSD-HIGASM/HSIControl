<?php

namespace App\Models;

use App\Enums\AgentGender;
use App\Enums\AgentStatus;
use App\Traits\LogsAllModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasFactory, LogsAllModelChanges, SoftDeletes;

    protected $fillable = [
        'last_name',
        'second_last_name',
        'first_name',
        'second_first_name',
        'dni',
        'gender',
        'email',
        'phone',
        'status',
        'person_id',
        'user_id',
        'user',
        'service_id',
    ];

    protected function casts(): array
    {
        return [
            'gender' => AgentGender::class,
            'dni' => 'integer',
            'status' => AgentStatus::class,
        ];
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function agentProfessions()
    {
        return $this->hasMany(AgentProfessionSpecialty::class, 'agent_id');
    }

    public function documents()
    {
        return $this->hasMany(AgentDocument::class, 'agent_id');
    }

    public function hsiRoles()
    {
        return $this->belongsToMany(HsiRole::class, 'hsi_role_agents', 'agent_id', 'hsi_role_id');
    }

    // --- ACCESORS (Atributos Dinámicos) ---

    /**
     * Calcula dinámicamente si el agente entregó todos los documentos obligatorios
     * y devuelve el listado detallado de faltantes y entregados.
     */
    public function getDocumentStatusAttribute(): array
    {
        if ($this->hsiRoles->isEmpty()) {
            return [
                'state' => 'N/A',
                'missing' => 0,
                'uploaded_docs' => [],
                'missing_docs' => [],
                'color' => 'bg-gray-50 text-gray-500 ring-gray-500/20',
            ];
        }

        $mandatoryDocs = [];

        foreach ($this->hsiRoles as $role) {
            foreach ($role->documentTypes as $docType) {
                if ($docType->pivot->is_mandatory) {
                    // Guardamos el ID como llave y el Nombre del documento como valor
                    $mandatoryDocs[$docType->id] = $docType->name;
                }
            }
        }

        if (empty($mandatoryDocs)) {
            return [
                'state' => 'COMPLETO',
                'missing' => 0,
                'uploaded_docs' => [],
                'missing_docs' => [],
                'color' => 'bg-green-50 text-green-700 ring-green-600/20',
            ];
        }

        // ¿Qué subió el agente hasta ahora?
        $uploadedTypeIds = $this->documents->pluck('type_id')->unique()->toArray();

        $uploadedDocs = [];
        $missingDocs = [];

        // Clasificamos uno por uno
        foreach ($mandatoryDocs as $id => $name) {
            if (in_array($id, $uploadedTypeIds)) {
                $uploadedDocs[] = $name;
            } else {
                $missingDocs[] = $name;
            }
        }

        $missingCount = count($missingDocs);

        if ($missingCount > 0) {
            return [
                'state' => 'FALTANTES',
                'missing' => $missingCount,
                'uploaded_docs' => $uploadedDocs,
                'missing_docs' => $missingDocs,
                'color' => 'bg-red-50 text-red-700 ring-red-600/20',
            ];
        }

        return [
            'state' => 'COMPLETO',
            'missing' => 0,
            'uploaded_docs' => $uploadedDocs,
            'missing_docs' => [],
            'color' => 'bg-green-50 text-green-700 ring-green-600/20',
        ];
    }

    /**
     * Determina el estado de vinculación con la tabla de usuarios.
     * Ya no se bloquea por falta de documentación física.
     */
    public function getHsiAccessStatusAttribute(): array
{
    // Si es INACTIVO o PENDIENTE, usamos directamente los valores de su Enum
    if ($this->status !== AgentStatus::ACTIVO) {
        return [
            'value' => $this->status->value,
            'label' => $this->status->label(),
            'color' => $this->status->color(),
        ];
    }

    // Si está ACTIVO, entonces evaluamos si tiene o no usuario vinculado
    if (empty($this->user_id)) {
        return [
            'value' => $this->status->value,
            'label' => 'SIN USUARIO', 
            'color' => 'bg-amber-50 text-amber-700 ring-amber-600/20'
        ];
    }

    return [
        'value' => $this->status->value,
        'label' => 'CON USUARIO', 
        'color' => 'bg-green-50 text-green-700 ring-green-600/20'
    ];
}


    /**
     * Las residencias que está cursando o cursó el agente.
     */
    public function residencies()
    {
        return $this->hasMany(Residency::class, 'agent_id');
    }

    /**
     * Las jefaturas de servicio que tiene asignadas el agente.
     */
    public function serviceBosses()
    {
        return $this->hasMany(ServiceBoss::class, 'agent_id');
    }

    /**
     * Las unidades jerárquicas de HSI a las que tiene acceso.
     */
    public function hierarchicalUnits()
    {
        return $this->belongsToMany(HierarchicalUnit::class, 'agent_hierarchical_unit', 'agent_id', 'hierarchical_unit_id')
            ->withPivot('responsible', 'created_by', 'updated_by')
            ->withTimestamps();
    }

    /**
     * Obtiene las notas asociadas al agente.
     */
    public function notes()
    {
        return $this->hasMany(AgentNotes::class);
    }
}
