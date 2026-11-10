<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarPresentacionRequest extends FormRequest
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
        $presentacionId = $this->route('presentacione') ?? $this->route('presentacion');

        return [
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('presentaciones', 'nombre')->ignore($presentacionId),
            ],
            'descripcion' => ['nullable', 'string'],
        ];
    }
}
