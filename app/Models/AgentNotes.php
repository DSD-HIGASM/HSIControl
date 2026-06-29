<?php

namespace App\Models;

use App\Traits\LogsAllModelChanges;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AgentNotes extends Model
{
    use LogsAllModelChanges, SoftDeletes;

    protected $fillable = [
        'agent_id',
        'title',
        'content',
    ];

    /**
     * El Agente físico.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
