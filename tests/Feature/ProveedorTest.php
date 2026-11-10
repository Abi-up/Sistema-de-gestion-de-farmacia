<?php

namespace Tests\Feature;

use App\Models\Marca;
use App\Models\Laboratorio;
use App\Models\Presentacion;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Medicamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProveedorTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_proveedores()
    {
        Proveedor::create(['nombre' => 'Prov1', 'rfc' => 'RFC111111', 'activo' => true]);
        Proveedor::create(['nombre' => 'Prov2', 'rfc' => 'RFC222222', 'activo' => true]);

        $response = $this->getJson('/api/proveedores');

        $response->assertOk()
                 ->assertJsonCount(2, 'data');
    }

    public function test_shows_a_proveedor()
    {
        $prov = Proveedor::create(['nombre' => 'SoloProv', 'rfc' => 'RFCSOLO123', 'activo' => true]);

        $response = $this->getJson("/api/proveedores/{$prov->id}");

        $response->assertOk()
                 ->assertJsonPath('id', $prov->id)
                 ->assertJsonPath('nombre', 'SoloProv')
                 ->assertJsonPath('rfc', 'RFCSOLO123');
    }

    public function test_creates_a_proveedor()
    {
        $payload = ['nombre' => 'NuevoProv', 'rfc' => 'RFCNEW12345'];

        $response = $this->postJson('/api/proveedores', $payload);

        $response->assertCreated()
                 ->assertJsonPath('proveedor.nombre', 'NuevoProv');

        $this->assertDatabaseHas('proveedores', $payload);
    }

    public function test_validates_required_fields_on_store()
    {
        $response = $this->postJson('/api/proveedores', ['nombre' => '', 'rfc' => '']);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['nombre']);
    }

    public function test_allow_duplicate_nombre_on_store()
    {
        Proveedor::create(['nombre' => 'DupProv', 'rfc' => 'RFC111', 'activo' => true]);

        $response = $this->postJson('/api/proveedores', [
            'nombre' => 'DupProv',
            'rfc'    => 'UniqueRFC',
        ]);

        $response->assertCreated()
                 ->assertJsonPath('proveedor.nombre', 'DupProv');
    }

    public function test_validates_unique_rfc_on_store()
    {
        Proveedor::create(['nombre' => 'DupProv', 'rfc' => 'RFC111', 'activo' => true]);

        $response = $this->postJson('/api/proveedores', [
            'nombre' => 'UniqueName',
            'rfc'    => 'RFC111',
        ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['rfc']);
    }

    public function test_updates_a_proveedor()
    {
        $prov = Proveedor::create(['nombre' => 'Viejo', 'rfc' => 'RFCVIEJO', 'activo' => true]);

        $payload = ['nombre' => 'Actualizado', 'rfc' => 'RFCACT'];

        $response = $this->putJson("/api/proveedores/{$prov->id}", $payload);

        $response->assertOk()
                 ->assertJsonPath('proveedor.nombre', 'Actualizado');

        $this->assertDatabaseHas('proveedores', ['id' => $prov->id, 'nombre' => 'Actualizado']);
    }

    public function test_validates_unique_fields_on_update()
    {
        Proveedor::create(['nombre' => 'Primera', 'rfc' => 'RFC1', 'activo' => true]);
        $prov = Proveedor::create(['nombre' => 'Segunda', 'rfc' => 'RFC2', 'activo' => true]);

        $response = $this->putJson("/api/proveedores/{$prov->id}", [
            'nombre' => 'Primera',
            'rfc'    => 'RFC1',
        ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['rfc']);
    }

    public function test_deactivates_a_proveedor()
    {
        $prov = Proveedor::create(['nombre' => 'ParaDesactivar', 'rfc' => 'RFCDEACT', 'activo' => true]);

        $response = $this->deleteJson("/api/proveedores/{$prov->id}");

        $response->assertOk()
                 ->assertJsonPath('proveedor.activo', false);

        $this->assertDatabaseHas('proveedores', ['id' => $prov->id, 'activo' => false]);
    }
}
