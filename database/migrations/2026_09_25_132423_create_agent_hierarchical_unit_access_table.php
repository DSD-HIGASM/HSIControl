<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_hierarchical_unit_access', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agent_id')
                ->constrained('agents')
                ->onDelete('cascade');

            $table->foreignId('hierarchical_unit_id')
                ->constrained('hierarchical_units')
                ->onDelete('cascade');

            // Campos obligatorios de auditoría
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agent_id', 'hierarchical_unit_id'], 'agent_unit_access_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_hierarchical_unit_access');
    }
};