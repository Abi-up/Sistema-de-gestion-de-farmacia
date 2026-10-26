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
        Schema::create('recuperacion_contrasena', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('token_hash', 255)->index();
            $table->dateTime('fecha_solicitud')->useCurrent();
            $table->dateTime('fecha_expiracion')->index();
            $table->dateTime('fecha_uso')->nullable();
            $table->enum('estado', ['PENDIENTE', 'USADO', 'EXPIRADO'])->default('PENDIENTE')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recuperacion_contrasena');
    }
};
