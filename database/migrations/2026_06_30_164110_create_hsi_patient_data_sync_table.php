<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hsi_patient_data_sync', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Índices clave para búsqueda rápida en tu tabla de pendientes
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('person_id')->nullable();
            $table->string('dni')->nullable()->index();

            // Almacenamiento crudo de las respuestas de la API
            $table->json('completed_data')->nullable();
            $table->json('personal_info')->nullable();
            $table->json('user_data')->nullable();
            $table->json('roles_data')->nullable();

            // Trazabilidad y configuración de importación
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_global')->default(false);
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hsi_patient_data_sync');
    }
};