<?php

namespace App\Models;

use App\Traits\LogsAllModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HsiRole extends Model
{
    use LogsAllModelChanges, SoftDeletes;

    protected $fillable = [
        'name',
    ];

    /**
     * Los tipos de documentos requeridos para este rol.
     */
    public function documentTypes()
    {
        // Pasamos el nombre de la pivote por seguridad y habilitamos los timestamps y campos extra
        return $this->belongsToMany(DocumentType::class, 'document_type_hsi_role')
            ->withPivot('is_mandatory')
            ->withTimestamps();
    }
}
