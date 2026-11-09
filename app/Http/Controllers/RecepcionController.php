<?php

namespace App\Http\Controllers;

use App\Exceptions\CompraException;
use App\Exceptions\InventarioException;
use App\Http\Requests\RegistrarRecepcionRequest;
use App\Models\Recepcion;
use App\Services\RecepcionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecepcionController extends Controller
{
    public function __construct(
        protected RecepcionService $recepcionService
    ) {
    }

    /**
     * Consulta el historial de recepciones registradas.
     */
    public function index(Request $request): JsonResponse
    {
        $compraId = $request->query('compra_id');

        $recepciones = Recepcion::with(['compra.proveedor', 'usuario', 'detalles.detalleCompra.medicamento'])
            ->when($compraId, fn ($q) => $q->where('compra_id', (int) $compraId))
            ->orderBy('fecha_hora', 'desc')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json($recepciones);
    }

    /**
     * Registra una entrega física (total o parcial) de una compra.
     */
    public function store(RegistrarRecepcionRequest $request, int $compraId): JsonResponse
    {
        try {
            $recepcion = $this->recepcionService->registrarRecepcion(
                $compraId,
                $request->user(),
                $request->input('partidas'),
                $request->input('observaciones'),
                $request->input('estado', 'RECIBIDO')
            );

            return response()->json([
                'message' => 'Recepción de mercancía registrada exitosamente.',
                'recepcion' => $recepcion,
            ], 201);
        } catch (CompraException|InventarioException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
