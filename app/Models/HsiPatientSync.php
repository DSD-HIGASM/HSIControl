<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HsiPatientSync extends Model
{
    protected $table = 'hsi_patient_data_sync';
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'patient_id',
        'person_id',
        'dni',
        'completed_data',
        'personal_info',
        'user_data',
        'roles_data',
        'user_id',
        'is_global',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_data' => 'array',
            'personal_info'  => 'array',
            'user_data'      => 'array',
            'roles_data'     => 'array',
            'is_global'      => 'boolean',
            'processed_at'   => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Relación con el usuario que inició la importación desde la extensión.
     */
    public function importedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}