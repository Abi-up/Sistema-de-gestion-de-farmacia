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
        Schema::create('detalles_receta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_id')->constrained('recetas')->cascadeOnDelete();
            $table->foreignId('medicamento_id')->constrained('medicamentos')->restrictOnDelete();
            $table->string('dosis', 100);
            $table->string('frecuencia', 100);
            $table->string('duracion', 100);
            $table->unsignedInteger('cantidad_prescrita');
            $table->text('indicaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_receta');
    }
};
