<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\DetalleDevolucion;
use App\Models\DetalleVenta;
use App\Models\Devolucion;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    /**
     * Reporte consolidado de existencias actuales por medicamento.
     *
     * @param int|null $categoriaId
     * @return array<int, array{
     *     medicamento_id: int,
     *     nombre: string,
     *     codigo_barras: string|null,
     *     categoria: string,
     *     stock_total_disponible: int,
     *     lotes_activos: int,
     *     proxima_caducidad: string|null
     * }>
     */
    public function reporteExistencias(?int $categoriaId = null): array
    {
        $hoy = Carbon::today()->toDateString();

        $medicamentos = Medicamento::query()
            ->with(['categoria', 'lotes' => function ($query) use ($hoy): void {
                $query->whereIn('estado', ['DISPONIBLE', 'POR_CADUCAR'])
                    ->where('cantidad_disponible', '>', 0)
                    ->where('fecha_caducidad', '>', $hoy)
                    ->orderBy('fecha_caducidad', 'asc');
            }])
            ->when($categoriaId !== null, fn ($q) => $q->where('categoria_id', $categoriaId))
            ->where('activo', true)
            ->get();

        $reporte = [];
        foreach ($medicamentos as $med) {
            $stock = (int) $med->lotes->sum('cantidad_disponible');
            $proximaCaducidad = $med->lotes->first()?->fecha_caducidad;

            $reporte[] = [
                'medicamento_id' => $med->id,
                'nombre' => $med->nombre,
                'codigo_barras' => $med->codigo_barras,
                'categoria' => $med->categoria->nombre ?? 'N/A',
                'stock_total_disponible' => $stock,
                'lotes_activos' => $med->lotes->count(),
                'proxima_caducidad' => $proximaCaducidad ? Carbon::parse($proximaCaducidad)->toDateString() : null,
            ];
        }

        return $reporte;
    }

    /**
     * Reporte de lotes caducados y próximos a caducar.
     *
     * @param int $diasAlerta
     * @return array{
     *     resumen: array{caducados_count: int, caducados_stock: int, por_caducar_count: int, por_caducar_stock: int},
     *     lotes_caducados: array<int, mixed>,
     *     lotes_por_caducar: array<int, mixed>
     * }
     */
    public function reporteLotesPorCaducarYCaducados(int $diasAlerta = 30): array
    {
        $hoy = Carbon::today()->toDateString();
        $limite = Carbon::today()->addDays($diasAlerta)->toDateString();

        $caducados = Lote::with('medicamento')
            ->where(function ($q) use ($hoy): void {
                $q->where('fecha_caducidad', '<=', $hoy)
                    ->orWhere('estado', 'CADUCADO');
            })
            ->orderBy('fecha_caducidad', 'asc')
            ->get();

        $porCaducar = Lote::with('medicamento')
            ->where('cantidad_disponible', '>', 0)
            ->where('estado', '!=', 'CUARENTENA')
            ->where('fecha_caducidad', '>', $hoy)
            ->where('fecha_caducidad', '<=', $limite)
            ->orderBy('fecha_caducidad', 'asc')
            ->get();

        return [
            'resumen' => [
                'caducados_count' => $caducados->count(),
                'caducados_stock' => (int) $caducados->sum('cantidad_disponible'),
                'por_caducar_count' => $porCaducar->count(),
                'por_caducar_stock' => (int) $porCaducar->sum('cantidad_disponible'),
            ],
            'lotes_caducados' => $caducados->map(fn ($l) => [
                'lote_id' => $l->id,
                'numero_lote' => $l->numero_lote,
                'medicamento' => $l->medicamento->nombre ?? 'N/A',
                'fecha_caducidad' => $l->fecha_caducidad ? Carbon::parse($l->fecha_caducidad)->toDateString() : null,
                'cantidad_disponible' => $l->cantidad_disponible,
                'estado' => $l->estado,
            ])->all(),
            'lotes_por_caducar' => $porCaducar->map(fn ($l) => [
                'lote_id' => $l->id,
                'numero_lote' => $l->numero_lote,
                'medicamento' => $l->medicamento->nombre ?? 'N/A',
                'fecha_caducidad' => $l->fecha_caducidad ? Carbon::parse($l->fecha_caducidad)->toDateString() : null,
                'dias_restantes' => Carbon::today()->diffInDays(Carbon::parse($l->fecha_caducidad), false),
                'cantidad_disponible' => $l->cantidad_disponible,
            ])->all(),
        ];
    }

    /**
     * Reporte de kardex e historial de movimientos de inventario por periodo.
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param string|null $tipoMovimiento
     * @return array{
     *     totales_por_tipo: array<string, int>,
     *     movimientos: array<int, mixed>
     * }
     */
    public function reporteMovimientosInventario(
        string $fechaInicio,
        string $fechaFin,
        ?string $tipoMovimiento = null
    ): array {
        $desde = Carbon::parse($fechaInicio)->startOfDay();
        $hasta = Carbon::parse($fechaFin)->endOfDay();

        $query = MovimientoInventario::with(['lote.medicamento', 'usuario'])
            ->whereBetween('fecha_hora', [$desde, $hasta])
            ->when($tipoMovimiento !== null, fn ($q) => $q->where('tipo_movimiento', $tipoMovimiento))
            ->orderBy('fecha_hora', 'desc');

        $movimientos = $query->get();

        $totales = [
            'ENTRADA' => 0,
            'SALIDA' => 0,
            'AJUSTE' => 0,
            'DEVOLUCION' => 0,
        ];

        foreach ($movimientos as $mov) {
            $tipo = $mov->tipo_movimiento;
            if (isset($totales[$tipo])) {
                $totales[$tipo] += abs((int) $mov->cantidad);
            }
        }

        return [
            'totales_por_tipo' => $totales,
            'movimientos' => $movimientos->map(fn ($m) => [
                'id' => $m->id,
                'fecha_hora' => $m->fecha_hora ? Carbon::parse($m->fecha_hora)->toDateTimeString() : null,
                'tipo_movimiento' => $m->tipo_movimiento,
                'cantidad' => $m->cantidad,
                'medicamento' => $m->lote->medicamento->nombre ?? 'N/A',
                'numero_lote' => $m->lote->numero_lote ?? 'N/A',
                'usuario' => $m->usuario->usuario ?? 'N/A',
                'motivo' => $m->motivo,
            ])->all(),
        ];
    }

    /**
     * Reporte financiero y operativo de ventas por periodo y cajero.
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param int|null $usuarioId
     * @return array{
     *     conteo_ventas: int,
     *     subtotal_bruto: float,
     *     descuentos: float,
     *     impuestos: float,
     *     total_neto: float,
     *     ventas: array<int, mixed>
     * }
     */
    public function reporteVentasPorPeriodo(
        string $fechaInicio,
        string $fechaFin,
        ?int $usuarioId = null
    ): array {
        $desde = Carbon::parse($fechaInicio)->startOfDay();
        $hasta = Carbon::parse($fechaFin)->endOfDay();

        $ventas = Venta::with('usuario')
            ->whereBetween('fecha_hora', [$desde, $hasta])
            ->when($usuarioId !== null, fn ($q) => $q->where('usuario_id', $usuarioId))
            ->where('estado', '!=', 'CANCELADA')
            ->orderBy('fecha_hora', 'desc')
            ->get();

        return [
            'conteo_ventas' => $ventas->count(),
            'subtotal_bruto' => (float) round($ventas->sum('subtotal'), 2),
            'descuentos' => (float) round($ventas->sum('descuento'), 2),
            'impuestos' => (float) round($ventas->sum('impuestos'), 2),
            'total_neto' => (float) round($ventas->sum('total'), 2),
            'ventas' => $ventas->map(fn ($v) => [
                'id' => $v->id,
                'folio' => $v->folio,
                'fecha_hora' => $v->fecha_hora ? Carbon::parse($v->fecha_hora)->toDateTimeString() : null,
                'cajero' => $v->usuario->usuario ?? 'N/A',
                'total' => (float) $v->total,
                'estado' => $v->estado,
            ])->all(),
        ];
    }

    /**
     * Reporte de los productos más vendidos en un rango de fechas.
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param int $limite
     * @return array<int, array{
     *     medicamento_id: int,
     *     nombre: string,
     *     unidades_vendidas: int,
     *     monto_generado: float
     * }>
     */
    public function reporteProductosMasVendidos(
        string $fechaInicio,
        string $fechaFin,
        int $limite = 10
    ): array {
        $desde = Carbon::parse($fechaInicio)->startOfDay();
        $hasta = Carbon::parse($fechaFin)->endOfDay();

        return DetalleVenta::query()
            ->join('ventas', 'detalles_venta.venta_id', '=', 'ventas.id')
            ->join('medicamentos', 'detalles_venta.medicamento_id', '=', 'medicamentos.id')
            ->whereBetween('ventas.fecha_hora', [$desde, $hasta])
            ->where('ventas.estado', '!=', 'CANCELADA')
            ->select(
                'medicamentos.id as medicamento_id',
                'medicamentos.nombre as nombre',
                DB::raw('SUM(detalles_venta.cantidad) as total_unidades'),
                DB::raw('SUM(detalles_venta.subtotal) as total_monto')
            )
            ->groupBy('medicamentos.id', 'medicamentos.nombre')
            ->orderByDesc('total_unidades')
            ->limit(max(1, min(100, $limite)))
            ->get()
            ->map(fn ($row) => [
                'medicamento_id' => (int) $row->medicamento_id,
                'nombre' => (string) $row->nombre,
                'unidades_vendidas' => (int) $row->total_unidades,
                'monto_generado' => (float) round($row->total_monto, 2),
            ])
            ->all();
    }

    /**
     * Reporte de órdenes de compra con recepciones pendientes.
     *
     * @return array<int, array{
     *     compra_id: int,
     *     proveedor: string,
     *     fecha: string,
     *     estado: string,
     *     total: float,
     *     partidas_pendientes: array<int, mixed>
     * }>
     */
    public function reporteComprasYRecepcionesPendientes(): array
    {
        $compras = Compra::with(['proveedor', 'detalles.detallesRecepcion', 'detalles.medicamento'])
            ->whereIn('estado', ['PENDIENTE', 'PARCIAL'])
            ->orderBy('fecha', 'asc')
            ->get();

        $reporte = [];
        foreach ($compras as $compra) {
            $partidasPendientes = [];

            foreach ($compra->detalles as $detalle) {
                $recibido = (int) $detalle->detallesRecepcion->sum('cantidad_recibida');
                $pendiente = (int) $detalle->cantidad_solicitada - $recibido;

                if ($pendiente > 0) {
                    $partidasPendientes[] = [
                        'detalle_compra_id' => $detalle->id,
                        'medicamento' => $detalle->medicamento->nombre ?? 'N/A',
                        'cantidad_solicitada' => $detalle->cantidad_solicitada,
                        'cantidad_recibida' => $recibido,
                        'cantidad_pendiente' => $pendiente,
                    ];
                }
            }

            $reporte[] = [
                'compra_id' => $compra->id,
                'proveedor' => $compra->proveedor->nombre ?? 'N/A',
                'fecha' => Carbon::parse($compra->fecha)->toDateString(),
                'estado' => $compra->estado,
                'total' => (float) $compra->total,
                'partidas_pendientes' => $partidasPendientes,
            ];
        }

        return $reporte;
    }

    /**
     * Reporte de devoluciones tramitadas por periodo.
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array{
     *     total_devoluciones: int,
     *     unidades_reingresadas: int,
     *     unidades_merma: int,
     *     devoluciones: array<int, mixed>
     * }
     */
    public function reporteDevoluciones(string $fechaInicio, string $fechaFin): array
    {
        $desde = Carbon::parse($fechaInicio)->startOfDay();
        $hasta = Carbon::parse($fechaFin)->endOfDay();

        $devoluciones = Devolucion::with(['detalles.ventaLote.lote.medicamento', 'usuario', 'venta'])
            ->whereBetween('fecha_hora', [$desde, $hasta])
            ->orderBy('fecha_hora', 'desc')
            ->get();

        $unidadesReingresadas = (int) DetalleDevolucion::whereHas('devolucion', fn ($q) => $q->whereBetween('fecha_hora', [$desde, $hasta]))
            ->where('destino_producto', 'REINGRESADO_INVENTARIO')
            ->sum('cantidad');

        $unidadesMerma = (int) DetalleDevolucion::whereHas('devolucion', fn ($q) => $q->whereBetween('fecha_hora', [$desde, $hasta]))
            ->where('destino_producto', 'MERMA_DESTRUIDO')
            ->sum('cantidad');

        return [
            'total_devoluciones' => $devoluciones->count(),
            'unidades_reingresadas' => $unidadesReingresadas,
            'unidades_merma' => $unidadesMerma,
            'devoluciones' => $devoluciones->map(fn ($d) => [
                'id' => $d->id,
                'venta_folio' => $d->venta->folio ?? 'N/A',
                'cajero' => $d->usuario->usuario ?? 'N/A',
                'fecha_hora' => $d->fecha_hora ? Carbon::parse($d->fecha_hora)->toDateTimeString() : null,
                'motivo' => $d->motivo,
                'estado' => $d->estado,
                'total_partidas' => $d->detalles->count(),
            ])->all(),
        ];
    }

    /**
     * Trazabilidad completa de un lote desde su origen hasta sus salidas o devoluciones.
     *
     * @param int $loteId
     * @return array{
     *     lote: array<string, mixed>,
     *     origen_recepcion: array<string, mixed>|null,
     *     movimientos: array<int, mixed>,
     *     ventas_asociadas: array<int, mixed>
     * }
     */
    public function reporteTrazabilidadLote(int $loteId): array
    {
        $lote = Lote::with([
            'medicamento',
            'detalleRecepcion.recepcion.compra.proveedor',
            'movimientosInventario.usuario',
            'ventasLotes.detalleVenta.venta',
        ])->findOrFail($loteId);

        $recepcionOrigen = null;
        if ($lote->detalleRecepcion) {
            $rec = $lote->detalleRecepcion->recepcion;
            $recepcionOrigen = [
                'recepcion_id' => $rec->id,
                'fecha_recepcion' => $rec->fecha_hora ? Carbon::parse($rec->fecha_hora)->toDateTimeString() : null,
                'orden_compra_id' => $rec->compra_id,
                'proveedor' => $rec->compra->proveedor->nombre ?? 'N/A',
            ];
        }

        return [
            'lote' => [
                'id' => $lote->id,
                'numero_lote' => $lote->numero_lote,
                'medicamento' => $lote->medicamento->nombre ?? 'N/A',
                'cantidad_inicial' => $lote->cantidad_inicial,
                'cantidad_disponible' => $lote->cantidad_disponible,
                'fecha_fabricacion' => $lote->fecha_fabricacion ? Carbon::parse($lote->fecha_fabricacion)->toDateString() : null,
                'fecha_caducidad' => $lote->fecha_caducidad ? Carbon::parse($lote->fecha_caducidad)->toDateString() : null,
                'estado' => $lote->estado,
            ],
            'origen_recepcion' => $recepcionOrigen,
            'movimientos' => $lote->movimientosInventario->map(fn ($m) => [
                'id' => $m->id,
                'fecha_hora' => $m->fecha_hora ? Carbon::parse($m->fecha_hora)->toDateTimeString() : null,
                'tipo' => $m->tipo_movimiento,
                'cantidad' => $m->cantidad,
                'usuario' => $m->usuario->usuario ?? 'N/A',
                'motivo' => $m->motivo,
            ])->all(),
            'ventas_asociadas' => $lote->ventasLotes->map(fn ($vl) => [
                'venta_lote_id' => $vl->id,
                'venta_id' => $vl->detalleVenta->venta->id ?? null,
                'folio' => $vl->detalleVenta->venta->folio ?? 'N/A',
                'cantidad_despachada' => $vl->cantidad,
                'fecha_venta' => $vl->detalleVenta->venta->fecha_hora ? Carbon::parse($vl->detalleVenta->venta->fecha_hora)->toDateTimeString() : null,
            ])->all(),
        ];
    }
}
