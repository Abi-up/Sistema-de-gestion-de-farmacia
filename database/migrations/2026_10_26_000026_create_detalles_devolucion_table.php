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
        Schema::create('detalles_devolucion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devolucion_id')->constrained('devoluciones')->restrictOnDelete();
            $table->foreignId('venta_lote_id')->constrained('ventas_lotes')->restrictOnDelete();
            $table->unsignedInteger('cantidad');
            $table->string('motivo', 255);
            $table->enum('destino_producto', ['REINGRESADO_INVENTARIO', 'MERMA_DESTRUIDO'])->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_devolucion');
    }
};
