<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarCategoriaRequest;
use App\Http\Requests\CrearCategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Lista las categorías con paginación y filtros por nombre y estado.
     */
    public function index(Request $request): JsonResponse
    {
        $nombre = $request->query('nombre');
        $activo = $request->query('activo');

        $categorias = Categoria::query()
            ->when($nombre, fn ($q) => $q->where('nombre', 'like', "%{$nombre}%"))
            ->when($activo !== null, fn ($q) => $q->where('activo', filter_var($activo, FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('nombre', 'asc')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json($categorias);
    }

    /**
     * Registra una nueva categoría en el sistema.
     */
    public function store(CrearCategoriaRequest $request): JsonResponse
    {
        $categoria = Categoria::create($request->validated());

        return response()->json([
            'message' => 'Categoría creada exitosamente.',
            'categoria' => $categoria,
        ], 201);
    }

    /**
     * Consulta el detalle de una categoría específica.
     */
    public function show(int $id): JsonResponse
    {
        $categoria = Categoria::findOrFail($id);

        return response()->json($categoria);
    }

    /**
     * Actualiza los datos de una categoría existente.
     */
    public function update(ActualizarCategoriaRequest $request, int $id): JsonResponse
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->update($request->validated());

        return response()->json([
            'message' => 'Categoría actualizada exitosamente.',
            'categoria' => $categoria,
        ]);
    }

    /**
     * Desactiva lógicamente una categoría preservando su historial.
     */
    public function destroy(int $id): JsonResponse
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->update(['activo' => false]);

        return response()->json([
            'message' => 'Categoría desactivada exitosamente.',
            'categoria' => $categoria,
        ]);
    }
}
