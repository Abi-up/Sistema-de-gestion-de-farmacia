<?php

namespace App\Http\Controllers;

use App\Exceptions\DevolucionException;
use App\Exceptions\InventarioException;
use App\Http\Requests\ProcesarDevolucionRequest;
use App\Models\Devolucion;
use App\Services\DevolucionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DevolucionController extends Controller
{
    public function __construct(
        protected DevolucionService $devolucionService
    ) {
    }

    /**
     * Consulta el listado de devoluciones registradas.
     */
    public function index(Request $request): JsonResponse
    {
        $ventaId = $request->query('venta_id');
        $estado = $request->query('estado');

        $devoluciones = Devolucion::with(['venta', 'usuario', 'detalles.ventaLote.lote.medicamento'])
            ->when($ventaId, fn ($q) => $q->where('venta_id', (int) $ventaId))
            ->when($estado, fn ($q) => $q->where('estado', strtoupper((string) $estado)))
            ->orderBy('fecha_hora', 'desc')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json($devoluciones);
    }

    /**
     * Muestra el detalle de una devolución.
     */
    public function show(int $id): JsonResponse
    {
        $devolucion = Devolucion::with([
            'venta.detalles.medicamento',
            'usuario',
            'detalles.ventaLote.lote.medicamento',
        ])->findOrFail($id);

        return response()->json($devolucion);
    }

    /**
     * Procesa una devolución sobre una venta determinada.
     */
    public function store(ProcesarDevolucionRequest $request, int $ventaId): JsonResponse
    {
        try {
            $devolucion = $this->devolucionService->procesarDevolucion(
                $ventaId,
                $request->user(),
                $request->input('motivo'),
                $request->input('partidas'),
                $request->input('observaciones'),
                $request->input('estado', 'PROCESADA')
            );

            return response()->json([
                'message' => 'Devolución procesada exitosamente.',
                'devolucion' => $devolucion,
            ], 201);
        } catch (DevolucionException|InventarioException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
