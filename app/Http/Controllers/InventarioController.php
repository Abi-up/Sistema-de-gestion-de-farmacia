<?php

namespace App\Http\Controllers;

use App\Exceptions\InventarioException;
use App\Http\Requests\RegistrarMovimientoRequest;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use App\Services\InventarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function __construct(
        protected InventarioService $inventarioService
    ) {
    }

    /**
     * Consulta el catálogo de existencias y lotes disponibles.
     */
    public function index(Request $request): JsonResponse
    {
        $medicamentoId = $request->query('medicamento_id');
        $estado = $request->query('estado');

        $lotes = Lote::with(['medicamento.categoria', 'medicamento.presentacion'])
            ->when($medicamentoId, fn ($q) => $q->where('medicamento_id', (int) $medicamentoId))
            ->when($estado, fn ($q) => $q->where('estado', strtoupper((string) $estado)))
            ->orderBy('fecha_caducidad', 'asc')
            ->paginate((int) $request->query('per_page', 25));

        return response()->json($lotes);
    }

    /**
     * Consulta el stock vendible total de un medicamento.
     */
    public function stockTotal(int $medicamentoId): JsonResponse
    {
        $medicamento = Medicamento::findOrFail($medicamentoId);
        $stock = $this->inventarioService->obtenerStockTotal($medicamento);

        return response()->json([
            'medicamento_id' => $medicamento->id,
            'nombre' => $medicamento->nombre,
            'stock_total_disponible' => $stock,
        ]);
    }

    /**
     * Consulta lotes disponibles mediante FEFO para un medicamento.
     */
    public function lotesFefo(Request $request, int $medicamentoId): JsonResponse
    {
        $cantidad = (int) $request->query('cantidad', 0);
        $lotes = $this->inventarioService->obtenerLotesDisponiblesFEFO($medicamentoId, $cantidad);

        return response()->json($lotes);
    }

    /**
     * Registra un movimiento de inventario (ENTRADA, SALIDA, AJUSTE, DEVOLUCION).
     */
    public function storeMovimiento(RegistrarMovimientoRequest $request): JsonResponse
    {
        $lote = Lote::findOrFail((int) $request->input('lote_id'));

        try {
            $movimiento = $this->inventarioService->registrarMovimiento(
                $lote,
                $request->user(),
                $request->input('tipo_movimiento'),
                (int) $request->input('cantidad'),
                $request->input('motivo'),
                $request->input('detalle_recepcion_id') ? (int) $request->input('detalle_recepcion_id') : null,
                $request->input('detalle_venta_id') ? (int) $request->input('detalle_venta_id') : null,
                $request->input('detalle_devolucion_id') ? (int) $request->input('detalle_devolucion_id') : null
            );

            return response()->json([
                'message' => 'Movimiento de inventario registrado exitosamente.',
                'movimiento' => $movimiento->load('lote'),
            ], 201);
        } catch (InventarioException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
