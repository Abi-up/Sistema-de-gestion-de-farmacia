<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcesarVentaRequest extends FormRequest
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
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicamento_id' => ['required', 'integer', 'exists:medicamentos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.descuento' => ['nullable', 'numeric', 'min:0'],
            'receta_ids' => ['nullable', 'array'],
            'receta_ids.*' => ['integer', 'exists:recetas,id'],
            'descuento_global' => ['nullable', 'numeric', 'min:0'],
            'tasa_impuesto' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ];
    }
}
