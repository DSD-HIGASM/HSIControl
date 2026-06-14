<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('agent_profession_specialty')->onDelete('cascade');
            $table->string('number');
            $table->string('scope'); // provincial o national
            $table->string('type');  // profession o specialty
            $table->timestamps();
            $table->softDeletes();   // Agregado para mantener el estándar del sistema
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};