<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarLaboratorioRequest extends FormRequest
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
        $laboratorioId = $this->route('laboratorio');

        return [
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:150',
                Rule::unique('laboratorios', 'nombre')->ignore($laboratorioId),
            ],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'correo' => ['nullable', 'string', 'email', 'max:100'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
