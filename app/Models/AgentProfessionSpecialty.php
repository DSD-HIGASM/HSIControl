<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAllModelChanges;

class AgentProfessionSpecialty extends Model
{
    use LogsAllModelChanges, SoftDeletes;

    /**
     * Forzamos el nombre exacto de la tabla para evitar errores de pluralización de Laravel.
     */
    protected $table = 'agent_profession_specialty';

    protected $fillable = [
        'agent_id',
        'profession_id',
        'specialty_id',
    ];

    /**
     * El Agente físico.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * La Profesión vinculada (Ej: Médico, Enfermero).
     */
    public function profession()
    {
        return $this->belongsTo(Occupation::class, 'profession_id'); 
    }

    /**
     * La Especialidad vinculada (Ej: Cardiología, Pediatría).
     */
    public function specialty()
    {
        return $this->belongsTo(Speciality::class, 'specialty_id');
    }

    /**
     * Matrículas vinculadas a esta asignación específica.
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class, 'assignment_id');
    }
}