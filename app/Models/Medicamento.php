<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicamento extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo en la base de datos.
     *
     * @var string
     */
    protected $table = 'medicamentos';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'categoria_id',
        'presentacion_id',
        'marca_id',
        'laboratorio_id',
        'nombre',
        'formula',
        'codigo_barras',
        'requiere_receta',
        'activo',
    ];

    /**
     * Atributos casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requiere_receta' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /**
     * Categoría terapéutica a la que pertenece el medicamento.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Presentación farmacéutica del medicamento.
     */
    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(Presentacion::class, 'presentacion_id');
    }

    /**
     * Marca comercial del medicamento.
     */
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    /**
     * Laboratorio fabricante del medicamento.
     */
    public function laboratorio(): BelongsTo
    {
        return $this->belongsTo(Laboratorio::class, 'laboratorio_id');
    }

    /**
     * Lotes físicos registrados para este medicamento.
     */
    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'medicamento_id');
    }

    /**
     * Detalles de prescripción médica asociados.
     */
    public function detallesReceta(): HasMany
    {
        return $this->hasMany(DetalleReceta::class, 'medicamento_id');
    }

    /**
     * Renglones de compras en los que se ha ordenado el medicamento.
     */
    public function detallesCompra(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'medicamento_id');
    }

    /**
     * Renglones de ventas donde se ha despachado el medicamento.
     */
    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'medicamento_id');
    }
}
