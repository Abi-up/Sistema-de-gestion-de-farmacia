<?php

namespace Tests\Unit;

use App\Models\Categoria;
use App\Models\Compra;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Marca;
use App\Models\Medicamento;
use App\Models\Presentacion;
use App\Models\Proveedor;
use App\Models\User;
use App\Services\BitacoraService;
use App\Services\CompraService;
use App\Services\DevolucionService;
use App\Services\InventarioService;
use App\Services\RecepcionService;
use App\Services\RecetaService;
use App\Services\ReporteService;
use App\Services\VentaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BitacoraYReporteServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BitacoraService $bitacoraService;
    protected ReporteService $reporteService;
    protected InventarioService $inventarioService;
    protected CompraService $compraService;
    protected RecepcionService $recepcionService;
    protected RecetaService $recetaService;
    protected VentaService $ventaService;
    protected DevolucionService $devolucionService;

    protected User $usuario;
    protected Proveedor $proveedor;
    protected Medicamento $medicamento;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bitacoraService = new BitacoraService();
        $this->reporteService = new ReporteService();
        $this->inventarioService = new InventarioService();
        $this->compraService = new CompraService();
        $this->recepcionService = new RecepcionService($this->inventarioService, $this->compraService);
        $this->recetaService = new RecetaService();
        $this->ventaService = new VentaService($this->inventarioService, $this->recetaService);
        $this->devolucionService = new DevolucionService($this->inventarioService);

        $this->usuario = User::create([
            'nombre' => 'Carlos',
            'apellidos' => 'Mendoza',
            'correo' => 'cmendoza@farmacia.test',
            'telefono' => '5556667788',
            'usuario' => 'cmendoza',
            'contrasena_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);

        $this->proveedor = Proveedor::create([
            'nombre' => 'Fármacos Nacionales SA',
            'rfc' => 'FNA880101XYZ',
        ]);

        $categoria = Categoria::create(['nombre' => 'Cardiovascular']);
        $presentacion = Presentacion::create(['nombre' => 'Comprimidos']);
        $marca = Marca::create(['nombre' => 'Cardio']);
        $laboratorio = Laboratorio::create(['nombre' => 'Pfizer']);

        $this->medicamento = Medicamento::create([
            'categoria_id' => $categoria->id,
            'presentacion_id' => $presentacion->id,
            'marca_id' => $marca->id,
            'laboratorio_id' => $laboratorio->id,
            'nombre' => 'Losartán 50mg',
            'formula' => 'Losartán potásico',
            'codigo_barras' => '750998877',
            'requiere_receta' => false,
            'activo' => true,
        ]);
    }

    public function test_bitacora_registra_y_sanitiza_datos_sensibles(): void
    {
        $bitacora = $this->bitacoraService->registrar(
            $this->usuario,
            'SEGURIDAD',
            'LOGIN',
            'Inicio de sesión exitoso',
            [
                'ip' => '192.168.1.10',
                'password' => 'miPasswordSecreto',
                'token' => 'abc123xyzToken',
                'usuario' => 'cmendoza',
            ]
        );

        $this->assertNotNull($bitacora);
        $this->assertSame('SEGURIDAD', $bitacora->modulo);
        $this->assertSame('LOGIN', $bitacora->accion);

        // Comprobar que los campos sensibles fueron ofuscados
        $this->assertStringNotContainsString('miPasswordSecreto', $bitacora->descripcion);
        $this->assertStringNotContainsString('abc123xyzToken', $bitacora->descripcion);
        $this->assertStringContainsString('********', $bitacora->descripcion);
        $this->assertStringContainsString('192.168.1.10', $bitacora->descripcion);
    }

    public function test_bitacora_tolerancia_a_fallos(): void
    {
        // Usuario inexistente que dispararía error en BD
        $resultado = $this->bitacoraService->registrar(
            999999,
            'MODULO',
            'ACCION',
            'Prueba de fallo'
        );

        // No lanza excepción, simplemente retorna null y registra en log
        $this->assertNull($resultado);
    }

    public function test_reporte_existencias_consolida_lotes_y_proxima_caducidad(): void
    {
        Lote::create([
            'medicamento_id' => $this->medicamento->id,
            'numero_lote' => 'LOT-REP-1',
            'fecha_caducidad' => Carbon::now()->addMonths(4)->toDateString(),
            'cantidad_inicial' => 50,
            'cantidad_disponible' => 50,
            'estado' => 'DISPONIBLE',
        ]);

        Lote::create([
            'medicamento_id' => $this->medicamento->id,
            'numero_lote' => 'LOT-REP-2',
            'fecha_caducidad' => Carbon::now()->addMonths(8)->toDateString(),
            'cantidad_inicial' => 30,
            'cantidad_disponible' => 30,
            'estado' => 'DISPONIBLE',
        ]);

        $reporte = $this->reporteService->reporteExistencias();

        $this->assertCount(1, $reporte);
        $item = $reporte[0];
        $this->assertSame('Losartán 50mg', $item['nombre']);
        $this->assertSame(80, $item['stock_total_disponible']);
        $this->assertSame(2, $item['lotes_activos']);
        $this->assertSame(Carbon::now()->addMonths(4)->toDateString(), $item['proxima_caducidad']);
    }

    public function test_reporte_ventas_y_productos_mas_vendidos(): void
    {
        Lote::create([
            'medicamento_id' => $this->medicamento->id,
            'numero_lote' => 'LOT-VENTAS',
            'fecha_caducidad' => Carbon::now()->addYear()->toDateString(),
            'cantidad_inicial' => 100,
            'cantidad_disponible' => 100,
            'estado' => 'DISPONIBLE',
        ]);

        // Registrar venta de 15 unidades a $30 c/u
        $this->ventaService->procesarVenta($this->usuario, [
            [
                'medicamento_id' => $this->medicamento->id,
                'cantidad' => 15,
                'precio_unitario' => 30.00,
            ],
        ]);

        $hoy = Carbon::today()->toDateString();

        $reporteVentas = $this->reporteService->reporteVentasPorPeriodo($hoy, $hoy);
        $this->assertSame(1, $reporteVentas['conteo_ventas']);
        $this->assertEquals(450.00, $reporteVentas['total_neto']);

        $masVendidos = $this->reporteService->reporteProductosMasVendidos($hoy, $hoy);
        $this->assertCount(1, $masVendidos);
        $this->assertSame('Losartán 50mg', $masVendidos[0]['nombre']);
        $this->assertSame(15, $masVendidos[0]['unidades_vendidas']);
        $this->assertEquals(450.00, $masVendidos[0]['monto_generado']);
    }

    public function test_reporte_compras_pendientes(): void
    {
        $compra = $this->compraService->crearOrdenCompra($this->proveedor, $this->usuario, [
            [
                'medicamento_id' => $this->medicamento->id,
                'cantidad_solicitada' => 60,
                'precio_unitario' => 18.00,
            ],
        ]);

        $detalle = $compra->detalles->first();

        // Recibir solo 20 de 60
        $this->recepcionService->registrarRecepcion($compra, $this->usuario, [
            [
                'detalle_compra_id' => $detalle->id,
                'cantidad_recibida' => 20,
                'numero_lote' => 'LOT-COMPRA-PEND',
                'fecha_caducidad' => Carbon::now()->addYear()->toDateString(),
            ],
        ]);

        $pendientes = $this->reporteService->reporteComprasYRecepcionesPendientes();
        $this->assertCount(1, $pendientes);
        $this->assertSame('PARCIAL', $pendientes[0]['estado']);
        $this->assertSame(40, $pendientes[0]['partidas_pendientes'][0]['cantidad_pendiente']);
    }

    public function test_reporte_trazabilidad_lote(): void
    {
        $compra = $this->compraService->crearOrdenCompra($this->proveedor, $this->usuario, [
            [
                'medicamento_id' => $this->medicamento->id,
                'cantidad_solicitada' => 50,
                'precio_unitario' => 15.00,
            ],
        ]);

        $recepcion = $this->recepcionService->registrarRecepcion($compra, $this->usuario, [
            [
                'detalle_compra_id' => $compra->detalles->first()->id,
                'cantidad_recibida' => 50,
                'numero_lote' => 'LOT-TRAZA-01',
                'fecha_caducidad' => Carbon::now()->addYear()->toDateString(),
            ],
        ]);

        $lote = Lote::where('numero_lote', 'LOT-TRAZA-01')->firstOrFail();

        // Realizar una venta que consuma 10 piezas de este lote
        $venta = $this->ventaService->procesarVenta($this->usuario, [
            [
                'medicamento_id' => $this->medicamento->id,
                'cantidad' => 10,
                'precio_unitario' => 25.00,
            ],
        ]);

        $trazabilidad = $this->reporteService->reporteTrazabilidadLote($lote->id);

        $this->assertSame('LOT-TRAZA-01', $trazabilidad['lote']['numero_lote']);
        $this->assertNotNull($trazabilidad['origen_recepcion']);
        $this->assertSame($recepcion->id, $trazabilidad['origen_recepcion']['recepcion_id']);
        $this->assertCount(2, $trazabilidad['movimientos']); // ENTRADA y SALIDA
        $this->assertCount(1, $trazabilidad['ventas_asociadas']);
        $this->assertSame($venta->folio, $trazabilidad['ventas_asociadas'][0]['folio']);
        $this->assertSame(10, $trazabilidad['ventas_asociadas'][0]['cantidad_despachada']);
    }
}
