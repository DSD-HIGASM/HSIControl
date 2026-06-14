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
        Schema::create('hierarchical_unit_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hierarchical_unit_parent_id')
          ->constrained('hierarchical_units')
          ->onDelete('cascade');
            $table->foreignId('hierarchical_unit_child_id')
          ->constrained('hierarchical_units')
          ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['hierarchical_unit_parent_id', 'hierarchical_unit_child_id'], 'unit_relation_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hierarchical_unit_relations');
    }
};
