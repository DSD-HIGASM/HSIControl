<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar la caché de permisos de Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear el catálogo de permisos base del sistema
        $permisosAdmin = [
            'configurar.documentos',
            'configurar.ocupaciones',
            'configurar.especialidades',
            'configurar.roles.hsi',
            'gestionar.usuarios',
            'gestionar.permisos'
        ];

        foreach ($permisosAdmin as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // Crear ÚNICAMENTE el usuario Administrador base para poder iniciar sesión
        // Los datos del legajo (RRHH) se vincularán automáticamente al correr la migración legacy
        $userAdmin = User::firstOrCreate(
            ['dni' => 43255000],
            ['password' => Hash::make('password')]
        );

        $userAdmin->givePermissionTo(Permission::all());
    }
}