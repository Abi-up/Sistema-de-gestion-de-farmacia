<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearLaboratorioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150', 'unique:laboratorios,nombre'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'correo' => ['nullable', 'string', 'email', 'max:100'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
