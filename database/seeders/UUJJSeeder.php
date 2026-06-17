<?php

namespace Database\Seeders;

use App\Models\HierarchicalUnitType;
use Illuminate\Database\Seeder;

class UUJJSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Sección',
            'Servicio',
            'Departamento',
            'Jefatura de sala',
            'Unidad de enfermería',
            'Unidad de consulta',
            'Unidad de internación',
            'Unidad de diagnóstico y tratamiento',
            'Dirección',
        ];

        foreach ($types as $type) {
            HierarchicalUnitType::firstOrCreate([
                'description' => $type
            ]);
        }
    }
}