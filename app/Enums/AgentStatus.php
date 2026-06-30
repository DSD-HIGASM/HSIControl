<?php

namespace App\Enums;

enum AgentStatus: string
{
    case INACTIVO = 'inactivo';
    case ACTIVO = 'activo';
    case PENDIENTE = 'pendiente';

    public function color(): string
    {
        return match($this) {
            self::INACTIVO => 'bg-gray-100 text-gray-500 ring-gray-500/10 border-gray-300',
            self::ACTIVO => 'bg-green-50 text-green-700 ring-green-600/20 border-green-200',
            self::PENDIENTE => 'bg-amber-100 text-amber-700 ring-amber-600/20 border-amber-200',
        };
    }

    public function label(): string
    {
        return match($this) {
            self::INACTIVO => 'INACTIVO',
            self::ACTIVO => 'ACTIVO',
            self::PENDIENTE => 'PENDIENTE',
        };
    }

    public function priority(): int
    {
        return match($this) {
            self::PENDIENTE => 1,
            self::ACTIVO => 2,
            self::INACTIVO => 3,
        };
    }
}
