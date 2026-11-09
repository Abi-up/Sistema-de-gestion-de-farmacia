<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarMovimientoRequest extends FormRequest
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
            'lote_id' => ['required', 'integer', 'exists:lotes,id'],
            'tipo_movimiento' => ['required', 'string', 'in:ENTRADA,SALIDA,AJUSTE,DEVOLUCION'],
            'cantidad' => ['required', 'integer'],
            'motivo' => ['nullable', 'string', 'max:1000'],
            'detalle_recepcion_id' => ['nullable', 'integer', 'exists:detalles_recepcion,id'],
            'detalle_venta_id' => ['nullable', 'integer', 'exists:detalles_venta,id'],
            'detalle_devolucion_id' => ['nullable', 'integer', 'exists:detalles_devolucion,id'],
        ];
    }
}
