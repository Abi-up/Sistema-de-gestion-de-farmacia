<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\{
    User, Rol, Permiso, Bitacora, RecuperacionContrasena,
    Categoria, Presentacion, Marca, Laboratorio, Proveedor,
    Medicamento, Receta, DetalleReceta, Compra, DetalleCompra,
    Recepcion, DetalleRecepcion, Lote, Venta, DetalleVenta,
    VentaLote, Devolucion, DetalleDevolucion, MovimientoInventario
};
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo, HasMany, BelongsToMany
};

class ModelsRelationshipTest extends TestCase
{
    /**
     * Prueba las relaciones de Usuarios, Roles, Permisos, Bitácora y Recuperación.
     */
    public function test_relaciones_modulo_usuarios_roles_permisos(): void
    {
        $user = new User();
        $this->assertInstanceOf(BelongsToMany::class, $user->roles());
        $this->assertSame('usuario_rol', $user->roles()->getTable());
        $this->assertSame('usuario_id', $user->roles()->getForeignPivotKeyName());
        $this->assertSame('rol_id', $user->roles()->getRelatedPivotKeyName());
        $this->assertInstanceOf(HasMany::class, $user->bitacoras());
        $this->assertInstanceOf(HasMany::class, $user->recuperacionesContrasena());
        $this->assertInstanceOf(HasMany::class, $user->compras());
        $this->assertInstanceOf(HasMany::class, $user->recepciones());
        $this->assertInstanceOf(HasMany::class, $user->ventas());
        $this->assertInstanceOf(HasMany::class, $user->devoluciones());
        $this->assertInstanceOf(HasMany::class, $user->movimientosInventario());

        $rol = new Rol();
        $this->assertInstanceOf(BelongsToMany::class, $rol->usuarios());
        $this->assertSame('usuario_rol', $rol->usuarios()->getTable());
        $this->assertSame('rol_id', $rol->usuarios()->getForeignPivotKeyName());
        $this->assertSame('usuario_id', $rol->usuarios()->getRelatedPivotKeyName());

        $this->assertInstanceOf(BelongsToMany::class, $rol->permisos());
        $this->assertSame('rol_permiso', $rol->permisos()->getTable());
        $this->assertSame('rol_id', $rol->permisos()->getForeignPivotKeyName());
        $this->assertSame('permiso_id', $rol->permisos()->getRelatedPivotKeyName());

        $permiso = new Permiso();
        $this->assertInstanceOf(BelongsToMany::class, $permiso->roles());
        $this->assertSame('rol_permiso', $permiso->roles()->getTable());
        $this->assertSame('permiso_id', $permiso->roles()->getForeignPivotKeyName());
        $this->assertSame('rol_id', $permiso->roles()->getRelatedPivotKeyName());

        $bitacora = new Bitacora();
        $this->assertInstanceOf(BelongsTo::class, $bitacora->usuario());
        $this->assertSame('usuario_id', $bitacora->usuario()->getForeignKeyName());

        $recuperacion = new RecuperacionContrasena();
        $this->assertInstanceOf(BelongsTo::class, $recuperacion->usuario());
        $this->assertSame('usuario_id', $recuperacion->usuario()->getForeignKeyName());
    }

