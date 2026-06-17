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
        Schema::create('hierarchical_units', function (Blueprint $table) {
            $table->id(); 
            $table->integer('institution_id')->default(484);
            $table->foreignId('type_id')->constrained('hierarchical_unit_types');
            $table->string('alias');
            $table->foreignId('hierarchical_unit_id_to_report')->nullable()->constrained('hierarchical_units');
            $table->foreignId('closest_service_id')->nullable()->constrained('hierarchical_units');
            $table->foreignId('clinical_specialty_id')->nullable()->constrained('specialities');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hierarchical_units');
    }
};
