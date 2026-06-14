<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAllModelChanges;

class AgentDocument extends Model
{
    use LogsAllModelChanges, SoftDeletes;

    protected $fillable = [
        'agent_id',
        'type_id',
        'other_type',
        'path',
    ];

    /**
     * El Agente físico.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * El tipo de documento vinculado.
     */
    public function type()
    {
        return $this->belongsTo(DocumentType::class);
    }
}
