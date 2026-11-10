<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarMarcaRequest;
use App\Http\Requests\CrearMarcaRequest;
use App\Models\Marca;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    /**
     * Lista las marcas comerciales con paginación y filtros por nombre y estado.
     */
    public function index(Request $request): JsonResponse
    {
        $nombre = $request->query('nombre');
        $activo = $request->query('activo');

        $marcas = Marca::query()
            ->when($nombre, fn ($q) => $q->where('nombre', 'like', "%{$nombre}%"))
            ->when($activo !== null, fn ($q) => $q->where('activo', filter_var($activo, FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('nombre', 'asc')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json($marcas);
    }

    /**
     * Registra una nueva marca comercial.
     */
    public function store(CrearMarcaRequest $request): JsonResponse
    {
        $marca = Marca::create($request->validated());

        return response()->json([
            'message' => 'Marca creada exitosamente.',
            'marca' => $marca,
        ], 201);
    }

    /**
     * Consulta el detalle de una marca específica.
     */
    public function show(int $id): JsonResponse
    {
        $marca = Marca::findOrFail($id);

        return response()->json($marca);
    }

    /**
     * Actualiza los datos de una marca existente.
     */
    public function update(ActualizarMarcaRequest $request, int $id): JsonResponse
    {
        $marca = Marca::findOrFail($id);
        $marca->update($request->validated());

        return response()->json([
            'message' => 'Marca actualizada exitosamente.',
            'marca' => $marca,
        ]);
    }

    /**
     * Desactiva lógicamente una marca preservando el historial de productos.
     */
    public function destroy(int $id): JsonResponse
    {
        $marca = Marca::findOrFail($id);
        $marca->update(['activo' => false]);

        return response()->json([
            'message' => 'Marca desactivada exitosamente.',
            'marca' => $marca,
        ]);
    }
}
