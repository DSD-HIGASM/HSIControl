<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAllModelChanges;

class Speciality extends Model
{
    use SoftDeletes, LogsAllModelChanges;
    
    protected $fillable = [
        'name',
    ];

    /**
     * Los agentes que poseen esta especialidad.
     */
    public function agents()
    {
        return $this->belongsToMany(
            Agent::class, 
            'agent_profession_specialty',
            'specialty_id',
            'agent_id'
        )->withTimestamps();
    }
}