<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receta extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo en la base de datos.
     *
     * @var string
     */
    protected $table = 'recetas';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'folio',
        'nombre_medico',
        'cedula_profesional',
        'fecha_emision',
        'fecha_registro',
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
            'fecha_emision' => 'date',
            'fecha_registro' => 'datetime',
        ];
    }

    /**
     * Medicamentos y dosificaciones prescritas en la receta.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleReceta::class, 'receta_id');
    }

    /**
     * Ventas en las que se ha surtido o vinculado esta receta.
     */
    public function ventas(): BelongsToMany
    {
        return $this->belongsToMany(Venta::class, 'venta_receta', 'receta_id', 'venta_id')
            ->withTimestamps();
    }
}
