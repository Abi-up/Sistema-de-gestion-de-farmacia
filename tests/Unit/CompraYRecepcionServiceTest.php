<?php

namespace Tests\Unit;

use App\Exceptions\CompraException;
use App\Models\Categoria;
use App\Models\Compra;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Marca;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use App\Models\Presentacion;
use App\Models\Proveedor;
use App\Models\Recepcion;
use App\Models\User;
use App\Services\CompraService;
use App\Services\InventarioService;
use App\Services\RecepcionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class CompraYRecepcionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CompraService $compraService;
    protected RecepcionService $recepcionService;
    protected InventarioService $inventarioService;
    protected User $usuario;
    protected Proveedor $proveedor;
    protected Medicamento $medicamentoA;
    protected Medicamento $medicamentoB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventarioService = new InventarioService();
        $this->compraService = new CompraService();
        $this->recepcionService = new RecepcionService($this->inventarioService, $this->compraService);

        $this->usuario = User::create([
            'nombre' => 'Almacenista',
            'apellidos' => 'Gómez',
            'correo' => 'almacen@farmacia.test',
            'telefono' => '5552223344',
            'usuario' => 'agomez',
            'contrasena_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);

        $this->proveedor = Proveedor::create([
            'nombre' => 'Distribuidora Farmacéutica del Norte SA',
            'rfc' => 'DFN101010ABC',
            'telefono' => '5559876543',
            'correo' => 'contacto@dfnorte.test',
        ]);

        $categoria = Categoria::create(['nombre' => 'General']);
        $presentacion = Presentacion::create(['nombre' => 'Caja']);
        $marca = Marca::create(['nombre' => 'Genérico']);
        $laboratorio = Laboratorio::create(['nombre' => 'Pisa']);

        $this->medicamentoA = Medicamento::create([
            'categoria_id' => $categoria->id,
            'presentacion_id' => $presentacion->id,
            'marca_id' => $marca->id,
            'laboratorio_id' => $laboratorio->id,
            'nombre' => 'Ibuprofeno 400mg',
            'formula' => 'Ibuprofeno',
            'requiere_receta' => false,
            'activo' => true,
        ]);

        $this->medicamentoB = Medicamento::create([
            'categoria_id' => $categoria->id,
            'presentacion_id' => $presentacion->id,
            'marca_id' => $marca->id,
            'laboratorio_id' => $laboratorio->id,
            'nombre' => 'Omeprazol 20mg',
            'formula' => 'Omeprazol',
            'requiere_receta' => false,
            'activo' => true,
        ]);
    }

    public function test_crear_orden_compra_calcula_importes_en_servidor(): void
    {
        $partidas = [
            [
                'medicamento_id' => $this->medicamentoA->id,
                'cantidad_solicitada' => 100,
                'precio_unitario' => 12.50,
            ],
            [
                'medicamento_id' => $this->medicamentoB->id,
                'cantidad_solicitada' => 50,
                'precio_unitario' => 20.00,
            ],
        ];

        $compra = $this->compraService->crearOrdenCompra($this->proveedor, $this->usuario, $partidas);

        $this->assertInstanceOf(Compra::class, $compra);
        $this->assertSame('PENDIENTE', $compra->estado);
        // (100 * 12.50) + (50 * 20.00) = 1250 + 1000 = 2250.00
        $this->assertEquals(2250.00, (float) $compra->total);
        $this->assertCount(2, $compra->detalles);
        $this->assertEquals(1250.00, (float) $compra->detalles[0]->subtotal);
        $this->assertEquals(1000.00, (float) $compra->detalles[1]->subtotal);
    }

    public function test_recepcion_parcial_actualiza_estado_compra_y_genera_lote_y_movimiento(): void
    {
        $compra = $this->compraService->crearOrdenCompra($this->proveedor, $this->usuario, [
            [
                'medicamento_id' => $this->medicamentoA->id,
                'cantidad_solicitada' => 100,
                'precio_unitario' => 10.00,
            ],
        ]);

        $detalleCompra = $compra->detalles->first();

        $recepcion = $this->recepcionService->registrarRecepcion(
            $compra,
            $this->usuario,
            [
                [
                    'detalle_compra_id' => $detalleCompra->id,
                    'cantidad_recibida' => 40,
                    'numero_lote' => 'LOT-IBU-001',
                    'fecha_caducidad' => Carbon::now()->addYear()->toDateString(),
                ],
            ],
            'Primera entrega parcial'
        );

        $this->assertInstanceOf(Recepcion::class, $recepcion);
        $this->assertSame('RECIBIDO', $recepcion->estado);

        $compra->refresh();
        $this->assertSame('PARCIAL', $compra->estado);

        $this->assertSame(60, $this->compraService->obtenerCantidadPendientePorDetalle($detalleCompra));

        // Verificar lote creado y stock disponible
        $lote = Lote::where('numero_lote', 'LOT-IBU-001')->first();
        $this->assertNotNull($lote);
        $this->assertSame(40, $lote->cantidad_disponible);
        $this->assertSame('DISPONIBLE', $lote->estado);

        // Verificar movimiento en Kardex
        $movimiento = MovimientoInventario::where('lote_id', $lote->id)->first();
        $this->assertNotNull($movimiento);
        $this->assertSame('ENTRADA', $movimiento->tipo_movimiento);
        $this->assertSame(40, $movimiento->cantidad);
    }

    public function test_recepcion_total_marca_compra_como_recibida(): void
    {
        $compra = $this->compraService->crearOrdenCompra($this->proveedor, $this->usuario, [
            [
                'medicamento_id' => $this->medicamentoA->id,
                'cantidad_solicitada' => 50,
                'precio_unitario' => 15.00,
            ],
        ]);

        $detalleCompra = $compra->detalles->first();

        // 1. Recibir 30 de 50 -> PARCIAL
        $this->recepcionService->registrarRecepcion($compra, $this->usuario, [
            [
                'detalle_compra_id' => $detalleCompra->id,
                'cantidad_recibida' => 30,
                'numero_lote' => 'LOT-A-1',
                'fecha_caducidad' => Carbon::now()->addYear()->toDateString(),
            ],
        ]);
        $this->assertSame('PARCIAL', $compra->fresh()->estado);

        // 2. Recibir 20 restantes de 50 -> RECIBIDA
        $this->recepcionService->registrarRecepcion($compra, $this->usuario, [
            [
                'detalle_compra_id' => $detalleCompra->id,
                'cantidad_recibida' => 20,
                'numero_lote' => 'LOT-A-2',
                'fecha_caducidad' => Carbon::now()->addYear()->toDateString(),
            ],
        ]);
        $this->assertSame('RECIBIDA', $compra->fresh()->estado);
        $this->assertSame(0, $this->compraService->obtenerCantidadPendientePorDetalle($detalleCompra));
    }

    public function test_rechaza_recibir_cantidad_superior_a_la_pendiente(): void
    {
        $compra = $this->compraService->crearOrdenCompra($this->proveedor, $this->usuario, [
            [
                'medicamento_id' => $this->medicamentoA->id,
                'cantidad_solicitada' => 50,
                'precio_unitario' => 10.00,
            ],
        ]);

        $detalleCompra = $compra->detalles->first();

        $this->expectException(CompraException::class);
        $this->expectExceptionMessageMatches('/excede la cantidad pendiente/');

        $this->recepcionService->registrarRecepcion($compra, $this->usuario, [
            [
                'detalle_compra_id' => $detalleCompra->id,
                'cantidad_recibida' => 51,
                'numero_lote' => 'LOT-EXCESO',
                'fecha_caducidad' => Carbon::now()->addYear()->toDateString(),
            ],
        ]);
    }

    public function test_rechaza_recibir_lote_caducado_desde_proveedor(): void
    {
        $compra = $this->compraService->crearOrdenCompra($this->proveedor, $this->usuario, [
            [
                'medicamento_id' => $this->medicamentoA->id,
                'cantidad_solicitada' => 20,
                'precio_unitario' => 10.00,
            ],
        ]);

        $detalleCompra = $compra->detalles->first();

        $this->expectException(CompraException::class);
        $this->expectExceptionMessageMatches('/vence hoy/');

        $this->recepcionService->registrarRecepcion($compra, $this->usuario, [
            [
                'detalle_compra_id' => $detalleCompra->id,
                'cantidad_recibida' => 10,
                'numero_lote' => 'LOT-CADUCADO-PROV',
                'fecha_caducidad' => Carbon::now()->subDay()->toDateString(),
            ],
        ]);
    }

    public function test_recepcion_rechazada_en_anden_no_genera_lote_ni_movimiento(): void
    {
        $compra = $this->compraService->crearOrdenCompra($this->proveedor, $this->usuario, [
            [
                'medicamento_id' => $this->medicamentoA->id,
                'cantidad_solicitada' => 50,
                'precio_unitario' => 10.00,
            ],
        ]);

        $detalleCompra = $compra->detalles->first();

        $recepcion = $this->recepcionService->registrarRecepcion(
            $compra,
            $this->usuario,
            [
                [
                    'detalle_compra_id' => $detalleCompra->id,
                    'cantidad_recibida' => 50,
                    'observaciones' => 'Empaque dañado por transporte',
                ],
            ],
            'Mercancía rechazada en andén',
            'RECHAZADO'
        );

        $this->assertSame('RECHAZADO', $recepcion->estado);
        $this->assertSame('PENDIENTE', $compra->fresh()->estado); // No se altera el estado de la compra
        $this->assertSame(0, Lote::count());
        $this->assertSame(0, MovimientoInventario::count());
    }

    public function test_cancelar_orden_compra_valida_estados(): void
    {
        $compra = $this->compraService->crearOrdenCompra($this->proveedor, $this->usuario, [
            [
                'medicamento_id' => $this->medicamentoA->id,
                'cantidad_solicitada' => 10,
                'precio_unitario' => 5.00,
            ],
        ]);

        $compraCancelada = $this->compraService->cancelarCompra($compra, $this->usuario, 'Error de captura');
        $this->assertSame('CANCELADA', $compraCancelada->estado);

        // Intentar recibir sobre compra cancelada debe fallar
        $this->expectException(CompraException::class);
        $this->recepcionService->registrarRecepcion($compraCancelada, $this->usuario, [
            [
                'detalle_compra_id' => $compra->detalles->first()->id,
                'cantidad_recibida' => 5,
                'numero_lote' => 'LOT-X',
                'fecha_caducidad' => Carbon::now()->addYear()->toDateString(),
            ],
        ]);
    }
}
