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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->string('folio', 50)->unique();
            $table->dateTime('fecha_hora')->useCurrent()->index();
            $table->decimal('subtotal', 10, 2)->unsigned();
            $table->decimal('descuento', 10, 2)->unsigned()->default(0.00);
            $table->decimal('impuestos', 10, 2)->unsigned()->default(0.00);
            $table->decimal('total', 10, 2)->unsigned();
            $table->enum('estado', ['COMPLETADA', 'CANCELADA', 'DEVUELTA_PARCIAL', 'DEVUELTA_TOTAL'])->default('COMPLETADA')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
