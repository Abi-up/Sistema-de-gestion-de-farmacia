<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarProveedorRequest;
use App\Http\Requests\CrearProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    /**
     * Lista los proveedores con paginación y filtros por nombre, RFC y estado.
     */
    public function index(Request $request): JsonResponse
    {
        $nombre = $request->query('nombre');
        $rfc = $request->query('rfc');
        $activo = $request->query('activo');

        $proveedores = Proveedor::query()
            ->when($nombre, fn ($q) => $q->where('nombre', 'like', "%{$nombre}%"))
            ->when($rfc, fn ($q) => $q->where('rfc', 'like', "%{$rfc}%"))
            ->when($activo !== null, fn ($q) => $q->where('activo', filter_var($activo, FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('nombre', 'asc')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json($proveedores);
    }

    /**
     * Registra un nuevo proveedor en el sistema.
     */
    public function store(CrearProveedorRequest $request): JsonResponse
    {
        $proveedor = Proveedor::create($request->validated());

        return response()->json([
            'message' => 'Proveedor creado exitosamente.',
            'proveedor' => $proveedor,
        ], 201);
    }

    /**
     * Consulta el detalle de un proveedor específico.
     */
    public function show(int $id): JsonResponse
    {
        $proveedor = Proveedor::findOrFail($id);

        return response()->json($proveedor);
    }

    /**
     * Actualiza los datos de un proveedor existente.
     */
    public function update(ActualizarProveedorRequest $request, int $id): JsonResponse
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->update($request->validated());

        return response()->json([
            'message' => 'Proveedor actualizado exitosamente.',
            'proveedor' => $proveedor,
        ]);
    }

    /**
     * Desactiva lógicamente un proveedor preservando el historial de compras.
     */
    public function destroy(int $id): JsonResponse
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->update(['activo' => false]);

        return response()->json([
            'message' => 'Proveedor desactivado exitosamente.',
            'proveedor' => $proveedor,
        ]);
    }
}
