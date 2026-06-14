<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAllModelChanges;

class Service extends Model
{
    Use softDeletes, LogsAllModelChanges;
    protected $fillable = [
        'name',
    ];    
}
