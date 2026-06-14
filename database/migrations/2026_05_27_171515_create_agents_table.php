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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('last_name');
            $table->string('second_last_name')->nullable();
            $table->string('first_name');
            $table->string('second_first_name')->nullable();
            $table->integer('dni');
            $table->string('gender');
            $table->string('email');
            $table->string('phone');
            $table->string('status');
            $table->integer('person_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('user')->nullable();
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
