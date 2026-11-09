<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearCompraRequest extends FormRequest
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
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
            'fecha' => ['nullable', 'date'],
            'partidas' => ['required', 'array', 'min:1'],
            'partidas.*.medicamento_id' => ['required', 'integer', 'exists:medicamentos,id'],
            'partidas.*.cantidad_solicitada' => ['required', 'integer', 'min:1'],
            'partidas.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ];
    }
}
