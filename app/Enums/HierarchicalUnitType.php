<?php

namespace App\Enums;

enum HierarchicalUnitType: int
{
    case DIRECCION = 1;
    case UNIDAD_DE_DIAGNOSTICO_Y_TRATAMIENTO = 2;
    case UNIDAD_INTERNACION = 3;
    case UNIDAD_DE_CONSULTA = 4;
    case UNIDAD_DE_ENFERMERÍA = 5;
    case JEFATURA_DE_SALA = 6;
    case DEPARTAMENTO = 7;
    case SERVICIO = 8;

    public function label(): string
    {
        return match ($this) {
            self::DIRECCION => 'Dirección',
            self::UNIDAD_DE_DIAGNOSTICO_Y_TRATAMIENTO => 'Unidad de diagnóstico y tratamiento',
            self::UNIDAD_INTERNACION => 'Unidad de internación',
            self::UNIDAD_DE_CONSULTA => 'Unidad de consulta',
            self::UNIDAD_DE_ENFERMERÍA => 'Unidad de enfermería',
            self::JEFATURA_DE_SALA => 'Jefatura de sala',
            self::DEPARTAMENTO => 'Departamento',
            self::SERVICIO => 'Servicio',
        };
    }

    public function isServicio(): bool
    {
        return $this === self::SERVICIO;
    }
}
