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
        // Limpiar la caché de permisos de Spatie (Obligatorio antes de operar)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Catálogo maestro de permisos del sistema
        $permisosMaestros = [
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
            'editar.documentos',
            'ver.unidades_jerarquicas',
            'gestionar.unidades_jerarquicas',
            'gestionar.miky',
        ];

        // 1. Idempotencia en lote: Consultar cuáles ya existen bajo el guard 'web'
        $permisosExistentes = Permission::where('guard_name', 'web')
            ->whereIn('name', $permisosMaestros)
            ->pluck('name')
            ->toArray();

        // Filtrar el array maestro para quedarnos solo con los que NO están en la base de datos
        $permisosFaltantes = array_diff($permisosMaestros, $permisosExistentes);

        // Si hay permisos nuevos, los preparamos e insertamos masivamente
        if (!empty($permisosFaltantes)) {
            $payloadInsert = array_map(function ($nombrePermiso) {
                return [
                    'name' => $nombrePermiso,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $permisosFaltantes);

            // Se realiza un único INSERT masivo
            Permission::insert($payloadInsert);
        }

        // 2. Crear o verificar el Rol de Super Administrador
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Administrador',
            'guard_name' => 'web'
        ]);

        // 3. Sincronizar de forma segura todos los permisos asignados al rol
        // syncPermissions se encarga de asociar los nuevos sin duplicar ni romper relaciones existentes
        $superAdminRole->syncPermissions(Permission::all());
    }
}
