<?php

namespace Tests\Feature;

use App\Models\Audio;
use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Libro;
use App\Models\Serie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAudioFiltersTest extends TestCase
{
    use RefreshDatabase;

    private Autor $author;

    private Categoria $category;

    private Libro $book;

    private Serie $series;

    protected function setUp(): void
    {
        parent::setUp();
        $this->author = Autor::create(['nombre' => 'Andrés Birch']);
        $this->category = Categoria::firstOrCreate(['nombre' => 'Predicaciones']);
        $this->book = Libro::create(['nombre' => 'Génesis', 'abreviatura' => 'Gn']);
        $this->series = Serie::create(['nombre' => 'Vida cristiana']);
    }

    private function audio(array $attributes = []): Audio
    {
        return Audio::create(array_merge([
            'titulo' => 'El principio', 'archivo' => 'audios/test.mp3',
            'estado' => 'Publicado', 'fecha_publicacion' => '2026-11-08',
            'autor_id' => $this->author->id, 'categoria_id' => $this->category->id,
            'libro_id' => $this->book->id, 'cita_biblica' => '1:1-5',
            'serie_id' => $this->series->id,
        ], $attributes));
    }

    public function test_typed_author_becomes_a_selected_filter_with_embed_context(): void
    {
        $this->audio();
        $url = '/audios?'.http_build_query(['q' => 'andres birch', 'embed' => 1, 'view' => 'table', 'per_page' => 10, 'page' => 3]);
        $redirect = $this->get($url)->assertRedirect();
        parse_str(parse_url($redirect->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertEquals($this->author->id, $query['autor_id']);
        $this->assertEquals(1, $query['embed']);
        $this->assertEquals(10, $query['per_page']);
        $this->assertArrayNotHasKey('q', $query);
        $this->assertArrayNotHasKey('page', $query);

        $response = $this->get($redirect->headers->get('Location'))->assertOk();
        $response->assertViewHas('filters', fn ($filters) => $filters['autor_id'] === $this->author->id && $filters['q'] === null);
        $response->assertSee('Autor: Andrés Birch');
        $response->assertSee('value="'.$this->author->id.'" selected', false);
        $response->assertSee('name="per_page" value="10"', false);
    }

    public function test_fifty_audios_load_by_default_and_an_explicit_page_size_is_respected(): void
    {
        for ($i = 0; $i < 51; $i++) {
            $this->audio();
        }
        foreach (['/audios', '/embed'] as $url) {
            $this->get($url)->assertOk()->assertViewHas('perPage', 50)
                ->assertViewHas('audios', fn ($audios) => $audios->count() === 50 && $audios->total() === 51)
                ->assertSee('name="per_page" value="50"', false);
        }
        $this->get('/audios?per_page=25')->assertOk()->assertViewHas('perPage', 25)
            ->assertViewHas('audios', fn ($audios) => $audios->count() === 25 && $audios->total() === 51);
    }

    public function test_unambiguous_partial_author_and_category_are_captured(): void
    {
        $this->audio();
        foreach (['Birch' => ['autor_id', $this->author->id], 'Predicaciones' => ['categoria_id', $this->category->id],
            'Vida cristiana' => ['serie_id', $this->series->id]] as $term => [$key, $id]) {
            $response = $this->get('/audios?'.http_build_query(['q' => $term]))->assertRedirect();
            parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
            $this->assertEquals($id, $query[$key]);
        }
    }

    public function test_book_name_without_accents_or_abbreviation_filters_the_book(): void
    {
        $expected = $this->audio();
        $this->audio(['titulo' => 'Otro mensaje', 'libro_id' => null]);
        foreach (['genesis', 'Gn'] as $term) {
            $redirect = $this->get('/audios?'.http_build_query(['q' => $term]))->assertRedirect();
            $this->get($redirect->headers->get('Location'))->assertOk()
                ->assertViewHas('audios', fn ($audios) => $audios->pluck('id')->all() === [$expected->id])
                ->assertSee('Libro de la Biblia: Génesis');
        }
    }

    public function test_ambiguous_text_stays_a_general_search(): void
    {
        $this->audio();
        Autor::create(['nombre' => 'Andrés Reid']);
        $this->get('/audios?q='.urlencode('Andrés'))->assertOk()
            ->assertViewHas('filters', fn ($filters) => $filters['q'] === 'Andrés' && $filters['autor_id'] === null);
    }

    public function test_explicit_author_is_not_replaced_by_a_different_typed_author(): void
    {
        $other = Autor::create(['nombre' => 'Carlos Velarde']);
        $this->audio();
        $this->get('/audios?'.http_build_query(['q' => $other->nombre, 'autor_id' => $this->author->id]))
            ->assertOk()->assertViewHas('resultCount', 0)
            ->assertViewHas('filters', fn ($filters) => $filters['autor_id'] === $this->author->id && $filters['q'] === $other->nombre);
    }

    public function test_all_criteria_combine_and_only_public_audios_are_returned(): void
    {
        $expected = $this->audio();
        $this->audio(['estado' => 'Pendiente']);
        $this->audio(['fecha_publicacion' => '2025-11-08']);
        $this->audio(['libro_id' => null]);
        $this->audio(['autor_id' => null]);
        $this->audio(['categoria_id' => null]);
        $this->audio(['serie_id' => null]);
        $query = ['autor_id' => $this->author->id, 'categoria_id' => $this->category->id, 'libro_id' => $this->book->id,
            'anio' => 2026, 'serie_id' => $this->series->id, 'embed' => 1];
        $this->get('/audios?'.http_build_query($query))->assertOk()
            ->assertViewHas('audios', fn ($audios) => $audios->pluck('id')->all() === [$expected->id])
            ->assertSee('Serie: Vida cristiana');
    }

    public function test_typed_year_is_captured(): void
    {
        $this->audio();
        foreach (['2026'] as $term) {
            $redirect = $this->get('/embed?'.http_build_query(['search' => $term]))->assertRedirect();
            $this->assertEquals('/embed', parse_url($redirect->headers->get('Location'), PHP_URL_PATH));
            $response = $this->get($redirect->headers->get('Location'))->assertOk();
            $response->assertViewHas('isEmbed', true)->assertViewHas('resultCount', 1);
            $response->assertViewHas('filters', fn ($filters) => $filters['q'] === null && $filters['anio'] === 2026);
        }
    }

    public function test_pagination_chips_and_clear_links_keep_the_other_filters(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->audio();
        }
        $query = ['autor_id' => $this->author->id, 'libro_id' => $this->book->id, 'year' => 2026,
            'serie_id' => $this->series->id, 'embed' => 1, 'view' => 'table', 'per_page' => 10];
        $response = $this->get('/audios?'.http_build_query($query))->assertOk();
        $response->assertViewHas('audios', function ($audios) use ($query) {
            parse_str(parse_url($audios->nextPageUrl(), PHP_URL_QUERY), $next);
            $this->assertEquals(array_merge($query, ['page' => 2]), $next);

            return $audios->total() === 12;
        });
        foreach ($response->viewData('filterChips') as $chip) {
            parse_str(parse_url($chip['remove_url'], PHP_URL_QUERY), $params);
            $this->assertArrayNotHasKey($chip['key'], $params);
            $this->assertEquals(1, $params['embed']);
            if ($chip['key'] === 'anio') {
                $this->assertArrayNotHasKey('year', $params);
            }
        }
        parse_str(parse_url($response->viewData('clearFiltersUrl'), PHP_URL_QUERY), $clear);
        $this->assertEquals(['embed' => 1, 'view' => 'table', 'per_page' => 10], $clear);
        $this->get('/audios?'.http_build_query(array_merge($query, ['page' => 2])))->assertOk()
            ->assertViewHas('audios', fn ($audios) => $audios->count() === 2);
    }

    public function test_cached_pagination_uses_the_current_embed_route(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->audio();
        }
        $this->get('/audios?per_page=10')->assertOk();
        $response = $this->get('/embed?per_page=10')->assertOk();
        $this->assertEquals('/embed', parse_url($response->viewData('audios')->nextPageUrl(), PHP_URL_PATH));
        $response = $this->get('/audios?per_page=10&embed=1')->assertOk();
        $this->assertEquals('/audios', parse_url($response->viewData('audios')->nextPageUrl(), PHP_URL_PATH));
    }

    public function test_free_text_search_includes_citations_and_invalid_filters_do_not_crash(): void
    {
        $this->audio();
        $this->get('/audios?q=1%3A1-5')->assertOk()->assertViewHas('resultCount', 1);
        $this->get('/audios?'.http_build_query(['q' => ['invalid'], 'autor_id' => '1.5', 'libro_id' => -1,
            'serie_id' => ['invalid']]))->assertOk()
            ->assertViewHas('filters', fn ($filters) => $filters['q'] === null && $filters['autor_id'] === null &&
                $filters['libro_id'] === null && $filters['serie_id'] === null);
    }

    public function test_legacy_date_ranges_are_removed_and_do_not_filter_results(): void
    {
        $this->audio();
        $redirect = $this->get('/embed?fecha_desde=2020-01-01&fecha_hasta=2020-12-31&per_page=10&page=3')->assertRedirect();
        parse_str(parse_url($redirect->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertEquals(['per_page' => 10], $query);
        $this->assertEquals('/embed', parse_url($redirect->headers->get('Location'), PHP_URL_PATH));
        $this->get($redirect->headers->get('Location'))->assertOk()->assertViewHas('resultCount', 1)
            ->assertDontSee('name="fecha_desde"', false)->assertDontSee('name="fecha_hasta"', false);
        $this->get('/audios?q=08%2F11%2F2026')->assertOk()
            ->assertViewHas('filters', fn ($filters) => $filters['q'] === '08/11/2026');
    }

    public function test_series_selector_only_lists_series_with_public_audio(): void
    {
        $this->audio();
        $private = Serie::create(['nombre' => 'Serie privada']);
        $this->audio(['serie_id' => $private->id, 'estado' => 'Pendiente']);
        Serie::create(['nombre' => 'Serie vacía']);
        $this->get('/audios')->assertOk()->assertViewHas('series', fn ($series) => $series->pluck('id')->all() === [$this->series->id])
            ->assertSee('name="serie_id"', false)->assertDontSee('Serie privada')->assertDontSee('Serie vacía');
    }

    public function test_book_and_series_with_same_name_are_not_silently_confused(): void
    {
        $sameName = Serie::create(['nombre' => 'Génesis']);
        $expected = $this->audio(['serie_id' => $sameName->id]);
        $this->audio(['libro_id' => null]);
        $this->get('/audios?q=genesis')->assertOk()
            ->assertViewHas('filters', fn ($filters) => $filters['q'] === 'genesis' && $filters['libro_id'] === null && $filters['serie_id'] === null);
        $this->get('/audios?serie_id='.$sameName->id)->assertOk()
            ->assertViewHas('audios', fn ($audios) => $audios->pluck('id')->all() === [$expected->id])
            ->assertSee('Serie: Génesis');
    }
}
