<?php

namespace App\Services;

use App\Exceptions\LoteNoAptoException;
use App\Exceptions\MovimientoInvalidoException;
use App\Exceptions\StockInsuficienteException;
use App\Models\DetalleDevolucion;
use App\Models\DetalleRecepcion;
use App\Models\DetalleVenta;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InventarioService
{
    /**
     * Tipos de movimiento permitidos en el sistema.
     */
    public const TIPOS_PERMITIDOS = [
        'ENTRADA',
        'SALIDA',
        'AJUSTE',
        'DEVOLUCION',
    ];

    /**
     * Días para considerar un lote como próximo a caducar.
     */
    public const DIAS_POR_CADUCAR = 30;

    /**
     * Registra un movimiento de inventario afectando atómicamente el lote y su kardex.
     *
     * @param Lote $lote Lote físico sobre el que se aplicará el movimiento.
     * @param User $usuario Usuario responsable de la operación.
     * @param string $tipoMovimiento Uno de: ENTRADA, SALIDA, AJUSTE, DEVOLUCION.
     * @param int $cantidad Cantidad a mover (positiva para ENTRADA, SALIDA, DEVOLUCION; firmada para AJUSTE).
     * @param string|null $motivo Justificación o descripción de la operación.
     * @param int|null $detalleRecepcionId Clave foránea opcional a detalles_recepcion.
     * @param int|null $detalleVentaId Clave foránea opcional a detalles_venta.
     * @param int|null $detalleDevolucionId Clave foránea opcional a detalles_devolucion.
     * @return MovimientoInventario
     *
     * @throws MovimientoInvalidoException Si el tipo, cantidad o referencias son inválidas.
     * @throws LoteNoAptoException Si el lote está caducado o en cuarentena.
     * @throws StockInsuficienteException Si no hay existencias suficientes para cubrir la salida o ajuste negativo.
     */
    public function registrarMovimiento(
        Lote $lote,
        User $usuario,
        string $tipoMovimiento,
        int $cantidad,
        ?string $motivo = null,
        ?int $detalleRecepcionId = null,
        ?int $detalleVentaId = null,
        ?int $detalleDevolucionId = null
    ): MovimientoInventario {
        $this->validarTipoYCantidad($tipoMovimiento, $cantidad);
        $this->validarReferencias($tipoMovimiento, $detalleRecepcionId, $detalleVentaId, $detalleDevolucionId);

        return DB::transaction(function () use (
            $lote,
            $usuario,
            $tipoMovimiento,
            $cantidad,
            $motivo,
            $detalleRecepcionId,
            $detalleVentaId,
            $detalleDevolucionId
        ): MovimientoInventario {
            /** @var Lote $loteBloqueado */
            $loteBloqueado = Lote::where('id', $lote->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validarAptitudLoteParaMovimiento($loteBloqueado, $tipoMovimiento, $cantidad);

            [$nuevaCantidad, $movimientoCantidad] = $this->calcularExistencias(
                $loteBloqueado,
                $tipoMovimiento,
                $cantidad
            );

            $this->actualizarLote($loteBloqueado, $nuevaCantidad);

            return MovimientoInventario::create([
                'lote_id' => $loteBloqueado->id,
                'usuario_id' => $usuario->id,
                'tipo_movimiento' => $tipoMovimiento,
                'cantidad' => $movimientoCantidad,
                'fecha_hora' => Carbon::now(),
                'motivo' => $motivo,
                'detalle_recepcion_id' => $detalleRecepcionId,
                'detalle_venta_id' => $detalleVentaId,
                'detalle_devolucion_id' => $detalleDevolucionId,
            ]);
        });
    }

    /**
     * Obtiene el stock vendible total de un medicamento sumando lotes disponibles.
     *
     * @param int|Medicamento $medicamento
     * @return int
     */
    public function obtenerStockTotal(int|Medicamento $medicamento): int
    {
        $medicamentoId = $medicamento instanceof Medicamento ? $medicamento->id : $medicamento;

        return (int) Lote::query()
            ->where('medicamento_id', $medicamentoId)
            ->whereIn('estado', ['DISPONIBLE', 'POR_CADUCAR'])
            ->where('fecha_caducidad', '>', Carbon::today())
            ->where('cantidad_disponible', '>', 0)
            ->sum('cantidad_disponible');
    }

    /**
     * Obtiene los lotes aptos para venta siguiendo la política FEFO (First Expired, First Out).
     *
     * @param int $medicamentoId
     * @param int $cantidadRequerida Cantidad opcional requerida para verificación.
     * @return Collection<int, Lote>
     */
    public function obtenerLotesDisponiblesFEFO(int $medicamentoId, int $cantidadRequerida = 0): Collection
    {
        $lotes = Lote::query()
            ->where('medicamento_id', $medicamentoId)
            ->whereIn('estado', ['DISPONIBLE', 'POR_CADUCAR'])
            ->where('fecha_caducidad', '>', Carbon::today())
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_caducidad', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($cantidadRequerida > 0) {
            $stockTotal = $lotes->sum('cantidad_disponible');
            if ($stockTotal < $cantidadRequerida) {
                throw new StockInsuficienteException(
                    "Stock insuficiente para el medicamento ID {$medicamentoId}. Requerido: {$cantidadRequerida}, Disponible: {$stockTotal}."
                );
            }
        }

        return $lotes;
    }

    /**
     * Valida que el tipo de movimiento y la cantidad cumplan las reglas básicas.
     *
     * @param string $tipoMovimiento
     * @param int $cantidad
     * @return void
     *
     * @throws MovimientoInvalidoException
     */
    protected function validarTipoYCantidad(string $tipoMovimiento, int $cantidad): void
    {
        if (!in_array($tipoMovimiento, self::TIPOS_PERMITIDOS, true)) {
            throw new MovimientoInvalidoException(
                "Tipo de movimiento no permitido: '{$tipoMovimiento}'. Valores permitidos: " . implode(', ', self::TIPOS_PERMITIDOS)
            );
        }

        if ($tipoMovimiento === 'AJUSTE') {
            if ($cantidad === 0) {
                throw new MovimientoInvalidoException('La cantidad para un movimiento de tipo AJUSTE no puede ser cero.');
            }
        } else {
            if ($cantidad <= 0) {
                throw new MovimientoInvalidoException("La cantidad para un movimiento de tipo {$tipoMovimiento} debe ser mayor a cero.");
            }
        }
    }

    /**
     * Valida que las claves foráneas de origen concuerden con el tipo de movimiento.
     *
     * @param string $tipoMovimiento
     * @param int|null $detalleRecepcionId
     * @param int|null $detalleVentaId
     * @param int|null $detalleDevolucionId
     * @return void
     *
     * @throws MovimientoInvalidoException
     */
    protected function validarReferencias(
        string $tipoMovimiento,
        ?int $detalleRecepcionId,
        ?int $detalleVentaId,
        ?int $detalleDevolucionId
    ): void {
        if ($detalleRecepcionId !== null) {
            if ($tipoMovimiento !== 'ENTRADA' && $tipoMovimiento !== 'AJUSTE') {
                throw new MovimientoInvalidoException(
                    "La referencia 'detalle_recepcion_id' solo está permitida para movimientos de tipo ENTRADA o AJUSTE."
                );
            }
            if (!DetalleRecepcion::where('id', $detalleRecepcionId)->exists()) {
                throw new MovimientoInvalidoException("El detalle de recepción con ID {$detalleRecepcionId} no existe en la base de datos.");
            }
        }

        if ($detalleVentaId !== null) {
            if ($tipoMovimiento !== 'SALIDA') {
                throw new MovimientoInvalidoException(
                    "La referencia 'detalle_venta_id' solo está permitida para movimientos de tipo SALIDA."
                );
            }
            if (!DetalleVenta::where('id', $detalleVentaId)->exists()) {
                throw new MovimientoInvalidoException("El detalle de venta con ID {$detalleVentaId} no existe en la base de datos.");
            }
        }

        if ($detalleDevolucionId !== null) {
            if ($tipoMovimiento !== 'DEVOLUCION') {
                throw new MovimientoInvalidoException(
                    "La referencia 'detalle_devolucion_id' solo está permitida para movimientos de tipo DEVOLUCION."
                );
            }
            if (!DetalleDevolucion::where('id', $detalleDevolucionId)->exists()) {
                throw new MovimientoInvalidoException("El detalle de devolución con ID {$detalleDevolucionId} no existe en la base de datos.");
            }
        }
    }

    /**
     * Verifica la aptitud del lote según su caducidad y estado para el tipo de movimiento.
     *
     * @param Lote $lote
     * @param string $tipoMovimiento
     * @param int $cantidad
     * @return void
     *
     * @throws LoteNoAptoException
     */
    protected function validarAptitudLoteParaMovimiento(Lote $lote, string $tipoMovimiento, int $cantidad): void
    {
        $fechaCaducidad = $lote->fecha_caducidad ? Carbon::parse($lote->fecha_caducidad)->startOfDay() : null;
        $hoy = Carbon::today();
        $estaVencidoPorFecha = $fechaCaducidad !== null && $fechaCaducidad->lessThanOrEqualTo($hoy);
        $estaCaducado = $lote->estado === 'CADUCADO' || $estaVencidoPorFecha;
        $estaEnCuarentena = $lote->estado === 'CUARENTENA';

        if ($estaEnCuarentena) {
            if ($tipoMovimiento === 'SALIDA') {
                throw new LoteNoAptoException(
                    "No se pueden realizar salidas del lote '{$lote->numero_lote}' porque se encuentra en CUARENTENA."
                );
            }
            if ($tipoMovimiento === 'ENTRADA' || $tipoMovimiento === 'DEVOLUCION') {
                throw new LoteNoAptoException(
                    "No se pueden registrar movimientos de {$tipoMovimiento} en un lote en CUARENTENA."
                );
            }
            if ($tipoMovimiento === 'AJUSTE' && $cantidad > 0) {
                throw new LoteNoAptoException(
                    "No se pueden ingresar existencias por AJUSTE a un lote en CUARENTENA."
                );
            }
        }

        if ($estaCaducado) {
            if ($tipoMovimiento === 'SALIDA') {
                throw new LoteNoAptoException(
                    "No se pueden realizar salidas del lote '{$lote->numero_lote}' porque se encuentra CADUCADO."
                );
            }
            if ($tipoMovimiento === 'ENTRADA') {
                throw new LoteNoAptoException(
                    "No se pueden ingresar mercancías a un lote CADUCADO ('{$lote->numero_lote}')."
                );
            }
            if ($tipoMovimiento === 'DEVOLUCION') {
                throw new LoteNoAptoException(
                    "No se pueden devolver productos al stock activo de un lote CADUCADO ('{$lote->numero_lote}'). Debe dirigirse a merma/destrucción."
                );
            }
            if ($tipoMovimiento === 'AJUSTE' && $cantidad > 0) {
                throw new LoteNoAptoException(
                    "No se pueden ingresar existencias positivas por AJUSTE a un lote CADUCADO."
                );
            }
        }
    }

    /**
     * Calcula la nueva cantidad de existencias y la cantidad a registrar en el movimiento.
     *
     * @param Lote $lote
     * @param string $tipoMovimiento
     * @param int $cantidad
     * @return array{0: int, 1: int} [nuevaCantidadDisponible, cantidadMovimiento]
     *
     * @throws StockInsuficienteException
     */
    protected function calcularExistencias(Lote $lote, string $tipoMovimiento, int $cantidad): array
    {
        $disponibleActual = (int) $lote->cantidad_disponible;

        switch ($tipoMovimiento) {
            case 'ENTRADA':
            case 'DEVOLUCION':
                $nuevaCantidad = $disponibleActual + $cantidad;
                $movimientoCantidad = $cantidad;
                break;

            case 'SALIDA':
                if ($disponibleActual < $cantidad) {
                    throw new StockInsuficienteException(
                        "Stock insuficiente en el lote '{$lote->numero_lote}'. Disponible: {$disponibleActual}, Solicitado: {$cantidad}."
                    );
                }
                $nuevaCantidad = $disponibleActual - $cantidad;
                $movimientoCantidad = -$cantidad;
                break;

            case 'AJUSTE':
                $nuevaCantidad = $disponibleActual + $cantidad;
                if ($nuevaCantidad < 0) {
                    throw new StockInsuficienteException(
                        "El ajuste de {$cantidad} unidades dejaría el lote '{$lote->numero_lote}' con existencias negativas ({$nuevaCantidad})."
                    );
                }
                $movimientoCantidad = $cantidad;
                break;

            default:
                throw new MovimientoInvalidoException("Tipo de movimiento desconocido: {$tipoMovimiento}.");
        }

        return [$nuevaCantidad, $movimientoCantidad];
    }

    /**
     * Actualiza la cantidad disponible y el estado del lote garantizando coherencia.
     *
     * @param Lote $lote
     * @param int $nuevaCantidad
     * @return void
     */
    protected function actualizarLote(Lote $lote, int $nuevaCantidad): void
    {
        $lote->cantidad_disponible = $nuevaCantidad;

        $fechaCaducidad = $lote->fecha_caducidad ? Carbon::parse($lote->fecha_caducidad)->startOfDay() : null;
        $hoy = Carbon::today();
        $estaVencidoPorFecha = $fechaCaducidad !== null && $fechaCaducidad->lessThanOrEqualTo($hoy);

        if ($lote->estado === 'CUARENTENA') {
            // Se respeta la condición de cuarentena sin alterar el estado administrativo
        } elseif ($lote->estado === 'CADUCADO' || $estaVencidoPorFecha) {
            $lote->estado = 'CADUCADO';
        } elseif ($nuevaCantidad === 0) {
            $lote->estado = 'AGOTADO';
        } else {
            // Cantidad positiva y no caducado
            $diasParaCaducar = $fechaCaducidad !== null ? (int) $hoy->diffInDays($fechaCaducidad, false) : 999;
            if ($diasParaCaducar <= self::DIAS_POR_CADUCAR && $diasParaCaducar >= 0) {
                $lote->estado = 'POR_CADUCAR';
            } else {
                $lote->estado = 'DISPONIBLE';
            }
        }

        $lote->save();
    }
}
