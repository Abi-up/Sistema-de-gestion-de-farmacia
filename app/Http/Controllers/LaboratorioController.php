<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarLaboratorioRequest;
use App\Http\Requests\CrearLaboratorioRequest;
use App\Models\Laboratorio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaboratorioController extends Controller
{
    /**
     * Lista los laboratorios farmacéuticos con paginación y filtros por nombre y estado.
     */
    public function index(Request $request): JsonResponse
    {
        $nombre = $request->query('nombre');
        $activo = $request->query('activo');

        $laboratorios = Laboratorio::query()
            ->when($nombre, fn ($q) => $q->where('nombre', 'like', "%{$nombre}%"))
            ->when($activo !== null, fn ($q) => $q->where('activo', filter_var($activo, FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('nombre', 'asc')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json($laboratorios);
    }

    /**
     * Registra un nuevo laboratorio fabricante.
     */
    public function store(CrearLaboratorioRequest $request): JsonResponse
    {
        $laboratorio = Laboratorio::create($request->validated());

        return response()->json([
            'message' => 'Laboratorio creado exitosamente.',
            'laboratorio' => $laboratorio,
        ], 201);
    }

    /**
     * Consulta el detalle de un laboratorio específico.
     */
    public function show(int $id): JsonResponse
    {
        $laboratorio = Laboratorio::findOrFail($id);

        return response()->json($laboratorio);
    }

    /**
     * Actualiza los datos de un laboratorio existente.
     */
    public function update(ActualizarLaboratorioRequest $request, int $id): JsonResponse
    {
        $laboratorio = Laboratorio::findOrFail($id);
        $laboratorio->update($request->validated());

        return response()->json([
            'message' => 'Laboratorio actualizado exitosamente.',
            'laboratorio' => $laboratorio,
        ]);
    }

    /**
     * Desactiva lógicamente un laboratorio preservando su historial.
     */
    public function destroy(int $id): JsonResponse
    {
        $laboratorio = Laboratorio::findOrFail($id);
        $laboratorio->update(['activo' => false]);

        return response()->json([
            'message' => 'Laboratorio desactivado exitosamente.',
            'laboratorio' => $laboratorio,
        ]);
    }
}
