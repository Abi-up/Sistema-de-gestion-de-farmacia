<?php

namespace App\Services;

use App\Exceptions\CompraException;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\DetalleRecepcion;
use App\Models\Lote;
use App\Models\Recepcion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecepcionService
{
    /**
     * Estados permitidos para una recepción física de mercancía.
     */
    public const ESTADOS_RECEPCION = [
        'RECIBIDO',
        'CON_OBSERVACIONES',
        'RECHAZADO',
    ];

    public function __construct(
        protected InventarioService $inventarioService,
        protected CompraService $compraService
    ) {
    }

    /**
     * Registra una recepción física de mercancía (total o parcial) vinculada a una compra.
     * Genera o actualiza lotes y registra movimientos de inventario de tipo ENTRADA.
     *
     * @param Compra|int $compra
     * @param User|int $usuario
     * @param array<int, array{
     *     detalle_compra_id: int,
     *     cantidad_recibida: int,
     *     numero_lote?: string,
     *     fecha_caducidad?: string,
     *     fecha_fabricacion?: string|null,
     *     observaciones?: string|null
     * }> $partidasRecibidas
     * @param string|null $observaciones
     * @param string $estadoRecepcion 'RECIBIDO', 'CON_OBSERVACIONES' o 'RECHAZADO'
     * @return Recepcion
     *
     * @throws CompraException
     * @throws InvalidArgumentException
     */
    public function registrarRecepcion(
        Compra|int $compra,
        User|int $usuario,
        array $partidasRecibidas,
        ?string $observaciones = null,
        string $estadoRecepcion = 'RECIBIDO'
    ): Recepcion {
        if (!in_array($estadoRecepcion, self::ESTADOS_RECEPCION, true)) {
            throw new InvalidArgumentException(
                "Estado de recepción inválido: '{$estadoRecepcion}'. Valores permitidos: " . implode(', ', self::ESTADOS_RECEPCION)
            );
        }

        if (empty($partidasRecibidas)) {
            throw new InvalidArgumentException('La recepción debe especificar al menos una partida recibida.');
        }

        $compraId = $compra instanceof Compra ? $compra->id : $compra;
        $usuarioModel = $usuario instanceof User ? $usuario : User::findOrFail($usuario);

        return DB::transaction(function () use (
            $compraId,
            $usuarioModel,
            $partidasRecibidas,
            $observaciones,
            $estadoRecepcion
        ): Recepcion {
            /** @var Compra $compraBloqueada */
            $compraBloqueada = Compra::where('id', $compraId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($compraBloqueada->estado === 'CANCELADA') {
                throw new CompraException("No se pueden registrar recepciones sobre una orden de compra CANCELADA (#{$compraBloqueada->id}).");
            }

            if ($compraBloqueada->estado === 'RECIBIDA') {
                throw new CompraException("La orden de compra #{$compraBloqueada->id} ya se encuentra totalmente RECIBIDA.");
            }

            $recepcion = Recepcion::create([
                'compra_id' => $compraBloqueada->id,
                'usuario_id' => $usuarioModel->id,
                'fecha_hora' => Carbon::now(),
                'estado' => $estadoRecepcion,
                'observaciones' => $observaciones,
            ]);

            foreach ($partidasRecibidas as $item) {
                $detalleCompraId = $item['detalle_compra_id'] ?? null;
                $cantidadRecibida = (int) ($item['cantidad_recibida'] ?? 0);

                if ($cantidadRecibida <= 0) {
                    throw new InvalidArgumentException("La cantidad recibida para la partida debe ser mayor a cero.");
                }

                /** @var DetalleCompra $detalleCompra */
                $detalleCompra = DetalleCompra::where('id', $detalleCompraId)
                    ->where('compra_id', $compraBloqueada->id)
                    ->lockForUpdate()
                    ->first();

                if (!$detalleCompra) {
                    throw new CompraException(
                        "La partida ID {$detalleCompraId} no pertenece a la orden de compra #{$compraBloqueada->id}."
                    );
                }

                // Validación anti-exceso de recepción
                $pendiente = $this->compraService->obtenerCantidadPendientePorDetalle($detalleCompra);
                if ($cantidadRecibida > $pendiente) {
                    throw new CompraException(
                        "La cantidad recibida ({$cantidadRecibida}) para el medicamento ID {$detalleCompra->medicamento_id} excede la cantidad pendiente por recibir ({$pendiente})."
                    );
                }

                $detalleRecepcion = DetalleRecepcion::create([
                    'recepcion_id' => $recepcion->id,
                    'detalle_compra_id' => $detalleCompra->id,
                    'cantidad_recibida' => $cantidadRecibida,
                    'observaciones' => $item['observaciones'] ?? null,
                ]);

                // Si la mercancía no fue rechazada físicamente en andén, ingresa al inventario
                if ($estadoRecepcion !== 'RECHAZADO') {
                    $numeroLote = trim((string) ($item['numero_lote'] ?? ''));
                    $fechaCaducidad = $item['fecha_caducidad'] ?? null;
                    $fechaFabricacion = $item['fecha_fabricacion'] ?? null;

                    if ($numeroLote === '') {
                        throw new CompraException("Debe especificarse el número de lote para la partida recibida.");
                    }

                    if (!$fechaCaducidad) {
                        throw new CompraException("Debe especificarse la fecha de caducidad para el lote '{$numeroLote}'.");
                    }

                    $caducidadCarbon = Carbon::parse($fechaCaducidad)->startOfDay();
                    if ($caducidadCarbon->lessThanOrEqualTo(Carbon::today())) {
                        throw new CompraException(
                            "No se puede recibir el lote '{$numeroLote}' porque su fecha de caducidad ({$caducidadCarbon->toDateString()}) ya venció o vence hoy."
                        );
                    }

                    $lote = Lote::where('medicamento_id', $detalleCompra->medicamento_id)
                        ->where('numero_lote', $numeroLote)
                        ->lockForUpdate()
                        ->first();

                    if (!$lote) {
                        $lote = Lote::create([
                            'medicamento_id' => $detalleCompra->medicamento_id,
                            'detalle_recepcion_id' => $detalleRecepcion->id,
                            'numero_lote' => $numeroLote,
                            'fecha_fabricacion' => $fechaFabricacion ? Carbon::parse($fechaFabricacion)->toDateString() : null,
                            'fecha_caducidad' => $caducidadCarbon->toDateString(),
                            'cantidad_inicial' => $cantidadRecibida,
                            'cantidad_disponible' => 0, // Se incrementará en el movimiento de inventario
                            'estado' => 'DISPONIBLE',
                        ]);
                    }

                    $this->inventarioService->registrarMovimiento(
                        $lote,
                        $usuarioModel,
                        'ENTRADA',
                        $cantidadRecibida,
                        "Recepción de compra #{$compraBloqueada->id} - Lote {$numeroLote}",
                        $detalleRecepcion->id
                    );
                }
            }

            if ($estadoRecepcion !== 'RECHAZADO') {
                $this->compraService->actualizarEstadoCompra($compraBloqueada);
            }

            return $recepcion->load(['detalles.lotes', 'usuario', 'compra']);
        });
    }
}
