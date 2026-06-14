<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAllModelChanges;

class Occupation extends Model
{
    // Corregido el error de sintaxis: 'Use softDeletes' a 'use SoftDeletes'
    use SoftDeletes, LogsAllModelChanges; 
    
    protected $fillable = [
        'name',
    ];

    /**
     * Los agentes que poseen esta ocupación/profesión.
     */
    public function agents()
    {
        return $this->belongsToMany(
            Agent::class, 
            'agent_profession_specialty',
            'profession_id', // Le avisamos que en la pivot se llama profession_id
            'agent_id'
        )->withTimestamps();
    }
}