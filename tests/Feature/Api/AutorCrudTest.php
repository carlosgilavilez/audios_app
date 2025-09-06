<?php

namespace Tests\Feature\Api;

use App\Models\Audio;
use App\Models\Autor;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutorCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_users_cannot_access_autores_endpoints()
    {
        $response = $this->getJson('/api/autores');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_create_an_autor_when_authenticated()
    {
        $user = User::factory()->create();
        $data = [
            'nombre' => 'Nuevo Autor de Prueba',
            'comentario' => 'Este es un comentario de prueba.',
        ];

        $response = $this->actingAs($user)->postJson('/api/autores', $data);

        $response->assertStatus(201)
                 ->assertJson(['nombre' => 'Nuevo Autor de Prueba']);

        $this->assertDatabaseHas('autores', $data);
    }

    /** @test */
    public function nombre_is_required_to_create_an_autor()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/autores', ['nombre' => '']);
        $response->assertStatus(422)->assertJsonValidationErrors('nombre');
    }

    /** @test */
    public function nombre_must_be_unique_to_create_an_autor()
    {
        $user = User::factory()->create();
        Autor::create(['nombre' => 'Autor Existente']);
        $response = $this->actingAs($user)->postJson('/api/autores', ['nombre' => 'Autor Existente']);
        $response->assertStatus(422)->assertJsonValidationErrors('nombre');
    }

    /** @test */
    public function it_can_show_an_autor_when_authenticated()
    {
        $user = User::factory()->create();
        $autor = Autor::create(['nombre' => 'Autor para ver']);
        $response = $this->actingAs($user)->getJson('/api/autores/' . $autor->id);
        $response->assertStatus(200)->assertJson(['nombre' => 'Autor para ver']);
    }

    /** @test */
    public function it_can_update_an_autor_when_authenticated()
    {
        $user = User::factory()->create();
        $autor = Autor::create(['nombre' => 'Nombre Original']);
        $data = [
            'nombre' => 'Nombre Actualizado',
            'comentario' => 'Comentario actualizado.',
        ];
        $response = $this->actingAs($user)->putJson('/api/autores/' . $autor->id, $data);
        $response->assertStatus(200)->assertJson(['nombre' => 'Nombre Actualizado']);
        $this->assertDatabaseHas('autores', $data);
    }

    /** @test */
    public function it_can_delete_an_autor_when_authenticated()
    {
        $user = User::factory()->create();
        $categoria = Categoria::create(['nombre' => 'Test Categoria']);
        $autor = Autor::create(['nombre' => 'Autor a eliminar']);
        $audio = Audio::create([
            'titulo' => 'Audio de prueba',
            'archivo' => 'path/to/file.mp3',
            'estado' => 'Normal',
            'autor_id' => $autor->id,
            'categoria_id' => $categoria->id,
        ]);

        $response = $this->actingAs($user)->deleteJson('/api/autores/' . $autor->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('autores', ['id' => $autor->id]);
        $this->assertDatabaseHas('audios', [
            'id' => $audio->id,
            'estado' => 'Pendiente',
        ]);
    }

    /** @test */
    public function it_can_list_and_filter_autores_when_authenticated()
    {
        $user = User::factory()->create();
        Autor::create(['nombre' => 'Juan Perez']);
        Autor::create(['nombre' => 'Maria Lopez']);

        $response = $this->actingAs($user)->getJson('/api/autores?q=lopez');
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonFragment(['nombre' => 'Maria Lopez']);
    }
}
