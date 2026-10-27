<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\Medicamento;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class CaducidadService
{
    /**
     * Días por defecto para la ventana de alerta de vencimiento.
     */
    public const DIAS_VENTANA_DEFECTO = 30;

    /**
     * Consulta los lotes caducados según la fecha real o el estado almacenado.
     *
     * @param int|Medicamento|null $medicamento Filtro opcional por medicamento.
     * @return Collection<int, Lote>
     */
    public function obtenerLotesCaducados(int|Medicamento|null $medicamento = null): Collection
    {
        $medicamentoId = $medicamento instanceof Medicamento ? $medicamento->id : $medicamento;
        $hoy = Carbon::today()->toDateString();

        return Lote::query()
            ->with(['medicamento'])
            ->when($medicamentoId !== null, fn ($query) => $query->where('medicamento_id', $medicamentoId))
            ->where(function ($query) use ($hoy): void {
                $query->where('fecha_caducidad', '<=', $hoy)
                    ->orWhere('estado', 'CADUCADO');
            })
            ->orderBy('fecha_caducidad', 'asc')
            ->get();
    }

    /**
     * Consulta los lotes próximos a caducar dentro de una ventana de días configurable.
     * Excluye lotes en cuarentena, lotes agotados y lotes que ya hayan vencido.
     *
     * @param int $diasVentana Plazo en días a futuro.
     * @param int|Medicamento|null $medicamento Filtro opcional por medicamento.
     * @return Collection<int, Lote>
     */
    public function obtenerLotesPorCaducar(
        int $diasVentana = self::DIAS_VENTANA_DEFECTO,
        int|Medicamento|null $medicamento = null
    ): Collection {
        if ($diasVentana <= 0) {
            throw new InvalidArgumentException('El plazo en días para calcular caducidades debe ser mayor a cero.');
        }

        $medicamentoId = $medicamento instanceof Medicamento ? $medicamento->id : $medicamento;
        $hoy = Carbon::today()->toDateString();
        $fechaLimite = Carbon::today()->addDays($diasVentana)->toDateString();

        return Lote::query()
            ->with(['medicamento'])
            ->when($medicamentoId !== null, fn ($query) => $query->where('medicamento_id', $medicamentoId))
            ->where('cantidad_disponible', '>', 0)
            ->where('estado', '!=', 'CUARENTENA')
            ->where('fecha_caducidad', '>', $hoy)
            ->where('fecha_caducidad', '<=', $fechaLimite)
            ->orderBy('fecha_caducidad', 'asc')
            ->get();
    }

    /**
     * Genera un resumen estadístico consolidado del estado de todos los lotes.
     *
     * @return array{
     *     total_lotes: int,
     *     lotes_disponibles: int,
     *     lotes_agotados: int,
     *     lotes_por_caducar: int,
     *     lotes_caducados: int,
     *     lotes_cuarentena: int,
     *     unidades_en_riesgo_caducidad: int,
     *     unidades_caducadas: int
     * }
     */
    public function obtenerResumenLotes(): array
    {
        $hoy = Carbon::today()->toDateString();
        $fechaLimiteAlerta = Carbon::today()->addDays(self::DIAS_VENTANA_DEFECTO)->toDateString();

        $lotes = Lote::all();

        $caducados = $lotes->filter(function (Lote $lote) use ($hoy): bool {
            return $lote->estado === 'CADUCADO' || ($lote->fecha_caducidad && $lote->fecha_caducidad->toDateString() <= $hoy);
        });

        $porCaducar = $lotes->filter(function (Lote $lote) use ($hoy, $fechaLimiteAlerta): bool {
            if ($lote->estado === 'CUARENTENA' || $lote->cantidad_disponible <= 0) {
                return false;
            }
            $fecha = $lote->fecha_caducidad ? $lote->fecha_caducidad->toDateString() : null;

            return $fecha !== null && $fecha > $hoy && $fecha <= $fechaLimiteAlerta;
        });

        return [
            'total_lotes' => $lotes->count(),
            'lotes_disponibles' => $lotes->where('estado', 'DISPONIBLE')->count(),
            'lotes_agotados' => $lotes->where('estado', 'AGOTADO')->count(),
            'lotes_por_caducar' => $porCaducar->count(),
            'lotes_caducados' => $caducados->count(),
            'lotes_cuarentena' => $lotes->where('estado', 'CUARENTENA')->count(),
            'unidades_en_riesgo_caducidad' => (int) $porCaducar->sum('cantidad_disponible'),
            'unidades_caducadas' => (int) $caducados->sum('cantidad_disponible'),
        ];
    }

    /**
     * Verifica si un lote está apto física y administrativamente para ser vendido.
     * Distingue la fecha real de caducidad del estado almacenado.
     *
     * @param Lote $lote
     * @return bool
     */
    public function esLoteAptoParaVenta(Lote $lote): bool
    {
        if ($lote->estado === 'CUARENTENA') {
            return false;
        }

        if ($lote->estado === 'CADUCADO') {
            return false;
        }

        if ((int) $lote->cantidad_disponible <= 0) {
            return false;
        }

        if ($lote->fecha_caducidad) {
            $fechaCaducidad = Carbon::parse($lote->fecha_caducidad)->startOfDay();
            if ($fechaCaducidad->lessThanOrEqualTo(Carbon::today())) {
                return false;
            }
        }

        return in_array($lote->estado, ['DISPONIBLE', 'POR_CADUCAR'], true);
    }

    /**
     * Sincroniza los estados almacenados de los lotes según su fecha real.
     * Regla estricta: No modifica existencias y jamás altera lotes en CUARENTENA.
     *
     * @param int $diasVentana
     * @return array{actualizados_a_caducado: int, actualizados_a_por_caducar: int}
     */
    public function sincronizarEstadosCaducidad(int $diasVentana = self::DIAS_VENTANA_DEFECTO): array
    {
        $hoy = Carbon::today()->toDateString();
        $fechaLimite = Carbon::today()->addDays($diasVentana)->toDateString();

        // 1. Lotes vencidos por fecha que no estén en CUARENTENA ni ya en CADUCADO
        $actualizadosCaducados = Lote::query()
            ->where('estado', '!=', 'CUARENTENA')
            ->where('estado', '!=', 'CADUCADO')
            ->where('fecha_caducidad', '<=', $hoy)
            ->update(['estado' => 'CADUCADO']);

        // 2. Lotes próximos a vencer que estén en DISPONIBLE y con stock
        $actualizadosPorCaducar = Lote::query()
            ->where('estado', 'DISPONIBLE')
            ->where('cantidad_disponible', '>', 0)
            ->where('fecha_caducidad', '>', $hoy)
            ->where('fecha_caducidad', '<=', $fechaLimite)
            ->update(['estado' => 'POR_CADUCAR']);

        return [
            'actualizados_a_caducado' => $actualizadosCaducados,
            'actualizados_a_por_caducar' => $actualizadosPorCaducar,
        ];
    }
}
