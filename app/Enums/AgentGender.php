<?php

namespace App\Enums;

enum AgentGender: string
{
    case MASCULINO = 'masculino';
    case FEMENINO = 'femenino';
    case X = 'x';
    case PENDIENTE = 'pendiente';

    public static function selectableCases(): array
    {
        // Devuelve todos los casos excepto el PENDIENTE
        return array_filter(self::cases(), fn($case) => $case !== self::PENDIENTE);
    }

    public function codedGender(): int
    {
        return match($this) {
            self::MASCULINO => 2,
            self::FEMENINO => 1,
            self::X => 3
        };
    }
}
