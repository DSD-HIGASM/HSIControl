<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Configuración global del Clúster / Red Mesh
        Schema::create('miky_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // 2. Inventario de Terminales (Mini PCs)
        Schema::create('miky_terminals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip')->index();
            $table->string('mac')->nullable();
            $table->string('sector')->default('General');
            $table->string('resolution')->default('auto');
            $table->json('custom_buttons')->nullable(); // [{name, url}]
            $table->json('cron')->nullable();           // {on: "06:00", off: "20:00"}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('miky_terminals');
        Schema::dropIfExists('miky_settings');
    }
};