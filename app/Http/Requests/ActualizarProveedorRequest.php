<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarProveedorRequest extends FormRequest
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
        $proveedorId = $this->route('proveedore') ?? $this->route('proveedor');

        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:150'],
            'rfc' => [
                'nullable',
                'string',
                'max:13',
                Rule::unique('proveedores', 'rfc')->ignore($proveedorId),
            ],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'correo' => ['nullable', 'string', 'email', 'max:100'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
