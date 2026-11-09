<?php

namespace App\Http\Controllers;

use App\Exceptions\RecetaException;
use App\Http\Requests\RegistrarRecetaRequest;
use App\Models\Receta;
use App\Services\RecetaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecetaController extends Controller
{
    public function __construct(
        protected RecetaService $recetaService
    ) {
    }

    /**
     * Consulta el catálogo de recetas médicas registradas.
     */
    public function index(Request $request): JsonResponse
    {
        $folio = $request->query('folio');
        $cedula = $request->query('cedula_profesional');
        $medico = $request->query('nombre_medico');

        $recetas = Receta::with(['detalles.medicamento'])
            ->when($folio, fn ($q) => $q->where('folio', 'like', "%{$folio}%"))
            ->when($cedula, fn ($q) => $q->where('cedula_profesional', 'like', "%{$cedula}%"))
            ->when($medico, fn ($q) => $q->where('nombre_medico', 'like', "%{$medico}%"))
            ->orderBy('fecha_registro', 'desc')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json($recetas);
    }

    /**
     * Consulta el detalle y prescripciones de una receta específica.
     */
    public function show(int $id): JsonResponse
    {
        $receta = Receta::with(['detalles.medicamento', 'ventas'])->findOrFail($id);

        return response()->json($receta);
    }

    /**
     * Da de alta una nueva receta médica en el sistema.
     */
    public function store(RegistrarRecetaRequest $request): JsonResponse
    {
        try {
            $receta = $this->recetaService->registrarReceta(
                [
                    'folio' => $request->input('folio'),
                    'nombre_medico' => $request->input('nombre_medico'),
                    'cedula_profesional' => $request->input('cedula_profesional'),
                    'fecha_emision' => $request->input('fecha_emision'),
                    'observaciones' => $request->input('observaciones'),
                ],
                $request->input('detalles')
            );

            return response()->json([
                'message' => 'Receta médica registrada exitosamente.',
                'receta' => $receta,
            ], 201);
        } catch (RecetaException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
