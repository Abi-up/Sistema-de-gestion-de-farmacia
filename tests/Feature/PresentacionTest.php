<?php

namespace Tests\Feature;

use App\Models\Laboratorio;
use App\Models\Marca;
use App\Models\Presentacion;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Medicamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresentacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_presentaciones()
    {
        Presentacion::create(['nombre' => 'Tab', 'descripcion' => 'Tableta']);
        Presentacion::create(['nombre' => 'Syr', 'descripcion' => 'Jarabe']);

        $response = $this->getJson('/api/presentaciones');

        $response->assertOk()
                 ->assertJsonStructure([
                     'current_page',
                     'data',
                     'links',
                     'total',
                     'per_page',
                     'last_page',
                 ])
                 ->assertJsonCount(2, 'data');
    }

    public function test_shows_a_presentacion()
    {
        $pres = Presentacion::create(['nombre' => 'Caja', 'descripcion' => 'Caja 30 unidades']);

        $response = $this->getJson("/api/presentaciones/{$pres->id}");

        $response->assertOk()
                 ->assertJsonPath('id', $pres->id)
                 ->assertJsonPath('nombre', 'Caja');
    }

    public function test_creates_a_presentacion()
    {
        $payload = ['nombre' => 'Frasco', 'descripcion' => 'Frasco de 100ml'];

        $response = $this->postJson('/api/presentaciones', $payload);

        $response->assertCreated()
                 ->assertJsonPath('presentacion.nombre', 'Frasco');

        $this->assertDatabaseHas('presentaciones', $payload);
    }

    public function test_validates_required_fields_on_store()
    {
        $response = $this->postJson('/api/presentaciones', ['nombre' => '']);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['nombre']);
    }

    public function test_updates_a_presentacion()
    {
        $pres = Presentacion::create(['nombre' => 'Viejo', 'descripcion' => 'viejo']);

        $payload = ['nombre' => 'Nuevo', 'descripcion' => 'nuevo'];

        $response = $this->putJson("/api/presentaciones/{$pres->id}", $payload);

        $response->assertOk()
                 ->assertJsonPath('presentacion.nombre', 'Nuevo');

        $this->assertDatabaseHas('presentaciones', ['id' => $pres->id, 'nombre' => 'Nuevo']);
    }

    public function test_deletes_when_no_medicamentos()
    {
        $pres = Presentacion::create(['nombre' => 'Libre', 'descripcion' => 'sin meds']);

        $response = $this->deleteJson("/api/presentaciones/{$pres->id}");

        $response->assertOk()
                 ->assertJsonPath('message', 'Presentación eliminada exitosamente.');

        $this->assertDatabaseMissing('presentaciones', ['id' => $pres->id]);
    }

    public function test_rejects_deletion_when_has_medicamentos()
    {
        // Necesitamos datos de referencia para crear un Medicamento
        $cat = Categoria::create(['nombre' => 'Cat', 'descripcion' => '']);
        $mar = Marca::create(['nombre' => 'Marca', 'activo' => true]);
        $lab = Laboratorio::create(['nombre' => 'Lab', 'activo' => true]);
        $prov = Proveedor::create(['nombre' => 'Prov', 'rfc' => 'RFC123456', 'activo' => true]);

        $pres = Presentacion::create(['nombre' => 'Con Med', 'descripcion' => '']);
        Medicamento::create([
            'categoria_id'       => $cat->id,
            'presentacion_id'    => $pres->id,
            'marca_id'           => $mar->id,
            'laboratorio_id'     => $lab->id,
            'nombre'             => 'MedTest',
            'formula'            => 'Fórmula',
            'codigo_barras'      => '1234567890123',
            'requiere_receta'    => false,
            'activo'             => true,
        ]);

        $response = $this->deleteJson("/api/presentaciones/{$pres->id}");

        $response->assertStatus(422)
                 ->assertJsonPath('message',
                     'No se puede eliminar la presentación porque tiene medicamentos vinculados en el catálogo.');

        $this->assertDatabaseHas('presentaciones', ['id' => $pres->id]);
    }
}