    /**
     * Prueba las relaciones de Catálogos y Medicamentos.
     */
    public function test_relaciones_modulo_catalogos_y_medicamentos(): void
    {
        $categoria = new Categoria();
        $this->assertInstanceOf(HasMany::class, $categoria->medicamentos());
        $this->assertSame('categoria_id', $categoria->medicamentos()->getForeignKeyName());

        $presentacion = new Presentacion();
        $this->assertInstanceOf(HasMany::class, $presentacion->medicamentos());
        $this->assertSame('presentacion_id', $presentacion->medicamentos()->getForeignKeyName());

        $marca = new Marca();
        $this->assertInstanceOf(HasMany::class, $marca->medicamentos());
        $this->assertSame('marca_id', $marca->medicamentos()->getForeignKeyName());

        $laboratorio = new Laboratorio();
        $this->assertInstanceOf(HasMany::class, $laboratorio->medicamentos());
        $this->assertSame('laboratorio_id', $laboratorio->medicamentos()->getForeignKeyName());

        $proveedor = new Proveedor();
        $this->assertInstanceOf(HasMany::class, $proveedor->compras());
        $this->assertSame('proveedor_id', $proveedor->compras()->getForeignKeyName());

        $medicamento = new Medicamento();
        $this->assertInstanceOf(BelongsTo::class, $medicamento->categoria());
        $this->assertSame('categoria_id', $medicamento->categoria()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $medicamento->presentacion());
        $this->assertSame('presentacion_id', $medicamento->presentacion()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $medicamento->marca());
        $this->assertSame('marca_id', $medicamento->marca()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $medicamento->laboratorio());
        $this->assertSame('laboratorio_id', $medicamento->laboratorio()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $medicamento->lotes());
        $this->assertSame('medicamento_id', $medicamento->lotes()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $medicamento->detallesReceta());
        $this->assertSame('medicamento_id', $medicamento->detallesReceta()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $medicamento->detallesCompra());
        $this->assertSame('medicamento_id', $medicamento->detallesCompra()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $medicamento->detallesVenta());
        $this->assertSame('medicamento_id', $medicamento->detallesVenta()->getForeignKeyName());
    }

    /**
     * Prueba las relaciones del módulo de Recetas.
     */
    public function test_relaciones_modulo_recetas(): void
    {
        $receta = new Receta();
        $this->assertInstanceOf(HasMany::class, $receta->detalles());
        $this->assertSame('receta_id', $receta->detalles()->getForeignKeyName());

        $this->assertInstanceOf(BelongsToMany::class, $receta->ventas());
        $this->assertSame('venta_receta', $receta->ventas()->getTable());
        $this->assertSame('receta_id', $receta->ventas()->getForeignPivotKeyName());
        $this->assertSame('venta_id', $receta->ventas()->getRelatedPivotKeyName());

        $detalleReceta = new DetalleReceta();
        $this->assertInstanceOf(BelongsTo::class, $detalleReceta->receta());
        $this->assertSame('receta_id', $detalleReceta->receta()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $detalleReceta->medicamento());
        $this->assertSame('medicamento_id', $detalleReceta->medicamento()->getForeignKeyName());
    }

    /**
     * Prueba las relaciones de Compras y Recepciones.
     */
    public function test_relaciones_modulo_compras_y_recepciones(): void
    {
        $compra = new Compra();
        $this->assertInstanceOf(BelongsTo::class, $compra->proveedor());
        $this->assertSame('proveedor_id', $compra->proveedor()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $compra->usuario());
        $this->assertSame('usuario_id', $compra->usuario()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $compra->detalles());
        $this->assertSame('compra_id', $compra->detalles()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $compra->recepciones());
        $this->assertSame('compra_id', $compra->recepciones()->getForeignKeyName());

        $detalleCompra = new DetalleCompra();
        $this->assertInstanceOf(BelongsTo::class, $detalleCompra->compra());
        $this->assertSame('compra_id', $detalleCompra->compra()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $detalleCompra->medicamento());
        $this->assertSame('medicamento_id', $detalleCompra->medicamento()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $detalleCompra->detallesRecepcion());
        $this->assertSame('detalle_compra_id', $detalleCompra->detallesRecepcion()->getForeignKeyName());

        $recepcion = new Recepcion();
        $this->assertInstanceOf(BelongsTo::class, $recepcion->compra());
        $this->assertSame('compra_id', $recepcion->compra()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $recepcion->usuario());
        $this->assertSame('usuario_id', $recepcion->usuario()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $recepcion->detalles());
        $this->assertSame('recepcion_id', $recepcion->detalles()->getForeignKeyName());

        $detalleRecepcion = new DetalleRecepcion();
        $this->assertInstanceOf(BelongsTo::class, $detalleRecepcion->recepcion());
        $this->assertSame('recepcion_id', $detalleRecepcion->recepcion()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $detalleRecepcion->detalleCompra());
        $this->assertSame('detalle_compra_id', $detalleRecepcion->detalleCompra()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $detalleRecepcion->lotes());
        $this->assertSame('detalle_recepcion_id', $detalleRecepcion->lotes()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $detalleRecepcion->movimientosInventario());
        $this->assertSame('detalle_recepcion_id', $detalleRecepcion->movimientosInventario()->getForeignKeyName());
    }

