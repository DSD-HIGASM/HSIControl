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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Usamos DNI en lugar de email como identificador único
            $table->string('dni')->unique();
            
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            
            // Agregamos softDeletes para no perder el historial de auditoría de un usuario dado de baja
            $table->softDeletes(); 
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            // El reseteo de contraseña ahora se buscará por DNI
            $table->string('dni')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};