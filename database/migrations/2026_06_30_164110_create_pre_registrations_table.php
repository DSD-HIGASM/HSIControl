<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('pre_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary(); // El token único que viajará en la URL
            $table->json('data');          // El JSON completo desde HSI
            $table->foreignId('user_id')->constrained(); // Quién lo importó
            $table->boolean('is_global')->default(false); // Switch: Individual vs División
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('pre_registrations');
    }
};