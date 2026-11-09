<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarRecepcionRequest extends FormRequest
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
            'estado' => ['nullable', 'string', 'in:RECIBIDO,CON_OBSERVACIONES,RECHAZADO'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'partidas' => ['required', 'array', 'min:1'],
            'partidas.*.detalle_compra_id' => ['required', 'integer', 'exists:detalles_compra,id'],
            'partidas.*.cantidad_recibida' => ['required', 'integer', 'min:1'],
            'partidas.*.numero_lote' => ['required_unless:estado,RECHAZADO', 'nullable', 'string', 'max:50'],
            'partidas.*.fecha_caducidad' => ['required_unless:estado,RECHAZADO', 'nullable', 'date'],
            'partidas.*.fecha_fabricacion' => ['nullable', 'date'],
            'partidas.*.observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }
}
