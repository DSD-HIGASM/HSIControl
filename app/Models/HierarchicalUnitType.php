<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAllModelChanges;

class HierarchicalUnitType extends Model
{
    use SoftDeletes, LogsAllModelChanges;

    protected $fillable = [
        'description',
    ];

    /**
     * Las unidades jerárquicas que pertenecen a este tipo.
     */
    public function hierarchicalUnits()
    {
        return $this->hasMany(HierarchicalUnit::class, 'type_id');
    }
}