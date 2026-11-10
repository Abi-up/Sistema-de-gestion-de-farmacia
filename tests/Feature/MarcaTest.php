<?php

namespace Tests\Feature;

use App\Models\Marca;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarcaTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_marcas()
    {
        Marca::create(['nombre' => 'Marca1', 'activo' => true]);
        Marca::create(['nombre' => 'Marca2', 'activo' => true]);

        $response = $this->getJson('/api/marcas');

        $response->assertOk()
                 ->assertJsonCount(2, 'data');
    }

    public function test_shows_a_marca()
    {
        $marca = Marca::create(['nombre' => 'SoloMarca', 'activo' => true]);

        $response = $this->getJson("/api/marcas/{$marca->id}");

        $response->assertOk()
                 ->assertJsonPath('id', $marca->id)
                 ->assertJsonPath('nombre', 'SoloMarca');
    }

    public function test_creates_a_marca()
    {
        $payload = ['nombre' => 'NuevaMarca'];

        $response = $this->postJson('/api/marcas', $payload);

        $response->assertCreated()
                 ->assertJsonPath('marca.nombre', 'NuevaMarca');

        $this->assertDatabaseHas('marcas', $payload);
    }

    public function test_validates_required_fields_on_store()
    {
        $response = $this->postJson('/api/marcas', ['nombre' => '']);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['nombre']);
    }

    public function test_validates_unique_nombre_on_store()
    {
        Marca::create(['nombre' => 'DupMarca']);

        $response = $this->postJson('/api/marcas', ['nombre' => 'DupMarca']);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['nombre']);
    }

    public function test_updates_a_marca()
    {
        $marca = Marca::create(['nombre' => 'Vieja', 'activo' => true]);

        $payload = ['nombre' => 'Actualizada', 'activo' => false];

        $response = $this->putJson("/api/marcas/{$marca->id}", $payload);

        $response->assertOk()
                 ->assertJsonPath('marca.nombre', 'Actualizada');

        $this->assertDatabaseHas('marcas', ['id' => $marca->id, 'nombre' => 'Actualizada']);
    }

    public function test_validates_unique_nombre_on_update()
    {
        Marca::create(['nombre' => 'Primera']);
        $marca = Marca::create(['nombre' => 'Segunda']);

        $response = $this->putJson("/api/marcas/{$marca->id}", ['nombre' => 'Primera']);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['nombre']);
    }

    public function test_deactivates_a_marca()
    {
        $marca = Marca::create(['nombre' => 'ParaDesactivar', 'activo' => true]);

        $response = $this->deleteJson("/api/marcas/{$marca->id}");

        $response->assertOk()
                 ->assertJsonPath('marca.activo', false);

        $this->assertDatabaseHas('marcas', ['id' => $marca->id, 'activo' => false]);
    }
}
