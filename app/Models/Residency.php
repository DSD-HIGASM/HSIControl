<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAllModelChanges;

class Residency extends Model
{
    use SoftDeletes, LogsAllModelChanges;

    protected $fillable = [
        'agent_id',
        'program_name',
        'current_year',
        'current_unit_id',
        'end_date',
    ];

    /**
     * Casteo de atributos.
     */
    protected function casts(): array
    {
        return [
            'end_date' => 'date',
        ];
    }

    /**
     * El Agente físico al que pertenece esta residencia.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * La Unidad Jerárquica física de HSI donde está rotando actualmente.
     */
    public function currentUnit()
    {
        // Se especifica 'current_unit_id' explícitamente para enlazar con la tabla hierarchical_units
        return $this->belongsTo(HierarchicalUnit::class, 'current_unit_id');
    }
}