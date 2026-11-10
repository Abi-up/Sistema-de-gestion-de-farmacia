<?php

namespace Tests\Feature;

use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_categorias()
    {
        Categoria::create(['nombre' => 'Cat A', 'descripcion' => 'Desc A']);
        Categoria::create(['nombre' => 'Cat B', 'descripcion' => 'Desc B']);

        $response = $this->getJson('/api/categorias');

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

    public function test_shows_a_single_categoria()
    {
        $cat = Categoria::create(['nombre' => 'Solo', 'descripcion' => 'Solo desc']);

        $response = $this->getJson("/api/categorias/{$cat->id}");

        $response->assertOk()
                 ->assertJsonPath('id', $cat->id)
                 ->assertJsonPath('nombre', 'Solo');
    }

    public function test_creates_a_categoria()
    {
        $payload = ['nombre' => 'Nueva', 'descripcion' => 'Nueva desc'];

        $response = $this->postJson('/api/categorias', $payload);

        $response->assertCreated()
                 ->assertJsonPath('categoria.nombre', 'Nueva');

        $this->assertDatabaseHas('categorias', $payload);
    }

    public function test_validates_required_fields_on_store()
    {
        $response = $this->postJson('/api/categorias', ['nombre' => '']);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['nombre']);
    }

    public function test_validates_unique_nombre_on_store()
    {
        Categoria::create(['nombre' => 'Duplicado']);

        $response = $this->postJson('/api/categorias', ['nombre' => 'Duplicado']);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['nombre']);
    }

    public function test_updates_a_categoria()
    {
        $cat = Categoria::create(['nombre' => 'Viejo', 'descripcion' => 'old']);

        $payload = ['nombre' => 'Nuevo', 'descripcion' => 'new'];

        $response = $this->putJson("/api/categorias/{$cat->id}", $payload);

        $response->assertOk()
                 ->assertJsonPath('categoria.nombre', 'Nuevo');

        $this->assertDatabaseHas('categorias', ['id' => $cat->id, 'nombre' => 'Nuevo']);
    }

    public function test_validates_unique_nombre_on_update()
    {
        Categoria::create(['nombre' => 'Primera']);
        $cat = Categoria::create(['nombre' => 'Segunda']);

        $response = $this->putJson("/api/categorias/{$cat->id}", ['nombre' => 'Primera']);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['nombre']);
    }

    public function test_deactivates_a_categoria()
    {
        $cat = Categoria::create(['nombre' => 'Para eliminar']);

        $response = $this->deleteJson("/api/categorias/{$cat->id}");

        $response->assertOk()
                 ->assertJsonPath('categoria.activo', false);

        $this->assertDatabaseHas('categorias', ['id' => $cat->id, 'activo' => false]);
    }
}
