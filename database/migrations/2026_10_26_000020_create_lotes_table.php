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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicamento_id')->constrained('medicamentos')->restrictOnDelete();
            $table->foreignId('detalle_recepcion_id')->nullable()->constrained('detalles_recepcion')->restrictOnDelete();
            $table->string('numero_lote', 50)->index();
            $table->date('fecha_fabricacion')->nullable();
            $table->date('fecha_caducidad')->index();
            $table->unsignedInteger('cantidad_inicial');
            $table->unsignedInteger('cantidad_disponible');
            $table->enum('estado', ['DISPONIBLE', 'AGOTADO', 'POR_CADUCAR', 'CADUCADO', 'CUARENTENA'])->default('DISPONIBLE')->index();
            $table->unique(['medicamento_id', 'numero_lote'], 'uk_medicamento_lote');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
