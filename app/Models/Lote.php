<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo en la base de datos.
     *
     * @var string
     */
    protected $table = 'lotes';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'medicamento_id',
        'detalle_recepcion_id',
        'numero_lote',
        'fecha_fabricacion',
        'fecha_caducidad',
        'cantidad_inicial',
        'cantidad_disponible',
        'estado',
    ];

    /**
     * Atributos casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_fabricacion' => 'date',
            'fecha_caducidad' => 'date',
            'cantidad_inicial' => 'integer',
            'cantidad_disponible' => 'integer',
        ];
    }

    /**
     * Medicamento al que pertenece el lote.
     */
    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class, 'medicamento_id');
    }

    /**
     * Recepción física de compra que originó este lote (anulable si es inventario inicial).
     */
    public function detalleRecepcion(): BelongsTo
    {
        return $this->belongsTo(DetalleRecepcion::class, 'detalle_recepcion_id');
    }

    /**
     * Dispensaciones o asignaciones de unidades de este lote en ventas.
     */
    public function ventasLotes(): HasMany
    {
        return $this->hasMany(VentaLote::class, 'lote_id');
    }

    /**
     * Historial de movimientos de stock (entradas, salidas, ajustes) de este lote.
     */
    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'lote_id');
    }
}
