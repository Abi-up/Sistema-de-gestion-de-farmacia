<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleReceta extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo en la base de datos.
     *
     * @var string
     */
    protected $table = 'detalles_receta';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'receta_id',
        'medicamento_id',
        'dosis',
        'frecuencia',
        'duracion',
        'cantidad_prescrita',
        'indicaciones',
    ];

    /**
     * Atributos casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad_prescrita' => 'integer',
        ];
    }

    /**
     * Receta médica a la que pertenece esta prescripción.
     */
    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    /**
     * Medicamento prescrito en este detalle.
     */
    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class, 'medicamento_id');
    }
}
