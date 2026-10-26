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
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->foreignId('presentacion_id')->constrained('presentaciones')->restrictOnDelete();
            $table->foreignId('marca_id')->constrained('marcas')->restrictOnDelete();
            $table->foreignId('laboratorio_id')->constrained('laboratorios')->restrictOnDelete();
            $table->string('nombre', 150)->index();
            $table->text('formula')->nullable();
            $table->string('codigo_barras', 50)->nullable()->unique();
            $table->boolean('requiere_receta')->default(false)->index();
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicamentos');
    }
};
