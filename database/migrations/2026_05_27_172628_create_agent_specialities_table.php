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
        Schema::create('agent_profession_specialty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            
            // Apuntamos explícitamente a la tabla 'occupations'
            $table->foreignId('profession_id')->constrained('occupations')->onDelete('cascade');
            
            // Apuntamos explícitamente a la tabla 'specialities' (con la "i")
            $table->foreignId('specialty_id')->nullable()->constrained('specialities')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_profession_specialty');
    }
};