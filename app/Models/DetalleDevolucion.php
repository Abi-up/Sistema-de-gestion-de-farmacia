<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetalleDevolucion extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo en la base de datos.
     *
     * @var string
     */
    protected $table = 'detalles_devolucion';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'devolucion_id',
        'venta_lote_id',
        'cantidad',
        'motivo',
        'destino_producto',
    ];

    /**
     * Atributos casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
        ];
    }

    /**
     * Devolución cabecera a la que pertenece esta partida.
     */
    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(Devolucion::class, 'devolucion_id');
    }

    /**
     * Registro de venta-lote del que proviene el medicamento devuelto.
     */
    public function ventaLote(): BelongsTo
    {
        return $this->belongsTo(VentaLote::class, 'venta_lote_id');
    }

    /**
     * Movimientos de inventario generados por la devolución (por reingreso o merma).
     */
    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'detalle_devolucion_id');
    }
}
