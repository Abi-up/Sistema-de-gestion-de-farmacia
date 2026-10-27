<?php

namespace App\Services;

use App\Exceptions\DevolucionException;
use App\Models\DetalleDevolucion;
use App\Models\Devolucion;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaLote;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DevolucionService
{
    /**
     * Destinos permitidos para el producto devuelto.
     */
    public const DESTINOS_PERMITIDOS = [
        'REINGRESADO_INVENTARIO',
        'MERMA_DESTRUIDO',
    ];

    /**
     * Estados permitidos para el registro de devolución.
     */
    public const ESTADOS_PERMITIDOS = [
        'SOLICITADA',
        'APROBADA',
        'RECHAZADA',
        'PROCESADA',
    ];

    public function __construct(
        protected InventarioService $inventarioService
    ) {
    }

    /**
     * Procesa una devolución de venta validando límites exactos por lote,
     * canalización a inventario o merma y transición de estado de la venta.
     *
     * @param Venta|int $venta Venta original.
     * @param User|int $usuario Usuario que procesa la devolución.
     * @param string $motivo Causa global de la devolución.
     * @param array<int, array{
     *     venta_lote_id: int,
     *     cantidad: int,
     *     motivo?: string|null,
     *     destino_producto: string
     * }> $partidasDevueltas Renglones a devolver.
     * @param string|null $observaciones Observaciones adicionales.
     * @param string $estado 'PROCESADA' o 'APROBADA'
     * @return Devolucion
     *
     * @throws DevolucionException
     * @throws InvalidArgumentException
     */
    public function procesarDevolucion(
        Venta|int $venta,
        User|int $usuario,
        string $motivo,
        array $partidasDevueltas,
        ?string $observaciones = null,
        string $estado = 'PROCESADA'
    ): Devolucion {
        if (!in_array($estado, self::ESTADOS_PERMITIDOS, true)) {
            throw new InvalidArgumentException("Estado de devolución inválido: '{$estado}'.");
        }

        if (empty($partidasDevueltas)) {
            throw new InvalidArgumentException('La solicitud de devolución debe incluir al menos un renglón.');
        }

        $motivoGlobal = trim($motivo);
        if ($motivoGlobal === '') {
            throw new InvalidArgumentException('Debe indicarse un motivo para la devolución.');
        }

        $ventaId = $venta instanceof Venta ? $venta->id : $venta;
        $usuarioModel = $usuario instanceof User ? $usuario : User::findOrFail($usuario);

        return DB::transaction(function () use (
            $ventaId,
            $usuarioModel,
            $motivoGlobal,
            $partidasDevueltas,
            $observaciones,
            $estado
        ): Devolucion {
            /** @var Venta $ventaBloqueada */
            $ventaBloqueada = Venta::where('id', $ventaId)->lockForUpdate()->firstOrFail();

            if ($ventaBloqueada->estado === 'CANCELADA') {
                throw new DevolucionException("No se pueden procesar devoluciones sobre una venta CANCELADA (#{$ventaBloqueada->folio}).");
            }

            if ($ventaBloqueada->estado === 'DEVUELTA_TOTAL') {
                throw new DevolucionException("La venta #{$ventaBloqueada->folio} ya fue devuelta en su totalidad.");
            }

            $devolucion = Devolucion::create([
                'venta_id' => $ventaBloqueada->id,
                'usuario_id' => $usuarioModel->id,
                'fecha_hora' => Carbon::now(),
                'motivo' => $motivoGlobal,
                'estado' => $estado,
                'observaciones' => $observaciones,
            ]);

            foreach ($partidasDevueltas as $indice => $item) {
                $ventaLoteId = $item['venta_lote_id'] ?? null;
                $cantidadADevolver = (int) ($item['cantidad'] ?? 0);
                $destino = $item['destino_producto'] ?? '';
                $motivoPartida = trim((string) ($item['motivo'] ?? $motivoGlobal));

                if ($cantidadADevolver <= 0) {
                    throw new InvalidArgumentException("La cantidad devuelta en la partida #{$indice} debe ser mayor a cero.");
                }

                if (!in_array($destino, self::DESTINOS_PERMITIDOS, true)) {
                    throw new InvalidArgumentException(
                        "Destino de producto inválido en la partida #{$indice}: '{$destino}'. Valores permitidos: " . implode(', ', self::DESTINOS_PERMITIDOS)
                    );
                }

                /** @var VentaLote|null $ventaLote */
                $ventaLote = VentaLote::with(['lote', 'detalleVenta'])
                    ->where('id', $ventaLoteId)
                    ->lockForUpdate()
                    ->first();

                if (!$ventaLote || $ventaLote->detalleVenta->venta_id !== $ventaBloqueada->id) {
                    throw new DevolucionException(
                        "El registro de venta-lote ID {$ventaLoteId} no pertenece a la venta #{$ventaBloqueada->folio}."
                    );
                }

                // Validación estricta anti-sobredevolución
                $devueltoPrevio = (int) DetalleDevolucion::where('venta_lote_id', $ventaLote->id)
                    ->whereHas('devolucion', function ($q): void {
                        $q->whereIn('estado', ['APROBADA', 'PROCESADA']);
                    })
                    ->sum('cantidad');

                $maxPermitido = (int) $ventaLote->cantidad - $devueltoPrevio;
                if ($cantidadADevolver > $maxPermitido) {
                    throw new DevolucionException(
                        "La cantidad solicitada a devolver ({$cantidadADevolver}) para el lote '{$ventaLote->lote->numero_lote}' excede el remanente devuelto permitido ({$maxPermitido})."
                    );
                }

                $detalleDevolucion = DetalleDevolucion::create([
                    'devolucion_id' => $devolucion->id,
                    'venta_lote_id' => $ventaLote->id,
                    'cantidad' => $cantidadADevolver,
                    'motivo' => $motivoPartida,
                    'destino_producto' => $destino,
                ]);

                // Gestión de inventario según destino del producto
                if (in_array($estado, ['PROCESADA', 'APROBADA'], true)) {
                    if ($destino === 'REINGRESADO_INVENTARIO') {
                        $lote = $ventaLote->lote;
                        if (!$lote) {
                            throw new DevolucionException("No se encontró el lote físico correspondiente a la venta.");
                        }

                        // No reingresar al inventario vendible si el lote ya venció o está en cuarentena
                        $caducidad = $lote->fecha_caducidad ? Carbon::parse($lote->fecha_caducidad)->startOfDay() : null;
                        if ($caducidad && $caducidad->lessThanOrEqualTo(Carbon::today())) {
                            throw new DevolucionException(
                                "El producto devuelto del lote '{$lote->numero_lote}' ya caducó ({$caducidad->toDateString()}). No puede reingresarse a inventario; debe marcarse como MERMA_DESTRUIDO."
                            );
                        }

                        if ($lote->estado === 'CUARENTENA') {
                            throw new DevolucionException(
                                "El lote '{$lote->numero_lote}' está en CUARENTENA y no admite reingresos directos a stock vendible."
                            );
                        }

                        $this->inventarioService->registrarMovimiento(
                            $lote,
                            $usuarioModel,
                            'DEVOLUCION',
                            $cantidadADevolver,
                            "Devolución ticket {$ventaBloqueada->folio}: {$motivoPartida}",
                            null,
                            null,
                            $detalleDevolucion->id
                        );
                    }
                    // Si el destino es 'MERMA_DESTRUIDO', no se reingresa al inventario vendible.
                }
            }

            // Actualizar estado de la venta padre
            if (in_array($estado, ['PROCESADA', 'APROBADA'], true)) {
                $this->actualizarEstadoVentaPorDevolucion($ventaBloqueada);
            }

            return $devolucion->load(['detalles.ventaLote.lote', 'usuario', 'venta']);
        });
    }

    /**
     * Determina si la venta fue devuelta de forma parcial o total y actualiza su estado.
     *
     * @param Venta $venta
     * @return void
     */
    protected function actualizarEstadoVentaPorDevolucion(Venta $venta): void
    {
        $detallesVenta = $venta->detalles()->with('ventasLotes')->get();

        $totalUnidadesVendidas = 0;
        foreach ($detallesVenta as $detalle) {
            $totalUnidadesVendidas += (int) $detalle->ventasLotes->sum('cantidad');
        }

        $totalUnidadesDevueltas = (int) DetalleDevolucion::whereHas('ventaLote.detalleVenta', function ($q) use ($venta): void {
            $q->where('venta_id', $venta->id);
        })->whereHas('devolucion', function ($q): void {
            $q->whereIn('estado', ['APROBADA', 'PROCESADA']);
        })->sum('cantidad');

        if ($totalUnidadesDevueltas >= $totalUnidadesVendidas && $totalUnidadesVendidas > 0) {
            $venta->estado = 'DEVUELTA_TOTAL';
        } elseif ($totalUnidadesDevueltas > 0) {
            $venta->estado = 'DEVUELTA_PARCIAL';
        }

        $venta->save();
    }
}
