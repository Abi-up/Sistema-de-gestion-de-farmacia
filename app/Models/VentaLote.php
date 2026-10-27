<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VentaLote extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo en la base de datos.
     *
     * @var string
     */
    protected $table = 'ventas_lotes';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'detalle_venta_id',
        'lote_id',
        'cantidad',
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
     * Renglón de venta al que se asignó este lote.
     */
    public function detalleVenta(): BelongsTo
    {
        return $this->belongsTo(DetalleVenta::class, 'detalle_venta_id');
    }

    /**
     * Lote del cual se tomaron físicamente las unidades vendidas.
     */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    /**
     * Devoluciones aplicadas sobre las unidades de este lote vendido.
     */
    public function detallesDevolucion(): HasMany
    {
        return $this->hasMany(DetalleDevolucion::class, 'venta_lote_id');
    }
}
