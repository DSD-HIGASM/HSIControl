<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAllModelChanges;

class ServiceBoss extends Model
{
    use LogsAllModelChanges, SoftDeletes;
    protected $fillable = [
        'service_id',
        'agent_id',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
