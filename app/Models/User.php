<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\LogsAllModelChanges;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['dni', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    // Acá se inyecta HasRoles, que es el que crea la relación "permissions()"
    use HasFactory, Notifiable, SoftDeletes, HasRoles, LogsAllModelChanges, HasApiTokens;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'dni' => 'integer',
        ];
    }

    /**
     * El agente físico (RRHH) asociado a esta credencial de acceso.
     */
    public function agent()
    {
        return $this->hasOne(Agent::class, 'dni', 'dni');
    }
}