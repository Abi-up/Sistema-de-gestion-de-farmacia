<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Tabla asociada al modelo en la base de datos.
     */
    protected $table = 'usuarios';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellidos',
        'correo',
        'telefono',
        'usuario',
        'contrasena_hash',
        'estado',
    ];

    /**
     * Los atributos que deben ocultarse para la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'contrasena_hash',
        'remember_token',
    ];

    /**
     * Obtiene la contraseña para la autenticación de Laravel.
     */
    public function getAuthPassword()
    {
        return $this->contrasena_hash;
    }

    /**
     * Atributos casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contrasena_hash' => 'hashed',
        ];
    }

    /**
     * Roles asignados al usuario.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'usuario_id', 'rol_id')
            ->withTimestamps();
    }

    /**
     * Registros de bitácora generados por el usuario.
     */
    public function bitacoras(): HasMany
    {
        return $this->hasMany(Bitacora::class, 'usuario_id');
    }

    /**
     * Solicitudes de recuperación de contraseña del usuario.
     */
    public function recuperacionesContrasena(): HasMany
    {
        return $this->hasMany(RecuperacionContrasena::class, 'usuario_id');
    }

    /**
     * Compras u órdenes registradas por el usuario.
     */
    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'usuario_id');
    }

    /**
     * Recepciones de mercancía gestionadas por el usuario.
     */
    public function recepciones(): HasMany
    {
        return $this->hasMany(Recepcion::class, 'usuario_id');
    }

    /**
     * Ventas concretadas por el usuario/cajero.
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'usuario_id');
    }

    /**
     * Devoluciones autorizadas o procesadas por el usuario.
     */
    public function devoluciones(): HasMany
    {
        return $this->hasMany(Devolucion::class, 'usuario_id');
    }

    /**
     * Movimientos de almacén/inventario registrados por el usuario.
     */
    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'usuario_id');
    }
}
