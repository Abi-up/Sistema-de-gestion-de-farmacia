<?php

namespace App\Http\Controllers;

use App\Exceptions\CompraException;
use App\Http\Requests\CancelarCompraRequest;
use App\Http\Requests\CrearCompraRequest;
use App\Models\Compra;
use App\Services\CompraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    public function __construct(
        protected CompraService $compraService
    ) {
    }

    /**
     * Lista las órdenes de compra registradas.
     */
    public function index(Request $request): JsonResponse
    {
        $estado = $request->query('estado');
        $proveedorId = $request->query('proveedor_id');

        $compras = Compra::with(['proveedor', 'usuario', 'detalles.medicamento'])
            ->when($estado, fn ($q) => $q->where('estado', strtoupper((string) $estado)))
            ->when($proveedorId, fn ($q) => $q->where('proveedor_id', (int) $proveedorId))
            ->orderBy('fecha', 'desc')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json($compras);
    }

    /**
     * Muestra el detalle de una orden de compra y sus cantidades pendientes.
     */
    public function show(int $id): JsonResponse
    {
        $compra = Compra::with(['proveedor', 'usuario', 'detalles.medicamento', 'recepciones.detalles'])
            ->findOrFail($id);

        $detallesConPendientes = $compra->detalles->map(function ($detalle) {
            $pendiente = $this->compraService->obtenerCantidadPendientePorDetalle($detalle);

            return [
                'id' => $detalle->id,
                'medicamento_id' => $detalle->medicamento_id,
                'medicamento' => $detalle->medicamento->nombre ?? 'N/A',
                'cantidad_solicitada' => $detalle->cantidad_solicitada,
                'precio_unitario' => (float) $detalle->precio_unitario,
                'subtotal' => (float) $detalle->subtotal,
                'cantidad_pendiente' => $pendiente,
            ];
        });

        return response()->json([
            'compra' => $compra,
            'partidas' => $detallesConPendientes,
        ]);
    }

    /**
     * Crea una nueva orden de compra a proveedor calculando totales en el servidor.
     */
    public function store(CrearCompraRequest $request): JsonResponse
    {
        try {
            $compra = $this->compraService->crearOrdenCompra(
                (int) $request->input('proveedor_id'),
                $request->user(),
                $request->input('partidas'),
                $request->input('fecha')
            );

            return response()->json([
                'message' => 'Orden de compra creada exitosamente.',
                'compra' => $compra,
            ], 201);
        } catch (CompraException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancela una orden de compra pendiente.
     */
    public function cancelar(CancelarCompraRequest $request, int $id): JsonResponse
    {
        try {
            $compra = $this->compraService->cancelarCompra(
                $id,
                $request->user(),
                $request->input('motivo')
            );

            return response()->json([
                'message' => 'Orden de compra cancelada exitosamente.',
                'compra' => $compra,
            ]);
        } catch (CompraException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
