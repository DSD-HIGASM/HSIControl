<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Le preguntamos a Laravel qué motor de Base de Datos estamos usando
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            // Solución segura para MySQL (Tu caso)
            Schema::disableForeignKeyConstraints();
            DB::statement('ALTER TABLE hierarchical_units MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            Schema::enableForeignKeyConstraints();
        } elseif ($driver === 'sqlite') {
            // SQLite no soporta el comando MODIFY de forma nativa. 
            // Como los usuarios nuevos ya tienen la tabla bien creada desde el inicio,
            // simplemente omitimos esta acción en SQLite para evitar que la consola explote con errores de sintaxis.
            // (La tabla de ellos ya es autoincremental de nacimiento).
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            Schema::disableForeignKeyConstraints();
            DB::statement('ALTER TABLE hierarchical_units MODIFY id BIGINT UNSIGNED NOT NULL');
            Schema::enableForeignKeyConstraints();
        }
    }
};