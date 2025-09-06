<?php

namespace Tests\Feature\Api;

use App\Models\Audio;
use App\Models\Serie;
use App\Models\Autor;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SerieCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_users_cannot_access_series_endpoints()
    {
        $response = $this->getJson('/api/series');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_create_a_serie_when_authenticated()
    {
        $user = User::factory()->create();
        $data = [
            'nombre' => 'Nueva Serie de Prueba',
            'comentario' => 'Este es un comentario de prueba.',
        ];

        $response = $this->actingAs($user)->postJson('/api/series', $data);

        $response->assertStatus(201)
                 ->assertJson(['nombre' => 'Nueva Serie de Prueba']);

        $this->assertDatabaseHas('series', $data);
    }

    /** @test */
    public function nombre_is_required_to_create_a_serie()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/series', ['nombre' => '']);
        $response->assertStatus(422)->assertJsonValidationErrors('nombre');
    }

    /** @test */
    public function nombre_must_be_unique_to_create_a_serie()
    {
        $user = User::factory()->create();
        Serie::create(['nombre' => 'Serie Existente']);
        $response = $this->actingAs($user)->postJson('/api/series', ['nombre' => 'Serie Existente']);
        $response->assertStatus(422)->assertJsonValidationErrors('nombre');
    }

    /** @test */
    public function it_can_show_a_serie_when_authenticated()
    {
        $user = User::factory()->create();
        $serie = Serie::create(['nombre' => 'Serie para ver']);
        $response = $this->actingAs($user)->getJson('/api/series/' . $serie->id);
        $response->assertStatus(200)->assertJson(['nombre' => 'Serie para ver']);
    }

    /** @test */
    public function it_can_update_a_serie_when_authenticated()
    {
        $user = User::factory()->create();
        $serie = Serie::create(['nombre' => 'Nombre Original']);
        $data = [
            'nombre' => 'Nombre Actualizado',
            'comentario' => 'Comentario actualizado.',
        ];
        $response = $this->actingAs($user)->putJson('/api/series/' . $serie->id, $data);
        $response->assertStatus(200)->assertJson(['nombre' => 'Nombre Actualizado']);
        $this->assertDatabaseHas('series', $data);
    }

    /** @test */
    public function it_can_delete_a_serie_and_updates_associated_audios()
    {
        $user = User::factory()->create();
        $autor = Autor::create(['nombre' => 'Test Autor']);
        $categoria = Categoria::create(['nombre' => 'Test Categoria']);
        $serie = Serie::create(['nombre' => 'Serie a eliminar']);
        $audio = Audio::create([
            'titulo' => 'Audio de prueba',
            'archivo' => 'path/to/file.mp3',
            'estado' => 'Normal',
            'autor_id' => $autor->id,
            'categoria_id' => $categoria->id,
            'serie_id' => $serie->id,
        ]);

        $response = $this->actingAs($user)->deleteJson('/api/series/' . $serie->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('series', ['id' => $serie->id]);
        $this->assertDatabaseHas('audios', [
            'id' => $audio->id,
            'estado' => 'Pendiente',
        ]);
    }

    /** @test */
    public function it_can_list_and_filter_series_when_authenticated()
    {
        $user = User::factory()->create();
        Serie::create(['nombre' => 'Serie A']);
        Serie::create(['nombre' => 'Serie B']);

        $response = $this->actingAs($user)->getJson('/api/series?q=B');
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonFragment(['nombre' => 'Serie B']);
    }
}
