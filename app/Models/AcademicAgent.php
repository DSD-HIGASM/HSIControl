<?php

namespace App\Models;

use App\Traits\LogsAllModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicAgent extends Model
{
    use HasFactory, LogsAllModelChanges, SoftDeletes;

    protected $fillable = [
        'agent_id',
        'anexo_document_id',
        'unlp_email',
        'personal_email',
        'facultad',
        'carrera_catedra',
        'materia',
        'cargo_docente',
        'legajo_unlp',
        'anexo_version',
        'anexo_signed_at',
        'autoridad_firmante_nombre',
        'autoridad_firmante_cargo',
        'hsi_status',
        'hsi_created_at',
        'hsi_username',
        'expires_at',
        'expiration_warning_sent',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'anexo_signed_at' => 'date',
            'expires_at' => 'date',
            'hsi_created_at' => 'datetime',
            'expiration_warning_sent' => 'boolean',
        ];
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function anexoDocument()
    {
        return $this->belongsTo(AgentDocument::class, 'anexo_document_id');
    }

    public function getHsiStatusBadgeAttribute(): array
    {
        return match ($this->hsi_status) {
            'pendiente_alta' => [
                'label' => 'PENDIENTE ALTA HSI',
                'color' => 'bg-amber-50 text-amber-700 ring-amber-600/20 border-amber-200',
            ],
            'activo' => [
                'label' => 'ACTIVO (VIGENTE)',
                'color' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 border-emerald-200',
            ],
            'pendiente_renovacion' => [
                'label' => 'POR VENCER',
                'color' => 'bg-orange-50 text-orange-700 ring-orange-600/20 border-orange-200',
            ],
            'vencido' => [
                'label' => 'VENCIDO / PENDIENTE BAJA',
                'color' => 'bg-rose-50 text-brand-pink ring-rose-600/20 border-rose-200',
            ],
            'revocado' => [
                'label' => 'REVOCADO',
                'color' => 'bg-gray-100 text-gray-700 ring-gray-600/20 border-gray-300',
            ],
            default => [
                'label' => strtoupper($this->hsi_status),
                'color' => 'bg-gray-50 text-gray-600 ring-gray-500/20 border-gray-200',
            ]
        };
    }
}