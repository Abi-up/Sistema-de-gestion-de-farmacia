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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellidos', 100);
            $table->string('correo', 150)->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('usuario', 50)->unique();
            $table->string('contrasena_hash', 255);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO')->index();
            $table->rememberToken();
            $table->timestamps();
        });

        // Tabla requerida por Laravel para almacenar sesiones en base de datos (SESSION_DRIVER=database)
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
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('usuarios');
    }
};
