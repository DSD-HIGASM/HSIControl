<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAllModelChanges;

class DocumentType extends Model
{
    use SoftDeletes, LogsAllModelChanges;
    
    protected $fillable = [
        'name',
    ];

    /**
     * Los roles HSI que requieren este tipo de documento.
     */
    public function hsiRoles()
    {
        return $this->belongsToMany(HsiRole::class, 'document_type_hsi_role')
                    ->withPivot('is_mandatory')
                    ->withTimestamps();
    }
}