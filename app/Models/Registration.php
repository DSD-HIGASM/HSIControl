<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAllModelChanges;
use App\Enums\RegistrationScope;
use App\Enums\RegistrationType;

class Registration extends Model
{
    use SoftDeletes, LogsAllModelChanges;

    protected $fillable = [
        'assignment_id',
        'number',
        'scope',
        'type',
    ];

    /**
     * Le indicamos a Laravel que parsee los strings de la base de datos como Enums.
     */
    protected function casts(): array
    {
        return [
            'scope' => RegistrationScope::class,
            'type' => RegistrationType::class,
        ];
    }

    /**
     * La asignación (Agente + Profesión + Especialidad) a la que pertenece esta matrícula.
     */
    public function assignment()
    {
        return $this->belongsTo(AgentProfessionSpecialty::class, 'assignment_id');
    }
}