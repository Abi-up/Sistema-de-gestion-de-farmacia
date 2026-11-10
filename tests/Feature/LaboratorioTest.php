<?php

namespace Tests\Feature;

use App\Models\Laboratorio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaboratorioTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_laboratorios()
    {
        Laboratorio::create(['nombre' => 'Lab A', 'activo' => true]);
        Laboratorio::create(['nombre' => 'Lab B', 'activo' => true]);

        $response = $this->getJson('/api/laboratorios');

        $response->assertOk()
                 ->assertJsonCount(2, 'data');
    }

    public function test_shows_a_laboratorio()
    {
        $lab = Laboratorio::create(['nombre' => 'SoloLab', 'activo' => true]);

        $response = $this->getJson("/api/laboratorios/{$lab->id}");

        $response->assertOk()
                 ->assertJsonPath('id', $lab->id)
                 ->assertJsonPath('nombre', 'SoloLab');
    }

    public function test_creates_a_laboratorio()
    {
        $payload = ['nombre' => 'NuevoLab'];

        $response = $this->postJson('/api/laboratorios', $payload);

        $response->assertCreated()
                 ->assertJsonPath('laboratorio.nombre', 'NuevoLab');

        $this->assertDatabaseHas('laboratorios', $payload);
    }

    public function test_validates_required_fields_on_store()
    {
        $response = $this->postJson('/api/laboratorios', ['nombre' => '']);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['nombre']);
    }

    public function test_validates_unique_nombre_on_store()
    {
        Laboratorio::create(['nombre' => 'DupLab']);

        $response = $this->postJson('/api/laboratorios', ['nombre' => 'DupLab']);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['nombre']);
    }

    public function test_updates_a_laboratorio()
    {
        $lab = Laboratorio::create(['nombre' => 'ViejoLab', 'activo' => true]);

        $payload = ['nombre' => 'NuevoLab', 'activo' => false];

        $response = $this->putJson("/api/laboratorios/{$lab->id}", $payload);

        $response->assertOk()
                 ->assertJsonPath('laboratorio.nombre', 'NuevoLab');

        $this->assertDatabaseHas('laboratorios', ['id' => $lab->id, 'nombre' => 'NuevoLab']);
    }

    public function test_validates_unique_nombre_on_update()
    {
        Laboratorio::create(['nombre' => 'Primera']);
        $lab = Laboratorio::create(['nombre' => 'Segunda']);

        $response = $this->putJson("/api/laboratorios/{$lab->id}", ['nombre' => 'Primera']);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['nombre']);
    }

    public function test_deactivates_a_laboratorio()
    {
        $lab = Laboratorio::create(['nombre' => 'ParaDesactivar', 'activo' => true]);

        $response = $this->deleteJson("/api/laboratorios/{$lab->id}");

        $response->assertOk()
                 ->assertJsonPath('laboratorio.activo', false);

        $this->assertDatabaseHas('laboratorios', ['id' => $lab->id, 'activo' => false]);
    }
}
