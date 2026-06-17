<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermisosSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar la caché de permisos de Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Catálogo maestro de permisos del sistema
        $permisos = [
            'configurar.documentos',
            'configurar.ocupaciones',
            'configurar.roles',
            'configurar.roles.hsi',
            'configurar.profesiones',
            'configurar.especialidades',
            'configurar.usuarios',
            'configurar.servicios',
            'gestionar.usuarios',
            'gestionar.permisos',
            'ver.logs',
            'crear.agente',
            'editar.informacion',
            'editar.profesiones',
            'editar.accesos',
            'editar.documentos'
        ];

        // 1. Crear o verificar permisos
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web'
            ]);
        }

        // 2. Crear el Rol de Super Administrador (si no existe)
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Administrador',
            'guard_name' => 'web'
        ]);

        // 3. Asignarle todos los permisos existentes al Super Admin
        $superAdminRole->syncPermissions(Permission::all());
    }
}