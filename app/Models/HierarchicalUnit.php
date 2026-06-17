<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAllModelChanges;

class HierarchicalUnit extends Model
{
    use SoftDeletes, LogsAllModelChanges;

    // Deshabilitamos el autoincremento para forzar el ID exacto de HSI
    // public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'institution_id',
        'type_id',
        'alias',
        'hierarchical_unit_id_to_report',
        'closest_service_id',
        'clinical_specialty_id',
        'created_by',
        'updated_by',
    ];

    public function type()
    {
        return $this->belongsTo(HierarchicalUnitType::class, 'type_id');
    }

    public function reportingUnit()
    {
        return $this->belongsTo(HierarchicalUnit::class, 'hierarchical_unit_id_to_report');
    }

    public function closestService()
    {
        return $this->belongsTo(HierarchicalUnit::class, 'closest_service_id');
    }

    public function specialty()
    {
        // Recordamos apuntar a la clase Speciality con 'i'
        return $this->belongsTo(Speciality::class, 'clinical_specialty_id');
    }

    /**
     * Unidades PADRE (por encima en la jerarquía)
     */
    public function parents()
    {
        return $this->belongsToMany(
            HierarchicalUnit::class,
            'hierarchical_unit_relations',
            'hierarchical_unit_child_id',
            'hierarchical_unit_parent_id'
        )
        ->withTimestamps()
        ->whereNull('hierarchical_unit_relations.deleted_at');
    }

    /**
     * Unidades HIJAS (por debajo en la jerarquía)
     */
    public function children()
    {
        return $this->belongsToMany(
            HierarchicalUnit::class,
            'hierarchical_unit_relations',
            'hierarchical_unit_parent_id',
            'hierarchical_unit_child_id'
        )
        ->withTimestamps()
        ->whereNull('hierarchical_unit_relations.deleted_at');
    }

    /**
     * Agentes asignados a esta unidad (El staff)
     * ESTE ES EL MÉTODO CORREGIDO QUE REEMPLAZA A users()
     */
    public function agents()
    {
        return $this->belongsToMany(Agent::class, 'agent_hierarchical_unit', 'hierarchical_unit_id', 'agent_id')
            ->withPivot('id', 'responsible', 'created_by', 'updated_by')
            ->withTimestamps()
            ->whereNull('agent_hierarchical_unit.deleted_at');
    }
}