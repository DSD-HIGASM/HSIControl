<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_agents', function (Blueprint $table) {
            $table->id();

            // Relación obligatoria con la tabla agents
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();

            // Datos institucionales UNLP
            $table->string('unlp_email');
            $table->string('personal_email');
            $table->string('facultad');
            $table->string('carrera_catedra');
            $table->string('materia');
            $table->string('cargo_docente');
            $table->string('legajo_unlp')->nullable();

            // Respaldo Legal (Anexo I)
            $table->string('anexo_version')->default('1.0');
            $table->date('anexo_signed_at')->nullable();
            $table->string('autoridad_firmante_nombre');
            $table->string('autoridad_firmante_cargo');

            // Estado de gestión manual en HSI Producción
            // Valores: 'pendiente_alta', 'activo', 'pendiente_renovacion', 'vencido', 'revocado'
            $table->string('hsi_status')->default('pendiente_alta');
            $table->string('hsi_username')->nullable();

            // Control estricto de vigencia y alertas automáticas
            $table->date('expires_at');
            $table->boolean('expiration_warning_sent')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_agents');
    }
};