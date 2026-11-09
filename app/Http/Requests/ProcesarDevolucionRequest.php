<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcesarDevolucionRequest extends FormRequest
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
            'motivo' => ['required', 'string', 'max:500'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'string', 'in:SOLICITADA,APROBADA,RECHAZADA,PROCESADA'],
            'partidas' => ['required', 'array', 'min:1'],
            'partidas.*.venta_lote_id' => ['required', 'integer', 'exists:ventas_lotes,id'],
            'partidas.*.cantidad' => ['required', 'integer', 'min:1'],
            'partidas.*.destino_producto' => ['required', 'string', 'in:REINGRESADO_INVENTARIO,MERMA_DESTRUIDO'],
            'partidas.*.motivo' => ['nullable', 'string', 'max:255'],
        ];
    }
}
