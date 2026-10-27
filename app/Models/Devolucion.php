<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devolucion extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo en la base de datos.
     *
     * @var string
     */
    protected $table = 'devoluciones';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'venta_id',
        'usuario_id',
        'fecha_hora',
        'motivo',
        'estado',
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
            'fecha_hora' => 'datetime',
        ];
    }

    /**
     * Venta sobre la cual se realiza la devolución.
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    /**
     * Usuario que autorizó o registró la devolución.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Renglones de productos/lotes que se están devolviendo.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleDevolucion::class, 'devolucion_id');
    }
}
