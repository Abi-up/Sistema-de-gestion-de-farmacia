<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
}
