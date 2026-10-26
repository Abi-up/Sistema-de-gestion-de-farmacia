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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lotes')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->enum('tipo_movimiento', ['ENTRADA', 'SALIDA', 'AJUSTE', 'DEVOLUCION'])->index();
            $table->integer('cantidad'); // Positivo o negativo según el tipo de movimiento
            $table->dateTime('fecha_hora')->useCurrent()->index();
            $table->text('motivo')->nullable();
            
            // Claves foráneas explícitas y anulables al evento de origen (sin polimorfismo ciego)
            $table->foreignId('detalle_recepcion_id')->nullable()->constrained('detalles_recepcion')->nullOnDelete();
            $table->foreignId('detalle_venta_id')->nullable()->constrained('detalles_venta')->nullOnDelete();
            $table->foreignId('detalle_devolucion_id')->nullable()->constrained('detalles_devolucion')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
