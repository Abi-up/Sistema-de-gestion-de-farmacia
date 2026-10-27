<?php

namespace App\Services;

use App\Exceptions\StockInsuficienteException;
use App\Exceptions\VentaException;
use App\Models\DetalleVenta;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaLote;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class VentaService
{
    public function __construct(
        protected InventarioService $inventarioService,
        protected RecetaService $recetaService
    ) {
    }

    /**
     * Procesa y registra una venta garantizando asignación FEFO, descuentos de stock atómicos,
     * cálculo de importes en el servidor y validación regulatoria de recetas.
     *
     * @param User|int $usuario Usuario o cajero responsable.
     * @param array<int, array{
     *     medicamento_id: int,
     *     cantidad: int,
     *     precio_unitario: float|int|string,
     *     descuento?: float|int|string|null
     * }> $items Renglones de productos a vender.
     * @param array<int, int> $recetaIds IDs de recetas médicas adjuntadas.
     * @param float $descuentoGlobal Descuento global aplicado a la venta.
     * @param float $tasaImpuesto Tasa de impuesto (ej. 0.16 para 16% o 0.0 para medicamentos exentos).
     * @return Venta
     *
     * @throws VentaException
     * @throws StockInsuficienteException
     * @throws InvalidArgumentException
     */
    public function procesarVenta(
        User|int $usuario,
        array $items,
        array $recetaIds = [],
        float $descuentoGlobal = 0.0,
        float $tasaImpuesto = 0.0
    ): Venta {
        if (empty($items)) {
            throw new InvalidArgumentException('La venta debe contener al menos un medicamento.');
        }

        $usuarioModel = $usuario instanceof User ? $usuario : User::findOrFail($usuario);
        if ($usuarioModel->estado !== 'ACTIVO') {
            throw new VentaException("El usuario '{$usuarioModel->usuario}' no está activo para registrar ventas.");
        }

        // 1. Validar recetas médicas obligatorias para medicamentos controlados
        $this->recetaService->validarRecetaParaVenta($items, $recetaIds);

        return DB::transaction(function () use (
            $usuarioModel,
            $items,
            $recetaIds,
            $descuentoGlobal,
            $tasaImpuesto
        ): Venta {
            $folio = $this->generarFolioVenta();

            /** @var Venta $venta */
            $venta = Venta::create([
                'usuario_id' => $usuarioModel->id,
                'folio' => $folio,
                'fecha_hora' => Carbon::now(),
                'subtotal' => 0.00,
                'descuento' => 0.00,
                'impuestos' => 0.00,
                'total' => 0.00,
                'estado' => 'COMPLETADA',
            ]);

            $subtotalBrutoAcumulado = 0.00;
            $descuentoLineasAcumulado = 0.00;

            foreach ($items as $indice => $item) {
                $medicamentoId = $item['medicamento_id'] ?? null;
                $cantidadSolicitada = (int) ($item['cantidad'] ?? 0);
                $precioUnitario = (float) ($item['precio_unitario'] ?? 0.0);
                $descuentoLinea = (float) ($item['descuento'] ?? 0.0);

                if ($cantidadSolicitada <= 0) {
                    throw new InvalidArgumentException("La cantidad solicitada en la partida #{$indice} debe ser mayor a cero.");
                }

                if ($precioUnitario < 0) {
                    throw new InvalidArgumentException("El precio unitario en la partida #{$indice} no puede ser negativo.");
                }

                if ($descuentoLinea < 0) {
                    throw new InvalidArgumentException("El descuento en la partida #{$indice} no puede ser negativo.");
                }

                /** @var Medicamento|null $medicamento */
                $medicamento = Medicamento::find($medicamentoId);
                if (!$medicamento || !$medicamento->activo) {
                    throw new VentaException("El medicamento con ID {$medicamentoId} no existe o no está activo para venta.");
                }

                // 2. Buscar lotes disponibles con estrategia FEFO aplicando bloqueo pesimista
                $lotes = Lote::where('medicamento_id', $medicamento->id)
                    ->whereIn('estado', ['DISPONIBLE', 'POR_CADUCAR'])
                    ->where('fecha_caducidad', '>', Carbon::today())
                    ->where('cantidad_disponible', '>', 0)
                    ->orderBy('fecha_caducidad', 'asc')
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->get();

                $stockTotalDisponible = $lotes->sum('cantidad_disponible');
                if ($stockTotalDisponible < $cantidadSolicitada) {
                    throw new StockInsuficienteException(
                        "Stock insuficiente para '{$medicamento->nombre}'. Solicitado: {$cantidadSolicitada}, Disponible: {$stockTotalDisponible}."
                    );
                }

                $subtotalLineaBruto = round($cantidadSolicitada * $precioUnitario, 2);
                $descuentoLineaEfectivo = min($descuentoLinea, $subtotalLineaBruto);
                $subtotalLineaNeto = round($subtotalLineaBruto - $descuentoLineaEfectivo, 2);

                $subtotalBrutoAcumulado += $subtotalLineaBruto;
                $descuentoLineasAcumulado += $descuentoLineaEfectivo;

                /** @var DetalleVenta $detalleVenta */
                $detalleVenta = DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'medicamento_id' => $medicamento->id,
                    'cantidad' => $cantidadSolicitada,
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $descuentoLineaEfectivo,
                    'subtotal' => $subtotalLineaNeto,
                ]);

                // 3. Dispensación por lotes (FEFO)
                $unidadesPendientes = $cantidadSolicitada;
                foreach ($lotes as $lote) {
                    if ($unidadesPendientes <= 0) {
                        break;
                    }

                    $unidadesDeEsteLote = min($unidadesPendientes, $lote->cantidad_disponible);

                    // Registrar vínculo exacto en ventas_lotes
                    VentaLote::create([
                        'detalle_venta_id' => $detalleVenta->id,
                        'lote_id' => $lote->id,
                        'cantidad' => $unidadesDeEsteLote,
                    ]);

                    // Descontar inventario y registrar movimiento en Kardex
                    $this->inventarioService->registrarMovimiento(
                        $lote,
                        $usuarioModel,
                        'SALIDA',
                        $unidadesDeEsteLote,
                        "Venta ticket {$venta->folio} - {$medicamento->nombre}",
                        null,
                        $detalleVenta->id
                    );

                    $unidadesPendientes -= $unidadesDeEsteLote;
                }
            }

            // 4. Cálculo final de importes en el servidor
            $descuentoTotal = round($descuentoLineasAcumulado + max(0.0, $descuentoGlobal), 2);
            $baseImponible = max(0.00, round($subtotalBrutoAcumulado - $descuentoTotal, 2));
            $impuestos = round($baseImponible * max(0.0, $tasaImpuesto), 2);
            $totalFinal = round($baseImponible + $impuestos, 2);

            $venta->subtotal = $subtotalBrutoAcumulado;
            $venta->descuento = $descuentoTotal;
            $venta->impuestos = $impuestos;
            $venta->total = $totalFinal;
            $venta->save();

            // 5. Vincular recetas médicas aportadas
            if (!empty($recetaIds)) {
                $this->recetaService->asociarRecetasAVenta($venta, $recetaIds);
            }

            return $venta->load(['detalles.ventasLotes.lote', 'detalles.medicamento', 'recetas', 'usuario']);
        });
    }

    /**
     * Cancela una venta previamente completada y reintegra el stock a sus respectivos lotes.
     *
     * @param Venta|int $venta
     * @param User|int $usuario
     * @param string $motivo
     * @return Venta
     *
     * @throws VentaException
     */
    public function cancelarVenta(Venta|int $venta, User|int $usuario, string $motivo): Venta
    {
        $ventaId = $venta instanceof Venta ? $venta->id : $venta;
        $usuarioModel = $usuario instanceof User ? $usuario : User::findOrFail($usuario);

        return DB::transaction(function () use ($ventaId, $usuarioModel, $motivo): Venta {
            /** @var Venta $ventaBloqueada */
            $ventaBloqueada = Venta::where('id', $ventaId)->lockForUpdate()->firstOrFail();

            if ($ventaBloqueada->estado === 'CANCELADA') {
                throw new VentaException("La venta folio '{$ventaBloqueada->folio}' ya se encuentra cancelada.");
            }

            if (in_array($ventaBloqueada->estado, ['DEVUELTA_PARCIAL', 'DEVUELTA_TOTAL'], true)) {
                throw new VentaException(
                    "No se puede cancelar la venta folio '{$ventaBloqueada->folio}' porque tiene devoluciones procesadas."
                );
            }

            // Reintegrar stock por cada lote despachado
            $detalles = $ventaBloqueada->detalles()->with('ventasLotes.lote')->get();

            foreach ($detalles as $detalle) {
                foreach ($detalle->ventasLotes as $ventaLote) {
                    $lote = $ventaLote->lote;
                    if ($lote) {
                        $this->inventarioService->registrarMovimiento(
                            $lote,
                            $usuarioModel,
                            'ENTRADA',
                            $ventaLote->cantidad,
                            "Cancelación de venta folio {$ventaBloqueada->folio}: {$motivo}"
                        );
                    }
                }
            }

            $ventaBloqueada->estado = 'CANCELADA';
            $ventaBloqueada->save();

            return $ventaBloqueada;
        });
    }

    /**
     * Genera un folio único consecutivo para el ticket de venta.
     *
     * @return string
     */
    protected function generarFolioVenta(): string
    {
        $fecha = Carbon::now()->format('Ymd');
        $random = strtoupper(Str::random(6));

        $folio = "V-{$fecha}-{$random}";
        while (Venta::where('folio', $folio)->exists()) {
            $random = strtoupper(Str::random(6));
            $folio = "V-{$fecha}-{$random}";
        }

        return $folio;
    }
}
