<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar la caché de permisos de Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear el catálogo de permisos base del sistema
        $permisos = [
            'configurar.documentos',
            'configurar.roles',
            'configurar.profesiones',
            'configurar.especialidades',
            'configurar.usuarios',
            'configurar.servicios',
            'ver.logs',
            'crear.agente',
            'editar.informacion',
            'editar.profesiones',
            'editar.accesos',
            'editar.documentos'
        ];

        // Cargar los permisos en la base de datos
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web'
            ]);
        }
    }
}
