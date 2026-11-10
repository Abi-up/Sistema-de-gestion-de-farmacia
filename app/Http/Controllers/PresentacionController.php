<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarPresentacionRequest;
use App\Http\Requests\CrearPresentacionRequest;
use App\Models\Presentacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresentacionController extends Controller
{
    /**
     * Lista las presentaciones farmacéuticas con paginación y filtro por nombre.
     */
    public function index(Request $request): JsonResponse
    {
        $nombre = $request->query('nombre');

        $presentaciones = Presentacion::query()
            ->when($nombre, fn ($q) => $q->where('nombre', 'like', "%{$nombre}%"))
            ->orderBy('nombre', 'asc')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json($presentaciones);
    }

    /**
     * Registra una nueva presentación farmacéutica.
     */
    public function store(CrearPresentacionRequest $request): JsonResponse
    {
        $presentacion = Presentacion::create($request->validated());

        return response()->json([
            'message' => 'Presentación creada exitosamente.',
            'presentacion' => $presentacion,
        ], 201);
    }

    /**
     * Consulta el detalle de una presentación específica.
     */
    public function show(int $id): JsonResponse
    {
        $presentacion = Presentacion::findOrFail($id);

        return response()->json($presentacion);
    }

    /**
     * Actualiza los datos de una presentación existente.
     */
    public function update(ActualizarPresentacionRequest $request, int $id): JsonResponse
    {
        $presentacion = Presentacion::findOrFail($id);
        $presentacion->update($request->validated());

        return response()->json([
            'message' => 'Presentación actualizada exitosamente.',
            'presentacion' => $presentacion,
        ]);
    }

    /**
     * Elimina una presentación farmacéutica si no tiene medicamentos asociados.
     */
    public function destroy(int $id): JsonResponse
    {
        $presentacion = Presentacion::findOrFail($id);

        if ($presentacion->medicamentos()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar la presentación porque tiene medicamentos vinculados en el catálogo.',
            ], 422);
        }

        $presentacion->delete();

        return response()->json([
            'message' => 'Presentación eliminada exitosamente.',
        ]);
    }
}
