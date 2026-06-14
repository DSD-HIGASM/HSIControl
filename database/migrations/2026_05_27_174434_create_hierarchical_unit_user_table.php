<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Renombramos la tabla para que siga la convención y apunte al Agente
        Schema::create('agent_hierarchical_unit', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('hierarchical_unit_id');
            $table->foreign('hierarchical_unit_id')
                ->references('id')
                ->on('hierarchical_units')
                ->onDelete('cascade');
                
            // Ahora apunta a agents
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            
            $table->boolean('responsible')->default(false);
            
            // Auditoría (estos sí quedan apuntando a users, quien hizo el clic)
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Un agente no puede estar asignado dos veces a la misma unidad
            $table->unique(['hierarchical_unit_id', 'agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_hierarchical_unit');
    }
};