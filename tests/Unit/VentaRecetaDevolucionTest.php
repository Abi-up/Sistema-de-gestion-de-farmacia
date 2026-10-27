<?php

namespace Tests\Unit;

use App\Exceptions\DevolucionException;
use App\Exceptions\RecetaException;
use App\Exceptions\StockInsuficienteException;
use App\Exceptions\VentaException;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Lote;
use App\Models\Marca;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use App\Models\Presentacion;
use App\Models\Receta;
use App\Models\User;
use App\Models\Venta;
use App\Services\DevolucionService;
use App\Services\InventarioService;
use App\Services\RecetaService;
use App\Services\VentaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaRecetaDevolucionTest extends TestCase
{
    use RefreshDatabase;

    protected InventarioService $inventarioService;
    protected RecetaService $recetaService;
    protected VentaService $ventaService;
    protected DevolucionService $devolucionService;

    protected User $cajero;
    protected Medicamento $medSinReceta;
    protected Medicamento $medConReceta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventarioService = new InventarioService();
        $this->recetaService = new RecetaService();
        $this->ventaService = new VentaService($this->inventarioService, $this->recetaService);
        $this->devolucionService = new DevolucionService($this->inventarioService);

        $this->cajero = User::create([
            'nombre' => 'Cajero',
            'apellidos' => 'Uno',
            'correo' => 'caja1@farmacia.test',
            'telefono' => '5554443322',
            'usuario' => 'cajero1',
            'contrasena_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);

        $categoria = Categoria::create(['nombre' => 'Generales']);
        $presentacion = Presentacion::create(['nombre' => 'Frasco']);
        $marca = Marca::create(['nombre' => 'Generico']);
        $laboratorio = Laboratorio::create(['nombre' => 'LabTest']);

        $this->medSinReceta = Medicamento::create([
            'categoria_id' => $categoria->id,
            'presentacion_id' => $presentacion->id,
            'marca_id' => $marca->id,
            'laboratorio_id' => $laboratorio->id,
            'nombre' => 'Aspirina 500mg',
            'formula' => 'Ácido acetilsalicílico',
            'requiere_receta' => false,
            'activo' => true,
        ]);

        $this->medConReceta = Medicamento::create([
            'categoria_id' => $categoria->id,
            'presentacion_id' => $presentacion->id,
            'marca_id' => $marca->id,
            'laboratorio_id' => $laboratorio->id,
            'nombre' => 'Clonazepam 2mg',
            'formula' => 'Clonazepam',
            'requiere_receta' => true,
            'activo' => true,
        ]);
    }

    protected function crearLote(Medicamento $med, array $atributos = []): Lote
    {
        return Lote::create(array_merge([
            'medicamento_id' => $med->id,
            'numero_lote' => 'LOT-' . uniqid(),
            'fecha_fabricacion' => Carbon::now()->subMonths(3)->toDateString(),
            'fecha_caducidad' => Carbon::now()->addYear()->toDateString(),
            'cantidad_inicial' => 100,
            'cantidad_disponible' => 100,
            'estado' => 'DISPONIBLE',
        ], $atributos));
    }

    public function test_venta_normal_aplica_fefo_descuenta_stock_y_calcula_importes(): void
    {
        // Dos lotes con caducidades distintas
        $lotePrimero = $this->crearLote($this->medSinReceta, [
            'numero_lote' => 'LOT-FEFO-1',
            'fecha_caducidad' => Carbon::now()->addMonths(2)->toDateString(),
            'cantidad_disponible' => 15,
        ]);

        $loteSegundo = $this->crearLote($this->medSinReceta, [
            'numero_lote' => 'LOT-FEFO-2',
            'fecha_caducidad' => Carbon::now()->addMonths(10)->toDateString(),
            'cantidad_disponible' => 20,
        ]);

        // Se solicitan 20 piezas a $10 c/u con $5 de descuento de línea
        $venta = $this->ventaService->procesarVenta($this->cajero, [
            [
                'medicamento_id' => $this->medSinReceta->id,
                'cantidad' => 20,
                'precio_unitario' => 10.00,
                'descuento' => 5.00,
            ],
        ], [], 0.0, 0.16);

        $this->assertInstanceOf(Venta::class, $venta);
        $this->assertSame('COMPLETADA', $venta->estado);
        $this->assertNotEmpty($venta->folio);

        // FEFO debe haber tomado 15 del lote primero (agotándolo) y 5 del segundo
        $this->assertSame(0, $lotePrimero->fresh()->cantidad_disponible);
        $this->assertSame('AGOTADO', $lotePrimero->fresh()->estado);

        $this->assertSame(15, $loteSegundo->fresh()->cantidad_disponible);

        // Comprobación de cálculos:
        // Subtotal bruto: 20 * 10 = 200.00
        // Descuento: 5.00
        // Base imponible: 195.00
        // Impuesto (16%): 31.20
        // Total: 226.20
        $this->assertEquals(200.00, (float) $venta->subtotal);
        $this->assertEquals(5.00, (float) $venta->descuento);
        $this->assertEquals(31.20, (float) $venta->impuestos);
        $this->assertEquals(226.20, (float) $venta->total);

        // Comprobar registros de salidas en Kardex
        $movimientos = MovimientoInventario::where('tipo_movimiento', 'SALIDA')->get();
        $this->assertCount(2, $movimientos);
        $this->assertSame(-15, $movimientos[0]->cantidad);
        $this->assertSame(-5, $movimientos[1]->cantidad);
    }

    public function test_venta_exige_receta_valida_para_medicamentos_controlados(): void
    {
        $this->crearLote($this->medConReceta, ['cantidad_disponible' => 10]);

        // 1. Intentar vender medicamento controlado sin receta debe fallar
        try {
            $this->ventaService->procesarVenta($this->cajero, [
                [
                    'medicamento_id' => $this->medConReceta->id,
                    'cantidad' => 2,
                    'precio_unitario' => 100.00,
                ],
            ]);
            $this->fail('Se esperaba excepción por falta de receta médica');
        } catch (RecetaException $e) {
            $this->assertStringContainsString('receta médica obligatoria', $e->getMessage());
        }

        // 2. Registrar receta válida
        $receta = $this->recetaService->registrarReceta([
            'folio' => 'REC-2026-001',
            'nombre_medico' => 'Dra. María Elena Garza',
            'cedula_profesional' => 'CED-998877',
            'fecha_emision' => Carbon::now()->subDays(3)->toDateString(),
        ], [
            [
                'medicamento_id' => $this->medConReceta->id,
                'dosis' => '2mg cada 24h',
                'frecuencia' => 'Diaria',
                'duracion' => '30 días',
                'cantidad_prescrita' => 2,
            ],
        ]);

        // 3. Vender ahora con la receta adjunta debe proceder con éxito
        $ventaExitosa = $this->ventaService->procesarVenta($this->cajero, [
            [
                'medicamento_id' => $this->medConReceta->id,
                'cantidad' => 2,
                'precio_unitario' => 100.00,
            ],
        ], [$receta->id]);

        $this->assertInstanceOf(Venta::class, $ventaExitosa);
        $this->assertTrue($ventaExitosa->recetas->contains($receta->id));
    }

    public function test_venta_falla_por_stock_insuficiente_y_revierte(): void
    {
        $this->crearLote($this->medSinReceta, ['cantidad_disponible' => 5]);

        $this->expectException(StockInsuficienteException::class);

        $this->ventaService->procesarVenta($this->cajero, [
            [
                'medicamento_id' => $this->medSinReceta->id,
                'cantidad' => 10,
                'precio_unitario' => 15.00,
            ],
        ]);
    }

    public function test_cancelar_venta_reintegra_stock(): void
    {
        $lote = $this->crearLote($this->medSinReceta, ['cantidad_disponible' => 30]);

        $venta = $this->ventaService->procesarVenta($this->cajero, [
            [
                'medicamento_id' => $this->medSinReceta->id,
                'cantidad' => 10,
                'precio_unitario' => 20.00,
            ],
        ]);

        $this->assertSame(20, $lote->fresh()->cantidad_disponible);

        $this->ventaService->cancelarVenta($venta, $this->cajero, 'Cliente no llevaba efectivo');

        $this->assertSame('CANCELADA', $venta->fresh()->estado);
        $this->assertSame(30, $lote->fresh()->cantidad_disponible);
    }

    public function test_devolucion_parcial_y_total_con_reingreso_a_inventario(): void
    {
        $lote = $this->crearLote($this->medSinReceta, ['cantidad_disponible' => 50]);

        $venta = $this->ventaService->procesarVenta($this->cajero, [
            [
                'medicamento_id' => $this->medSinReceta->id,
                'cantidad' => 10,
                'precio_unitario' => 25.00,
            ],
        ]);

        $this->assertSame(40, $lote->fresh()->cantidad_disponible);

        $ventaLote = $venta->detalles->first()->ventasLotes->first();

        // 1. Devolver 4 de 10 con reingreso
        $devolucion1 = $this->devolucionService->procesarDevolucion(
            $venta,
            $this->cajero,
            'Producto duplicado por error de cliente',
            [
                [
                    'venta_lote_id' => $ventaLote->id,
                    'cantidad' => 4,
                    'destino_producto' => 'REINGRESADO_INVENTARIO',
                ],
            ]
        );

        $this->assertSame('PROCESADA', $devolucion1->estado);
        $this->assertSame('DEVUELTA_PARCIAL', $venta->fresh()->estado);
        $this->assertSame(44, $lote->fresh()->cantidad_disponible); // 40 + 4 reingresados

        // 2. Devolver las 6 restantes a MERMA_DESTRUIDO (empaque roto)
        $devolucion2 = $this->devolucionService->procesarDevolucion(
            $venta,
            $this->cajero,
            'Empaque exterior roto',
            [
                [
                    'venta_lote_id' => $ventaLote->id,
                    'cantidad' => 6,
                    'destino_producto' => 'MERMA_DESTRUIDO',
                ],
            ]
        );

        $this->assertSame('DEVUELTA_TOTAL', $venta->fresh()->estado);
        // El stock no aumenta con MERMA_DESTRUIDO: sigue en 44
        $this->assertSame(44, $lote->fresh()->cantidad_disponible);
    }

    public function test_rechaza_devolucion_superior_a_la_cantidad_vendida(): void
    {
        $this->crearLote($this->medSinReceta, ['cantidad_disponible' => 20]);

        $venta = $this->ventaService->procesarVenta($this->cajero, [
            [
                'medicamento_id' => $this->medSinReceta->id,
                'cantidad' => 5,
                'precio_unitario' => 10.00,
            ],
        ]);

        $ventaLote = $venta->detalles->first()->ventasLotes->first();

        $this->expectException(DevolucionException::class);
        $this->expectExceptionMessageMatches('/excede el remanente devuelto permitido/');

        $this->devolucionService->procesarDevolucion(
            $venta,
            $this->cajero,
            'Intento de devolver más de lo comprado',
            [
                [
                    'venta_lote_id' => $ventaLote->id,
                    'cantidad' => 6, // Vendió 5, intenta devolver 6
                    'destino_producto' => 'REINGRESADO_INVENTARIO',
                ],
            ]
        );
    }

    public function test_rechaza_reingreso_a_inventario_si_el_lote_ya_caduco(): void
    {
        $lote = $this->crearLote($this->medSinReceta, [
            'cantidad_disponible' => 20,
            'fecha_caducidad' => Carbon::now()->addDays(5)->toDateString(),
        ]);

        $venta = $this->ventaService->procesarVenta($this->cajero, [
            [
                'medicamento_id' => $this->medSinReceta->id,
                'cantidad' => 5,
                'precio_unitario' => 10.00,
            ],
        ]);

        $ventaLote = $venta->detalles->first()->ventasLotes->first();

        // Simulamos que el lote vence antes de que el cliente regrese a hacer la devolución
        $lote->fecha_caducidad = Carbon::now()->subDay()->toDateString();
        $lote->save();

        $this->expectException(DevolucionException::class);
        $this->expectExceptionMessageMatches('/MERMA_DESTRUIDO/');

        $this->devolucionService->procesarDevolucion(
            $venta,
            $this->cajero,
            'Devolución tardía de producto vencido',
            [
                [
                    'venta_lote_id' => $ventaLote->id,
                    'cantidad' => 2,
                    'destino_producto' => 'REINGRESADO_INVENTARIO',
                ],
            ]
        );
    }
}
