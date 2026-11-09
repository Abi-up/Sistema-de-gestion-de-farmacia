<?php

namespace App\Http\Controllers;

use App\Services\CaducidadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaducidadController extends Controller
{
    public function __construct(
        protected CaducidadService $caducidadService
    ) {
    }

    /**
     * Consulta lotes caducados por fecha o marcados en estado CADUCADO.
     */
    public function caducados(Request $request): JsonResponse
    {
        $medicamentoId = $request->query('medicamento_id') ? (int) $request->query('medicamento_id') : null;
        $lotes = $this->caducidadService->obtenerLotesCaducados($medicamentoId);

        return response()->json($lotes);
    }

    /**
     * Consulta lotes próximos a caducar dentro de un plazo configurable.
     */
    public function porCaducar(Request $request): JsonResponse
    {
        $dias = (int) $request->query('dias', CaducidadService::DIAS_VENTANA_DEFECTO);
        $medicamentoId = $request->query('medicamento_id') ? (int) $request->query('medicamento_id') : null;

        $lotes = $this->caducidadService->obtenerLotesPorCaducar($dias, $medicamentoId);

        return response()->json($lotes);
    }

    /**
     * Genera un resumen estadístico de la situación de caducidades en el anaquel.
     */
    public function resumen(): JsonResponse
    {
        $resumen = $this->caducidadService->obtenerResumenLotes();

        return response()->json($resumen);
    }

    /**
     * Sincroniza los estados administrativos de los lotes sin tocar existencias.
     */
    public function sincronizar(Request $request): JsonResponse
    {
        $dias = (int) $request->input('dias', CaducidadService::DIAS_VENTANA_DEFECTO);
        $resultado = $this->caducidadService->sincronizarEstadosCaducidad($dias);

        return response()->json([
            'message' => 'Estados de caducidad sincronizados exitosamente.',
            'resultado' => $resultado,
        ]);
    }
}
