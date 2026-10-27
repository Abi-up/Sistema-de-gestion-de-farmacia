<?php

namespace Tests\Unit;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Marca;
use App\Models\Medicamento;
use App\Models\Presentacion;
use App\Services\CaducidadService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class CaducidadServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CaducidadService $service;
    protected Medicamento $medicamento;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CaducidadService();

        $categoria = Categoria::create(['nombre' => 'Antibioticos']);
        $presentacion = Presentacion::create(['nombre' => 'Suspension']);
        $marca = Marca::create(['nombre' => 'Amoxil']);
        $laboratorio = Laboratorio::create(['nombre' => 'GSK']);

        $this->medicamento = Medicamento::create([
            'categoria_id' => $categoria->id,
            'presentacion_id' => $presentacion->id,
            'marca_id' => $marca->id,
            'laboratorio_id' => $laboratorio->id,
            'nombre' => 'Amoxicilina 500mg',
            'formula' => 'Amoxicilina trihidrato',
            'requiere_receta' => true,
            'activo' => true,
        ]);
    }

    protected function crearLote(array $atributos = []): Lote
    {
        return Lote::create(array_merge([
            'medicamento_id' => $this->medicamento->id,
            'numero_lote' => 'LOT-' . uniqid(),
            'fecha_fabricacion' => Carbon::now()->subMonths(6)->toDateString(),
            'fecha_caducidad' => Carbon::now()->addMonths(6)->toDateString(),
            'cantidad_inicial' => 100,
            'cantidad_disponible' => 100,
            'estado' => 'DISPONIBLE',
        ], $atributos));
    }

    public function test_obtener_lotes_caducados_distingue_fecha_y_estado(): void
    {
        // 1. Caducado por fecha pasada
        $loteVencidoFecha = $this->crearLote([
            'numero_lote' => 'LOT-VENCIDO-FECHA',
            'fecha_caducidad' => Carbon::now()->subDay()->toDateString(),
            'estado' => 'DISPONIBLE',
        ]);

        // 2. Caducado por estado almacenado
        $loteEstadoCaducado = $this->crearLote([
            'numero_lote' => 'LOT-ESTADO-CADUCADO',
            'fecha_caducidad' => Carbon::now()->addMonth()->toDateString(),
            'estado' => 'CADUCADO',
        ]);

        // 3. Lote sano vigente
        $this->crearLote([
            'numero_lote' => 'LOT-SANO',
            'fecha_caducidad' => Carbon::now()->addMonths(6)->toDateString(),
            'estado' => 'DISPONIBLE',
        ]);

        $caducados = $this->service->obtenerLotesCaducados();

        $this->assertCount(2, $caducados);
        $ids = $caducados->pluck('id')->all();
        $this->assertContains($loteVencidoFecha->id, $ids);
        $this->assertContains($loteEstadoCaducado->id, $ids);
    }

    public function test_obtener_lotes_por_caducar_con_plazo_configurable(): void
    {
        // Vence en 15 días (dentro de ventana de 30)
        $lote15Dias = $this->crearLote([
            'numero_lote' => 'LOT-15',
            'fecha_caducidad' => Carbon::now()->addDays(15)->toDateString(),
            'cantidad_disponible' => 20,
        ]);

        // Vence en 45 días (fuera de ventana de 30, pero dentro de 60)
        $lote45Dias = $this->crearLote([
            'numero_lote' => 'LOT-45',
            'fecha_caducidad' => Carbon::now()->addDays(45)->toDateString(),
            'cantidad_disponible' => 30,
        ]);

        // Lote en cuarentena vence en 10 días (debe excluirse de venta y alerta normal)
        $this->crearLote([
            'numero_lote' => 'LOT-CUARENTENA',
            'fecha_caducidad' => Carbon::now()->addDays(10)->toDateString(),
            'cantidad_disponible' => 15,
            'estado' => 'CUARENTENA',
        ]);

        $porCaducar30 = $this->service->obtenerLotesPorCaducar(30);
        $this->assertCount(1, $porCaducar30);
        $this->assertSame($lote15Dias->id, $porCaducar30->first()->id);

        $porCaducar60 = $this->service->obtenerLotesPorCaducar(60);
        $this->assertCount(2, $porCaducar60);
        $this->assertSame($lote15Dias->id, $porCaducar60->first()->id);
        $this->assertSame($lote45Dias->id, $porCaducar60->last()->id);
    }

    public function test_es_lote_apto_para_venta(): void
    {
        $loteApto = $this->crearLote([
            'fecha_caducidad' => Carbon::now()->addMonths(3)->toDateString(),
            'cantidad_disponible' => 10,
            'estado' => 'DISPONIBLE',
        ]);
        $this->assertTrue($this->service->esLoteAptoParaVenta($loteApto));

        $loteCuarentena = $this->crearLote([
            'fecha_caducidad' => Carbon::now()->addMonths(3)->toDateString(),
            'cantidad_disponible' => 10,
            'estado' => 'CUARENTENA',
        ]);
        $this->assertFalse($this->service->esLoteAptoParaVenta($loteCuarentena));

        $loteVencidoFecha = $this->crearLote([
            'fecha_caducidad' => Carbon::now()->subDay()->toDateString(),
            'cantidad_disponible' => 10,
            'estado' => 'DISPONIBLE',
        ]);
        $this->assertFalse($this->service->esLoteAptoParaVenta($loteVencidoFecha));

        $loteAgotado = $this->crearLote([
            'fecha_caducidad' => Carbon::now()->addMonths(3)->toDateString(),
            'cantidad_disponible' => 0,
            'estado' => 'AGOTADO',
        ]);
        $this->assertFalse($this->service->esLoteAptoParaVenta($loteAgotado));
    }

    public function test_sincronizar_estados_caducidad_no_modifica_existencias_ni_cuarentena(): void
    {
        $loteVencido = $this->crearLote([
            'fecha_caducidad' => Carbon::now()->subDay()->toDateString(),
            'cantidad_disponible' => 42,
            'estado' => 'DISPONIBLE',
        ]);

        $loteCuarentena = $this->crearLote([
            'fecha_caducidad' => Carbon::now()->subDay()->toDateString(),
            'cantidad_disponible' => 10,
            'estado' => 'CUARENTENA',
        ]);

        $loteProximo = $this->crearLote([
            'fecha_caducidad' => Carbon::now()->addDays(10)->toDateString(),
            'cantidad_disponible' => 18,
            'estado' => 'DISPONIBLE',
        ]);

        $resultado = $this->service->sincronizarEstadosCaducidad(30);

        $this->assertSame(1, $resultado['actualizados_a_caducado']);
        $this->assertSame(1, $resultado['actualizados_a_por_caducar']);

        $loteVencido->refresh();
        $this->assertSame('CADUCADO', $loteVencido->estado);
        $this->assertSame(42, $loteVencido->cantidad_disponible); // No se modificaron existencias

        $loteCuarentena->refresh();
        $this->assertSame('CUARENTENA', $loteCuarentena->estado); // Jamás se altera cuarentena

        $loteProximo->refresh();
        $this->assertSame('POR_CADUCAR', $loteProximo->estado);
        $this->assertSame(18, $loteProximo->cantidad_disponible);
    }

    public function test_rechaza_plazo_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->obtenerLotesPorCaducar(0);
    }
}
