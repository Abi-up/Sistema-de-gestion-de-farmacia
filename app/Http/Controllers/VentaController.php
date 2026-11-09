<?php

namespace App\Http\Controllers;

use App\Exceptions\InventarioException;
use App\Exceptions\RecetaException;
use App\Exceptions\VentaException;
use App\Http\Requests\CancelarVentaRequest;
use App\Http\Requests\ProcesarVentaRequest;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function __construct(
        protected VentaService $ventaService
    ) {
    }

    /**
     * Consulta el listado de ventas realizadas.
     */
    public function index(Request $request): JsonResponse
    {
        $estado = $request->query('estado');
        $folio = $request->query('folio');
        $fecha = $request->query('fecha');

        $ventas = Venta::with(['usuario', 'detalles.medicamento', 'recetas'])
            ->when($estado, fn ($q) => $q->where('estado', strtoupper((string) $estado)))
            ->when($folio, fn ($q) => $q->where('folio', 'like', "%{$folio}%"))
            ->when($fecha, fn ($q) => $q->whereDate('fecha_hora', $fecha))
            ->orderBy('fecha_hora', 'desc')
            ->paginate((int) $request->query('per_page', 25));

        return response()->json($ventas);
    }

    /**
     * Muestra el detalle completo de un ticket de venta.
     */
    public function show(int $id): JsonResponse
    {
        $venta = Venta::with([
            'usuario',
            'detalles.medicamento.categoria',
            'detalles.medicamento.presentacion',
            'detalles.ventasLotes.lote',
            'recetas.detalles',
            'devoluciones.detalles',
        ])->findOrFail($id);

        return response()->json($venta);
    }

    /**
     * Procesa y cobra una nueva venta en mostrador/caja.
     */
    public function store(ProcesarVentaRequest $request): JsonResponse
    {
        try {
            $venta = $this->ventaService->procesarVenta(
                $request->user(),
                $request->input('items'),
                $request->input('receta_ids', []),
                (float) $request->input('descuento_global', 0.0),
                (float) $request->input('tasa_impuesto', 0.0)
            );

            return response()->json([
                'message' => 'Venta procesada exitosamente.',
                'venta' => $venta,
            ], 201);
        } catch (VentaException|InventarioException|RecetaException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancela una venta activa y reintegra el stock a sus lotes.
     */
    public function cancelar(CancelarVentaRequest $request, int $id): JsonResponse
    {
        try {
            $venta = $this->ventaService->cancelarVenta(
                $id,
                $request->user(),
                $request->input('motivo')
            );

            return response()->json([
                'message' => 'Venta cancelada exitosamente.',
                'venta' => $venta,
            ]);
        } catch (VentaException|InventarioException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
