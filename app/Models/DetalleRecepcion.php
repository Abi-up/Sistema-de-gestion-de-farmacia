<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetalleRecepcion extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo en la base de datos.
     *
     * @var string
     */
    protected $table = 'detalles_recepcion';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'recepcion_id',
        'detalle_compra_id',
        'cantidad_recibida',
        'observaciones',
    ];

    /**
     * Atributos casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad_recibida' => 'integer',
        ];
    }

    /**
     * Cabecera de recepción a la que pertenece este detalle.
     */
    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(Recepcion::class, 'recepcion_id');
    }

    /**
     * Renglón de compra original que se está surtiendo.
     */
    public function detalleCompra(): BelongsTo
    {
        return $this->belongsTo(DetalleCompra::class, 'detalle_compra_id');
    }

    /**
     * Lotes generados o ingresados físicamente a partir de esta recepción.
     */
    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'detalle_recepcion_id');
    }

    /**
     * Movimientos de inventario (entradas) vinculados a esta recepción.
     */
    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'detalle_recepcion_id');
    }
}
