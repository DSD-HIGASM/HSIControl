<?php

namespace App\Traits;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions; // <-- EL NAMESPACE CORRECTO PARA LA V5

trait LogsAllModelChanges
{
    use LogsActivity;

    /**
     * Define las opciones de log de manera dinámica para cualquier modelo.
     */
    public function getActivitylogOptions(): LogOptions
    {
        // Campos globales altamente sensibles
        $sensitiveFields = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

        if (property_exists($this, 'dontLogFields')) {
            $sensitiveFields = array_merge($sensitiveFields, $this->dontLogFields);
        }

        return LogOptions::defaults()
            ->logAll()
            ->logExcept($sensitiveFields)
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}