<?php

namespace App\Services;

use App\Exceptions\RecetaException;
use App\Models\Medicamento;
use App\Models\Receta;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecetaService
{
    /**
     * Días de vigencia estándar de una receta médica (por defecto 30 días).
     */
    public const DIAS_VIGENCIA_DEFECTO = 30;

    /**
     * Registra una nueva receta médica con sus renglones de prescripción.
     *
     * @param array{
     *     folio: string,
     *     nombre_medico: string,
     *     cedula_profesional: string,
     *     fecha_emision: string,
     *     observaciones?: string|null
     * } $datosReceta
     * @param array<int, array{
     *     medicamento_id: int,
     *     dosis: string,
     *     frecuencia: string,
     *     duracion: string,
     *     cantidad_prescrita: int,
     *     indicaciones?: string|null
     * }> $detallesPrescripcion
     * @return Receta
     *
     * @throws RecetaException
     * @throws InvalidArgumentException
     */
    public function registrarReceta(array $datosReceta, array $detallesPrescripcion): Receta
    {
        $folio = trim((string) ($datosReceta['folio'] ?? ''));
        $nombreMedico = trim((string) ($datosReceta['nombre_medico'] ?? ''));
        $cedula = trim((string) ($datosReceta['cedula_profesional'] ?? ''));
        $fechaEmision = $datosReceta['fecha_emision'] ?? null;

        if ($folio === '' || $nombreMedico === '' || $cedula === '' || !$fechaEmision) {
            throw new InvalidArgumentException(
                'Los campos folio, nombre del médico, cédula profesional y fecha de emisión son obligatorios.'
            );
        }

        if (Receta::where('folio', $folio)->exists()) {
            throw new RecetaException("Ya existe una receta médica registrada con el folio '{$folio}'.");
        }

        $emisionCarbon = Carbon::parse($fechaEmision)->startOfDay();
        if ($emisionCarbon->isFuture()) {
            throw new RecetaException("La fecha de emisión de la receta ({$emisionCarbon->toDateString()}) no puede ser una fecha futura.");
        }

        if (empty($detallesPrescripcion)) {
            throw new InvalidArgumentException('La receta médica debe incluir al menos un medicamento prescrito.');
        }

        return DB::transaction(function () use ($datosReceta, $detallesPrescripcion, $emisionCarbon, $folio, $nombreMedico, $cedula): Receta {
            $receta = Receta::create([
                'folio' => $folio,
                'nombre_medico' => $nombreMedico,
                'cedula_profesional' => $cedula,
                'fecha_emision' => $emisionCarbon->toDateString(),
                'fecha_registro' => Carbon::now(),
                'observaciones' => $datosReceta['observaciones'] ?? null,
            ]);

            foreach ($detallesPrescripcion as $indice => $detalle) {
                $medicamentoId = $detalle['medicamento_id'] ?? null;
                $cantidad = (int) ($detalle['cantidad_prescrita'] ?? 0);

                if (!$medicamentoId || !Medicamento::where('id', $medicamentoId)->exists()) {
                    throw new RecetaException("El medicamento prescrito en el renglón #{$indice} no existe.");
                }

                if ($cantidad <= 0) {
                    throw new InvalidArgumentException("La cantidad prescrita en el renglón #{$indice} debe ser mayor a cero.");
                }

                $receta->detalles()->create([
                    'medicamento_id' => $medicamentoId,
                    'dosis' => trim((string) ($detalle['dosis'] ?? 'N/E')),
                    'frecuencia' => trim((string) ($detalle['frecuencia'] ?? 'N/E')),
                    'duracion' => trim((string) ($detalle['duracion'] ?? 'N/E')),
                    'cantidad_prescrita' => $cantidad,
                    'indicaciones' => $detalle['indicaciones'] ?? null,
                ]);
            }

            return $receta->load(['detalles.medicamento']);
        });
    }

    /**
     * Valida que los medicamentos de una venta que exigen receta cuenten con prescripciones válidas y vigentes.
     *
     * @param array<int, array{medicamento_id: int, cantidad: int}> $itemsVenta
     * @param array<int, int> $recetaIds Identificadores de recetas aportadas para la venta.
     * @param int $diasVigencia Días de vigencia permitidos para la receta a partir de su emisión.
     * @return bool
     *
     * @throws RecetaException
     */
    public function validarRecetaParaVenta(
        array $itemsVenta,
        array $recetaIds = [],
        int $diasVigencia = self::DIAS_VIGENCIA_DEFECTO
    ): bool {
        // 1. Filtrar medicamentos que requieren receta y totalizar cantidades solicitadas
        $cantidadesRequeridasPorMedicamento = [];
        foreach ($itemsVenta as $item) {
            $medicamentoId = $item['medicamento_id'];
            $cantidad = (int) $item['cantidad'];

            $medicamento = Medicamento::find($medicamentoId);
            if ($medicamento && $medicamento->requiere_receta) {
                $cantidadesRequeridasPorMedicamento[$medicamentoId] = ($cantidadesRequeridasPorMedicamento[$medicamentoId] ?? 0) + $cantidad;
            }
        }

        // Si ningún medicamento de la venta requiere receta, la validación pasa
        if (empty($cantidadesRequeridasPorMedicamento)) {
            return true;
        }

        // Si hay medicamentos que requieren receta pero no se adjuntó ninguna
        if (empty($recetaIds)) {
            throw new RecetaException(
                'La venta incluye medicamentos controlados o antibióticos que requieren receta médica obligatoria.'
            );
        }

        $recetas = Receta::with('detalles')->whereIn('id', $recetaIds)->get();
        if ($recetas->count() !== count(array_unique($recetaIds))) {
            throw new RecetaException('Una o más recetas médicas especificadas no existen en el sistema.');
        }

        $hoy = Carbon::today();

        // 2. Validar vigencia de cada receta
        foreach ($recetas as $receta) {
            $fechaEmision = Carbon::parse($receta->fecha_emision)->startOfDay();
            $diasTranscurridos = $fechaEmision->diffInDays($hoy, false);

            if ($diasTranscurridos < 0) {
                throw new RecetaException("La receta folio '{$receta->folio}' tiene una fecha de emisión futura.");
            }

            if ($diasTranscurridos > $diasVigencia) {
                throw new RecetaException(
                    "La receta folio '{$receta->folio}' ha caducado ({$diasTranscurridos} días transcurridos; límite: {$diasVigencia} días)."
                );
            }
        }

        // 3. Validar que las recetas cubran las cantidades prescritas para cada medicamento
        foreach ($cantidadesRequeridasPorMedicamento as $medicamentoId => $cantidadSolicitada) {
            $cantidadPrescritaTotal = 0;

            foreach ($recetas as $receta) {
                $cantidadPrescritaTotal += (int) $receta->detalles
                    ->where('medicamento_id', $medicamentoId)
                    ->sum('cantidad_prescrita');
            }

            if ($cantidadPrescritaTotal < $cantidadSolicitada) {
                $nombreMed = Medicamento::find($medicamentoId)?->nombre ?? "ID {$medicamentoId}";
                throw new RecetaException(
                    "La cantidad prescrita en las recetas ({$cantidadPrescritaTotal}) es menor que la cantidad solicitada ({$cantidadSolicitada}) para el medicamento '{$nombreMed}'."
                );
            }
        }

        return true;
    }

    /**
     * Vincula las recetas médicas autorizadas a una venta completada en la tabla pivot venta_receta.
     *
     * @param Venta|int $venta
     * @param array<int, int> $recetaIds
     * @return void
     */
    public function asociarRecetasAVenta(Venta|int $venta, array $recetaIds): void
    {
        if (empty($recetaIds)) {
            return;
        }

        $ventaModel = $venta instanceof Venta ? $venta : Venta::findOrFail($venta);
        $ventaModel->recetas()->syncWithoutDetaching($recetaIds);
    }
}
