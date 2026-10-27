<?php

namespace App\Services;

use App\Exceptions\CompraException;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\DetalleRecepcion;
use App\Models\Medicamento;
use App\Models\Proveedor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CompraService
{
    /**
     * Registra una nueva orden de compra a proveedor calculando subtotales y total en el servidor.
     *
     * @param Proveedor|int $proveedor
     * @param User|int $usuario
     * @param array<int, array{
     *     medicamento_id: int,
     *     cantidad_solicitada: int,
     *     precio_unitario: float|int|string
     * }> $partidas
     * @param string|null $fecha
     * @return Compra
     *
     * @throws CompraException
     * @throws InvalidArgumentException
     */
    public function crearOrdenCompra(
        Proveedor|int $proveedor,
        User|int $usuario,
        array $partidas,
        ?string $fecha = null
    ): Compra {
        $proveedorId = $proveedor instanceof Proveedor ? $proveedor->id : $proveedor;
        $usuarioId = $usuario instanceof User ? $usuario->id : $usuario;

        if (empty($partidas)) {
            throw new InvalidArgumentException('La orden de compra debe contener al menos una partida.');
        }

        if (!Proveedor::where('id', $proveedorId)->exists()) {
            throw new CompraException("El proveedor con ID {$proveedorId} no existe.");
        }

        if (!User::where('id', $usuarioId)->where('estado', 'ACTIVO')->exists()) {
            throw new CompraException("El usuario con ID {$usuarioId} no existe o se encuentra inactivo.");
        }

        return DB::transaction(function () use ($proveedorId, $usuarioId, $partidas, $fecha): Compra {
            $totalCompra = 0.00;
            $detallesParaInsertar = [];

            foreach ($partidas as $indice => $partida) {
                $medicamentoId = $partida['medicamento_id'] ?? null;
                $cantidad = (int) ($partida['cantidad_solicitada'] ?? 0);
                $precioUnitario = (float) ($partida['precio_unitario'] ?? 0.0);

                if (!$medicamentoId || !Medicamento::where('id', $medicamentoId)->where('activo', true)->exists()) {
                    throw new CompraException("El medicamento especificado en la partida #{$indice} no existe o no está activo.");
                }

                if ($cantidad <= 0) {
                    throw new InvalidArgumentException("La cantidad solicitada en la partida #{$indice} debe ser mayor a cero.");
                }

                if ($precioUnitario < 0) {
                    throw new InvalidArgumentException("El precio unitario en la partida #{$indice} no puede ser negativo.");
                }

                $subtotal = round($cantidad * $precioUnitario, 2);
                $totalCompra += $subtotal;

                $detallesParaInsertar[] = [
                    'medicamento_id' => $medicamentoId,
                    'cantidad_solicitada' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotal,
                ];
            }

            $compra = Compra::create([
                'proveedor_id' => $proveedorId,
                'usuario_id' => $usuarioId,
                'fecha' => $fecha ? Carbon::parse($fecha)->toDateString() : Carbon::today()->toDateString(),
                'estado' => 'PENDIENTE',
                'total' => round($totalCompra, 2),
            ]);

            foreach ($detallesParaInsertar as $detalle) {
                $compra->detalles()->create($detalle);
            }

            return $compra->load(['detalles.medicamento', 'proveedor', 'usuario']);
        });
    }

    /**
     * Calcula la cantidad física pendiente de recibir para un renglón de orden de compra.
     *
     * @param DetalleCompra $detalle
     * @return int
     */
    public function obtenerCantidadPendientePorDetalle(DetalleCompra $detalle): int
    {
        $recibido = (int) DetalleRecepcion::where('detalle_compra_id', $detalle->id)->sum('cantidad_recibida');

        return max(0, (int) $detalle->cantidad_solicitada - $recibido);
    }

    /**
     * Reevalúa y actualiza de forma coherente el estado de la compra según las cantidades recibidas.
     *
     * @param Compra $compra
     * @return string Estado resultante ('PENDIENTE', 'PARCIAL', 'RECIBIDA', 'CANCELADA')
     */
    public function actualizarEstadoCompra(Compra $compra): string
    {
        if ($compra->estado === 'CANCELADA') {
            return 'CANCELADA';
        }

        $detalles = $compra->detalles()->with('detallesRecepcion')->get();
        if ($detalles->isEmpty()) {
            return $compra->estado;
        }

        $totalSolicitado = $detalles->sum('cantidad_solicitada');
        $totalRecibido = 0;
        $todasPartidasCompletas = true;

        foreach ($detalles as $detalle) {
            $recibido = (int) $detalle->detallesRecepcion->sum('cantidad_recibida');
            $totalRecibido += $recibido;
            if ($recibido < $detalle->cantidad_solicitada) {
                $todasPartidasCompletas = false;
            }
        }

        if ($totalRecibido === 0) {
            $nuevoEstado = 'PENDIENTE';
        } elseif ($todasPartidasCompletas || $totalRecibido >= $totalSolicitado) {
            $nuevoEstado = 'RECIBIDA';
        } else {
            $nuevoEstado = 'PARCIAL';
        }

        if ($compra->estado !== $nuevoEstado) {
            $compra->estado = $nuevoEstado;
            $compra->save();
        }

        return $nuevoEstado;
    }

    /**
     * Cancela una orden de compra siempre que no haya tenido recepciones físicas previas.
     *
     * @param Compra|int $compra
     * @param User|int $usuario
     * @param string $motivo
     * @return Compra
     *
     * @throws CompraException
     */
    public function cancelarCompra(Compra|int $compra, User|int $usuario, string $motivo): Compra
    {
        $compraModel = $compra instanceof Compra ? $compra : Compra::findOrFail($compra);

        if ($compraModel->estado === 'CANCELADA') {
            throw new CompraException("La orden de compra #{$compraModel->id} ya se encuentra cancelada.");
        }

        if ($compraModel->estado === 'RECIBIDA' || $compraModel->estado === 'PARCIAL') {
            throw new CompraException(
                "No se puede cancelar la orden de compra #{$compraModel->id} porque ya cuenta con mercancía recibida."
            );
        }

        $compraModel->estado = 'CANCELADA';
        $compraModel->save();

        return $compraModel;
    }
}
