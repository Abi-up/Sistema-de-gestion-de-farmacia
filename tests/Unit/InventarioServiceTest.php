<?php

namespace Tests\Unit;

use App\Exceptions\LoteNoAptoException;
use App\Exceptions\MovimientoInvalidoException;
use App\Exceptions\StockInsuficienteException;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Marca;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use App\Models\Presentacion;
use App\Models\User;
use App\Services\InventarioService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventarioServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InventarioService $service;
    protected User $usuario;
    protected Medicamento $medicamento;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new InventarioService();

        $this->usuario = User::create([
            'nombre' => 'Juan',
            'apellidos' => 'Pérez',
            'correo' => 'juan.perez@farmacia.test',
            'telefono' => '5551234567',
            'usuario' => 'jperez',
            'contrasena_hash' => bcrypt('secret123'),
            'estado' => 'ACTIVO',
        ]);

        $categoria = Categoria::create(['nombre' => 'Analgesicos', 'descripcion' => 'Alivio del dolor']);
        $presentacion = Presentacion::create(['nombre' => 'Tabletas']);
        $marca = Marca::create(['nombre' => 'Genfar']);
        $laboratorio = Laboratorio::create(['nombre' => 'Bayer', 'telefono' => '5550001', 'correo' => 'bayer@test.com']);

        $this->medicamento = Medicamento::create([
            'categoria_id' => $categoria->id,
            'presentacion_id' => $presentacion->id,
            'marca_id' => $marca->id,
            'laboratorio_id' => $laboratorio->id,
            'nombre' => 'Paracetamol 500mg',
            'formula' => 'Paracetamol',
            'codigo_barras' => '75010001',
            'requiere_receta' => false,
            'activo' => true,
        ]);
    }

    protected function crearLote(array $atributos = []): Lote
    {
        return Lote::create(array_merge([
            'medicamento_id' => $this->medicamento->id,
            'numero_lote' => 'LOT-' . uniqid(),
            'fecha_fabricacion' => Carbon::now()->subMonths(2)->toDateString(),
            'fecha_caducidad' => Carbon::now()->addMonths(12)->toDateString(),
            'cantidad_inicial' => 100,
            'cantidad_disponible' => 100,
            'estado' => 'DISPONIBLE',
        ], $atributos));
    }

    public function test_entrada_registra_movimiento_positivo_y_aumenta_stock(): void
    {
        $lote = $this->crearLote(['cantidad_disponible' => 50]);

        $movimiento = $this->service->registrarMovimiento(
            $lote,
            $this->usuario,
            'ENTRADA',
            25,
            'Recepción de prueba'
        );

        $this->assertInstanceOf(MovimientoInventario::class, $movimiento);
        $this->assertSame(25, $movimiento->cantidad);
        $this->assertSame('ENTRADA', $movimiento->tipo_movimiento);
        $this->assertSame('Recepción de prueba', $movimiento->motivo);

        $lote->refresh();
        $this->assertSame(75, $lote->cantidad_disponible);
        $this->assertSame('DISPONIBLE', $lote->estado);
    }

    public function test_salida_registra_movimiento_negativo_y_descuenta_stock(): void
    {
        $lote = $this->crearLote(['cantidad_disponible' => 50]);

        $movimiento = $this->service->registrarMovimiento(
            $lote,
            $this->usuario,
            'SALIDA',
            20,
            'Venta en mostrador'
        );

        $this->assertSame(-20, $movimiento->cantidad);
        $this->assertSame('SALIDA', $movimiento->tipo_movimiento);

        $lote->refresh();
        $this->assertSame(30, $lote->cantidad_disponible);
    }

    public function test_salida_que_agota_stock_actualiza_estado_a_agotado(): void
    {
        $lote = $this->crearLote(['cantidad_disponible' => 15]);

        $this->service->registrarMovimiento(
            $lote,
            $this->usuario,
            'SALIDA',
            15,
            'Salida total'
        );

        $lote->refresh();
        $this->assertSame(0, $lote->cantidad_disponible);
        $this->assertSame('AGOTADO', $lote->estado);
    }

    public function test_entrada_a_lote_agotado_restaura_estado_disponible(): void
    {
        $lote = $this->crearLote([
            'cantidad_disponible' => 0,
            'estado' => 'AGOTADO',
            'fecha_caducidad' => Carbon::now()->addMonths(6)->toDateString(),
        ]);

        $this->service->registrarMovimiento(
            $lote,
            $this->usuario,
            'ENTRADA',
            10,
            'Reabastecimiento'
        );

        $lote->refresh();
        $this->assertSame(10, $lote->cantidad_disponible);
        $this->assertSame('DISPONIBLE', $lote->estado);
    }

    public function test_devolucion_registra_movimiento_positivo_y_aumenta_stock(): void
    {
        $lote = $this->crearLote(['cantidad_disponible' => 10]);

        $movimiento = $this->service->registrarMovimiento(
            $lote,
            $this->usuario,
            'DEVOLUCION',
            5,
            'Devolución de cliente'
        );

        $this->assertSame(5, $movimiento->cantidad);
        $this->assertSame('DEVOLUCION', $movimiento->tipo_movimiento);

        $lote->refresh();
        $this->assertSame(15, $lote->cantidad_disponible);
    }

    public function test_ajuste_positivo_y_negativo(): void
    {
        $lote = $this->crearLote(['cantidad_disponible' => 50]);

        $movPositivo = $this->service->registrarMovimiento(
            $lote,
            $this->usuario,
            'AJUSTE',
            10,
            'Conteo físico sobrante'
        );
        $this->assertSame(10, $movPositivo->cantidad);
        $this->assertSame(60, $lote->fresh()->cantidad_disponible);

        $movNegativo = $this->service->registrarMovimiento(
            $lote,
            $this->usuario,
            'AJUSTE',
            -15,
            'Conteo físico faltante'
        );
        $this->assertSame(-15, $movNegativo->cantidad);
        $this->assertSame(45, $lote->fresh()->cantidad_disponible);
    }

    public function test_salida_falla_por_stock_insuficiente(): void
    {
        $lote = $this->crearLote(['cantidad_disponible' => 10]);

        $this->expectException(StockInsuficienteException::class);

        $this->service->registrarMovimiento(
            $lote,
            $this->usuario,
            'SALIDA',
            15
        );
    }

    public function test_ajuste_negativo_falla_si_deja_stock_negativo(): void
    {
        $lote = $this->crearLote(['cantidad_disponible' => 10]);

        $this->expectException(StockInsuficienteException::class);

        $this->service->registrarMovimiento(
            $lote,
            $this->usuario,
            'AJUSTE',
            -11
        );
    }

    public function test_rechaza_cantidades_invalidas(): void
    {
        $lote = $this->crearLote(['cantidad_disponible' => 50]);

        try {
            $this->service->registrarMovimiento($lote, $this->usuario, 'SALIDA', 0);
            $this->fail('Se esperaba excepción por cantidad 0 en SALIDA');
        } catch (MovimientoInvalidoException $e) {
            $this->assertStringContainsString('mayor a cero', $e->getMessage());
        }

        try {
            $this->service->registrarMovimiento($lote, $this->usuario, 'ENTRADA', -5);
            $this->fail('Se esperaba excepción por cantidad negativa en ENTRADA');
        } catch (MovimientoInvalidoException $e) {
            $this->assertStringContainsString('mayor a cero', $e->getMessage());
        }

        try {
            $this->service->registrarMovimiento($lote, $this->usuario, 'AJUSTE', 0);
            $this->fail('Se esperaba excepción por cantidad 0 en AJUSTE');
        } catch (MovimientoInvalidoException $e) {
            $this->assertStringContainsString('no puede ser cero', $e->getMessage());
        }
    }

    public function test_rechaza_tipo_de_movimiento_desconocido(): void
    {
        $lote = $this->crearLote();

        $this->expectException(MovimientoInvalidoException::class);
        $this->service->registrarMovimiento($lote, $this->usuario, 'TRANSFERENCIA', 10);
    }

    public function test_rechaza_salida_de_lote_caducado_por_fecha(): void
    {
        $lote = $this->crearLote([
            'fecha_caducidad' => Carbon::now()->subDays(5)->toDateString(),
            'estado' => 'DISPONIBLE',
        ]);

        $this->expectException(LoteNoAptoException::class);
        $this->expectExceptionMessageMatches('/CADUCADO/');

        $this->service->registrarMovimiento($lote, $this->usuario, 'SALIDA', 5);
    }

    public function test_rechaza_salida_y_entrada_de_lote_en_cuarentena(): void
    {
        $lote = $this->crearLote([
            'estado' => 'CUARENTENA',
        ]);

        try {
            $this->service->registrarMovimiento($lote, $this->usuario, 'SALIDA', 5);
            $this->fail('Se esperaba excepción por CUARENTENA en SALIDA');
        } catch (LoteNoAptoException $e) {
            $this->assertStringContainsString('CUARENTENA', $e->getMessage());
        }

        try {
            $this->service->registrarMovimiento($lote, $this->usuario, 'ENTRADA', 5);
            $this->fail('Se esperaba excepción por CUARENTENA en ENTRADA');
        } catch (LoteNoAptoException $e) {
            $this->assertStringContainsString('CUARENTENA', $e->getMessage());
        }
    }

    public function test_transaccion_revierte_si_ocurre_un_fallo(): void
    {
        $lote = $this->crearLote(['cantidad_disponible' => 50]);

        try {
            // detalle_venta_id inexistente causará fallo en la validación
            $this->service->registrarMovimiento(
                $lote,
                $this->usuario,
                'SALIDA',
                10,
                'Fallo intencional',
                null,
                999999
            );
            $this->fail('Se esperaba fallo por referencia inválida');
        } catch (MovimientoInvalidoException $e) {
            $this->assertStringContainsString('no existe', $e->getMessage());
        }

        $lote->refresh();
        $this->assertSame(50, $lote->cantidad_disponible);
        $this->assertSame(0, MovimientoInventario::count());
    }

    public function test_obtener_lotes_disponibles_fefo(): void
    {
        $loteLejano = $this->crearLote([
            'numero_lote' => 'LOT-LEJANO',
            'fecha_caducidad' => Carbon::now()->addMonths(10)->toDateString(),
            'cantidad_disponible' => 30,
        ]);

        $loteCercano = $this->crearLote([
            'numero_lote' => 'LOT-CERCANO',
            'fecha_caducidad' => Carbon::now()->addMonths(2)->toDateString(),
            'cantidad_disponible' => 20,
        ]);

        $this->crearLote([
            'numero_lote' => 'LOT-CADUCADO',
            'fecha_caducidad' => Carbon::now()->subDay()->toDateString(),
            'cantidad_disponible' => 10,
            'estado' => 'CADUCADO',
        ]);

        $lotes = $this->service->obtenerLotesDisponiblesFEFO($this->medicamento->id);

        $this->assertCount(2, $lotes);
        $this->assertSame($loteCercano->id, $lotes->first()->id);
        $this->assertSame($loteLejano->id, $lotes->last()->id);
    }
}
