<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAllModelChanges;

class HsiRoleAgent extends Model
{
    use LogsAllModelChanges, SoftDeletes;

    protected $fillable = [
        'hsi_role_id',
        'agent_id',
    ];

    public function hsiRole()
    {
        return $this->belongsTo(HsiRole::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
