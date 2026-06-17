<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// CAMBIA ESTA LÍNEA (Línea 8):
class UUJJSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['description' => 'Sección', 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'Servicio', 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'Departamento', 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'Jefatura de sala', 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'Unidad de enfermería', 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'Unidad de consulta', 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'Unidad de internación', 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'Unidad de diagnóstico y tratamiento', 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'Dirección', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('hierarchical_unit_types')->insert($types);
    }
}
