<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo en la base de datos.
     *
     * @var string
     */
    protected $table = 'movimientos_inventario';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lote_id',
        'usuario_id',
        'tipo_movimiento',
        'cantidad',
        'fecha_hora',
        'motivo',
        'detalle_recepcion_id',
        'detalle_venta_id',
        'detalle_devolucion_id',
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
            'fecha_hora' => 'datetime',
        ];
    }

    /**
     * Lote sobre el cual se realiza la afectación de stock.
     */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    /**
     * Usuario responsable de registrar o autorizar el movimiento.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Renglón de recepción que originó este movimiento (en caso de ENTRADA por compra).
     */
    public function detalleRecepcion(): BelongsTo
    {
        return $this->belongsTo(DetalleRecepcion::class, 'detalle_recepcion_id');
    }

    /**
     * Renglón de venta que originó este movimiento (en caso de SALIDA por venta).
     */
    public function detalleVenta(): BelongsTo
    {
        return $this->belongsTo(DetalleVenta::class, 'detalle_venta_id');
    }

    /**
     * Renglón de devolución que originó este movimiento (en caso de DEVOLUCION).
     */
    public function detalleDevolucion(): BelongsTo
    {
        return $this->belongsTo(DetalleDevolucion::class, 'detalle_devolucion_id');
    }
}
