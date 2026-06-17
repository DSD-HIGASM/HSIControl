<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Correr los catálogos base primero
        $this->call([
            PermisosSeeder::class,
            UUJJSeeder::class,
        ]);

        // 2. Crear el Usuario Administrador por defecto para el primer login en cualquier hospital
        // Documentar esto en el README.md del repositorio para los instaladores
        $userAdmin = User::firstOrCreate(
            ['dni' => '12345678'],
            ['password' => Hash::make('password')] 
        );

        // 3. Asignarle el rol maestro creado en el PermisosSeeder
        if (!$userAdmin->hasRole('Super Administrador')) {
            $userAdmin->assignRole('Super Administrador');
        }
    }
}