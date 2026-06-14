<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_type_hsi_role', function (Blueprint $table) {
            $table->id();
            
            // Llaves foráneas apuntando a tus tablas explícitamente
            $table->foreignId('hsi_role_id')->constrained('hsi_roles')->onDelete('cascade');
            $table->foreignId('document_type_id')->constrained('document_types')->onDelete('cascade');
            
            // Opcional pero recomendado: un flag para saber si el documento es bloqueante/obligatorio
            $table->boolean('is_mandatory')->default(true);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_type_hsi_role');
    }
};