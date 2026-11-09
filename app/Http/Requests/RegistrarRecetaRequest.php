<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarRecetaRequest extends FormRequest
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
            'folio' => ['required', 'string', 'max:50', 'unique:recetas,folio'],
            'nombre_medico' => ['required', 'string', 'max:150'],
            'cedula_profesional' => ['required', 'string', 'max:50'],
            'fecha_emision' => ['required', 'date', 'before_or_equal:today'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.medicamento_id' => ['required', 'integer', 'exists:medicamentos,id'],
            'detalles.*.dosis' => ['required', 'string', 'max:100'],
            'detalles.*.frecuencia' => ['required', 'string', 'max:100'],
            'detalles.*.duracion' => ['required', 'string', 'max:100'],
            'detalles.*.cantidad_prescrita' => ['required', 'integer', 'min:1'],
            'detalles.*.indicaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