    /**
     * Prueba las relaciones de Lotes, Ventas y Asignación de Lotes.
     */
    public function test_relaciones_modulo_lotes_y_ventas(): void
    {
        $lote = new Lote();
        $this->assertInstanceOf(BelongsTo::class, $lote->medicamento());
        $this->assertSame('medicamento_id', $lote->medicamento()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $lote->detalleRecepcion());
        $this->assertSame('detalle_recepcion_id', $lote->detalleRecepcion()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $lote->ventasLotes());
        $this->assertSame('lote_id', $lote->ventasLotes()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $lote->movimientosInventario());
        $this->assertSame('lote_id', $lote->movimientosInventario()->getForeignKeyName());

        $venta = new Venta();
        $this->assertInstanceOf(BelongsTo::class, $venta->usuario());
        $this->assertSame('usuario_id', $venta->usuario()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $venta->detalles());
        $this->assertSame('venta_id', $venta->detalles()->getForeignKeyName());

        $this->assertInstanceOf(BelongsToMany::class, $venta->recetas());
        $this->assertSame('venta_receta', $venta->recetas()->getTable());
        $this->assertSame('venta_id', $venta->recetas()->getForeignPivotKeyName());
        $this->assertSame('receta_id', $venta->recetas()->getRelatedPivotKeyName());

        $this->assertInstanceOf(HasMany::class, $venta->devoluciones());
        $this->assertSame('venta_id', $venta->devoluciones()->getForeignKeyName());

        $detalleVenta = new DetalleVenta();
        $this->assertInstanceOf(BelongsTo::class, $detalleVenta->venta());
        $this->assertSame('venta_id', $detalleVenta->venta()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $detalleVenta->medicamento());
        $this->assertSame('medicamento_id', $detalleVenta->medicamento()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $detalleVenta->ventasLotes());
        $this->assertSame('detalle_venta_id', $detalleVenta->ventasLotes()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $detalleVenta->movimientosInventario());
        $this->assertSame('detalle_venta_id', $detalleVenta->movimientosInventario()->getForeignKeyName());

        $ventaLote = new VentaLote();
        $this->assertInstanceOf(BelongsTo::class, $ventaLote->detalleVenta());
        $this->assertSame('detalle_venta_id', $ventaLote->detalleVenta()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $ventaLote->lote());
        $this->assertSame('lote_id', $ventaLote->lote()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $ventaLote->detallesDevolucion());
        $this->assertSame('venta_lote_id', $ventaLote->detallesDevolucion()->getForeignKeyName());
    }

    /**
     * Prueba las relaciones de Devoluciones y Movimientos de Inventario.
     */
    public function test_relaciones_modulo_devoluciones_y_movimientos(): void
    {
        $devolucion = new Devolucion();
        $this->assertInstanceOf(BelongsTo::class, $devolucion->venta());
        $this->assertSame('venta_id', $devolucion->venta()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $devolucion->usuario());
        $this->assertSame('usuario_id', $devolucion->usuario()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $devolucion->detalles());
        $this->assertSame('devolucion_id', $devolucion->detalles()->getForeignKeyName());

        $detalleDevolucion = new DetalleDevolucion();
        $this->assertInstanceOf(BelongsTo::class, $detalleDevolucion->devolucion());
        $this->assertSame('devolucion_id', $detalleDevolucion->devolucion()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $detalleDevolucion->ventaLote());
        $this->assertSame('venta_lote_id', $detalleDevolucion->ventaLote()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $detalleDevolucion->movimientosInventario());
        $this->assertSame('detalle_devolucion_id', $detalleDevolucion->movimientosInventario()->getForeignKeyName());

        $movimiento = new MovimientoInventario();
        $this->assertInstanceOf(BelongsTo::class, $movimiento->lote());
        $this->assertSame('lote_id', $movimiento->lote()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $movimiento->usuario());
        $this->assertSame('usuario_id', $movimiento->usuario()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $movimiento->detalleRecepcion());
        $this->assertSame('detalle_recepcion_id', $movimiento->detalleRecepcion()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $movimiento->detalleVenta());
        $this->assertSame('detalle_venta_id', $movimiento->detalleVenta()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $movimiento->detalleDevolucion());
        $this->assertSame('detalle_devolucion_id', $movimiento->detalleDevolucion()->getForeignKeyName());
    }
}
