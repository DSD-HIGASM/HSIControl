<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('residencies', function (Blueprint $table) {
            $table->id();
            
            // A quién pertenece
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            
            // Datos académicos (Texto plano o Enum, sin tablas extra)
            $table->string('program_name'); // Ej: "Tocoginecología", "Terapia Intensiva"
            $table->string('current_year'); // Ej: "R1", "R2", "Jefe", "Rotante Externo"
            
            // ¿Dónde está parado FÍSICAMENTE HOY? (Apunta a tu tabla de HSI)
            $table->unsignedBigInteger('current_unit_id')->nullable();
            $table->foreign('current_unit_id')->references('id')->on('hierarchical_units');
            
            // ¿Cuándo se va del hospital? (Para saber cuándo darle de baja en el sistema)
            $table->date('end_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residencies');
    }
};
