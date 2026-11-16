<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Libro;
use App\Models\Serie;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicAudioController extends Controller
{
    public function index(Request $request)
    {
        if ($request->hasAny(['fecha_desde', 'fecha_hasta'])) {
            return redirect()->route($request->routeIs('public.embed') ? 'public.embed' : 'public.audios',
                Arr::except($request->query(), ['fecha_desde', 'fecha_hasta', 'page']));
        }

        $previousUrl = url()->previous();
        $homeUrl = route('dashboard');
        $isInvalidBackUrl = ! $previousUrl || $previousUrl === url()->current() || str_contains($previousUrl, '/login');
        $backUrl = $isInvalidBackUrl ? $homeUrl : $previousUrl;

        $isEmbed = $request->boolean('embed', false) || $request->routeIs('public.embed');
        $showPreviewBar = ! $isEmbed && $request->integer('preview', 1) === 1;

        $perPage = (int) $request->input('per_page', 50);
        $perPage = max(5, min(100, $perPage));
        $page = max(1, (int) $request->input('page', 1));

        $categorias = Cache::remember('public.audios.categories', now()->addHour(), fn () => Categoria::orderBy('nombre')->get(['id', 'nombre']));
        $autores = Cache::remember('public.audios.authors', now()->addHour(), fn () => Autor::orderBy('nombre')->get(['id', 'nombre']));
        $libros = Cache::remember('public.audios.books', now()->addHour(), fn () => Libro::orderBy('id')->get(['id', 'nombre', 'abreviatura']));
        $series = Cache::remember('public.audios.series', now()->addHour(), fn () => Serie::query()
            ->whereHas('audios', fn ($query) => $query->whereIn('estado', ['Publicado', 'Publico']))
            ->orderBy('nombre')->get(['id', 'nombre']));
        $years = Cache::remember('public.audios.years.v2', now()->addHour(), function () {
            return Audio::whereIn('estado', ['Publicado', 'Publico'])
                ->whereNotNull('fecha_publicacion')
                ->select('fecha_publicacion')->distinct()->orderByDesc('fecha_publicacion')
                ->pluck('fecha_publicacion')
                ->map(fn ($date) => (int) substr($date, 0, 4))->unique()->values();
        });

        $rawFilters = [
            'q' => $request->query('q', $request->query('search')),
            'categoria_id' => $request->query('categoria_id'),
            'autor_id' => $request->query('autor_id'),
            'libro_id' => $request->query('libro_id'),
            'serie_id' => $request->query('serie_id'),
            'anio' => $request->query('anio', $request->query('year')),
        ];

        $filters = [
            'q' => is_string($rawFilters['q']) ? (trim($rawFilters['q']) ?: null) : null,
            'categoria_id' => $this->sanitizeIntegerFilter($rawFilters['categoria_id']),
            'autor_id' => $this->sanitizeIntegerFilter($rawFilters['autor_id']),
            'libro_id' => $this->sanitizeIntegerFilter($rawFilters['libro_id']),
            'serie_id' => $this->sanitizeIntegerFilter($rawFilters['serie_id']),
            'anio' => $this->sanitizeIntegerFilter($rawFilters['anio']),
        ];

        // Keep typed criteria, selects, chips and URLs in the same explicit state.
        $resolvedFilters = $this->resolveSearchFilter($filters, $autores, $categorias, $libros, $series, $years);
        if ($resolvedFilters !== $filters) {
            $params = Arr::except($request->query(), array_merge(array_keys($filters), ['search', 'year', 'page']));
            $params = array_merge($params, array_filter($resolvedFilters, fn ($value) => $value !== null && $value !== ''));

            return redirect()->route($request->routeIs('public.embed') ? 'public.embed' : 'public.audios', $params);
        }

        $baseQuery = Audio::query()
            ->with(['autor', 'serie', 'categoria', 'libro'])
            ->whereIn('estado', ['Publicado', 'Publico']);

        if ($filters['q']) {
            $search = $filters['q'];
            $baseQuery->where(function ($builder) use ($search) {
                $builder->where('titulo', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%")
                    ->orWhere('cita_biblica', 'like', "%{$search}%")
                    ->orWhereHas('libro', function ($bookQuery) use ($search) {
                        $bookQuery->where('nombre', 'like', "%{$search}%");
                    })
                    ->orWhereHas('autor', function ($authorQuery) use ($search) {
                        $authorQuery->where('nombre', 'like', "%{$search}%");
                    })
                    ->orWhereHas('serie', function ($serieQuery) use ($search) {
                        $serieQuery->where('nombre', 'like', "%{$search}%");
                    })
                    ->orWhereHas('categoria', function ($categoriaQuery) use ($search) {
                        $categoriaQuery->where('nombre', 'like', "%{$search}%");
                    });
            });
        }

        if ($filters['categoria_id']) {
            $baseQuery->where('categoria_id', $filters['categoria_id']);
        }
        if ($filters['autor_id']) {
            $baseQuery->where('autor_id', $filters['autor_id']);
        }
        if ($filters['libro_id']) {
            $baseQuery->where('libro_id', $filters['libro_id']);
        }
        if ($filters['serie_id']) {
            $baseQuery->where('serie_id', $filters['serie_id']);
        }
        if ($filters['anio']) {
            $baseQuery->whereYear('fecha_publicacion', $filters['anio']);
        }
        $cacheKey = 'public.audios.list.v4.'.md5(json_encode([
            'filters' => $filters,
            'page' => $page,
            'per_page' => $perPage,
        ]));

        $audios = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($baseQuery, $perPage, $page) {
            return (clone $baseQuery)
                ->orderByDesc('fecha_publicacion')
                ->orderByDesc('id')
                ->paginate($perPage, ['*'], 'page', $page);
        });
        $audios->withPath($request->url())->appends($request->query());

        $activeFilters = $this->buildActiveFilters(
            $filters,
            $request->query(),
            $categorias,
            $autores,
            $libros,
            $series
        );

        $filterKeys = array_keys($filters);
        $clearFiltersUrl = route('public.audios', Arr::except(
            $request->query(),
            array_merge($filterKeys, ['search', 'year', 'page'])
        ));

        $viewMode = $request->query('view');
        if (! in_array($viewMode, ['table', 'cards'], true)) {
            $viewMode = 'table';
        }

        $dark = $request->boolean('dark', false);
        $allowedWidths = [414, 820, 1280];
        $previewWidth = (int) $request->query('wp_width', 1280);
        if (! in_array($previewWidth, $allowedWidths, true)) {
            $previewWidth = 1280;
        }

        return view('public.audios', [
            'audios' => $audios,
            'filters' => $filters,
            'filterChips' => $activeFilters,
            'clearFiltersUrl' => $clearFiltersUrl,
            'categorias' => $categorias,
            'autores' => $autores,
            'libros' => $libros,
            'series' => $series,
            'years' => $years,
            'viewMode' => $viewMode,
            'dark' => $dark,
            'previewWidth' => $previewWidth,
            'perPage' => $perPage,
            'backUrl' => $backUrl,
            'showPreviewBar' => $showPreviewBar,
            'isEmbed' => $isEmbed,
            'resultCount' => $audios->total(),
        ]);
    }

    public function playAudio(Audio $audio)
    {
        if (! Storage::disk('public')->exists($audio->archivo)) {
            abort(404, 'Archivo no encontrado en el almacenamiento.');
        }

        return Storage::disk('public')->response($audio->archivo);
    }

    public function download(Audio $audio)
    {
        if (Storage::disk('public')->exists($audio->archivo)) {
            return Storage::disk('public')->download($audio->archivo, $audio->titulo.'.mp3');
        }
        abort(404, 'Audio not found.');
    }

    private function sanitizeIntegerFilter($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
    }

    private function resolveSearchFilter(array $filters, Collection $autores, Collection $categorias, Collection $libros, Collection $series, Collection $years): array
    {
        if ($filters['q'] === null) {
            return $filters;
        }

        $search = $filters['q'];
        $matches = [];
        if (preg_match('/^\d{4}$/', $search) && $years->contains((int) $search)) {
            $matches['anio'] = (int) $search;
        } else {
            $normalize = fn ($text) => Str::lower(Str::ascii(Str::squish($text)));
            $needle = $normalize($search);
            $exact = [];
            $partial = [];
            foreach (['autor_id' => $autores, 'categoria_id' => $categorias, 'libro_id' => $libros, 'serie_id' => $series] as $key => $items) {
                foreach ($items as $item) {
                    $name = $normalize($item->nombre);
                    if ($name === $needle || ($key === 'libro_id' && $normalize($item->abreviatura ?? '') === $needle)) {
                        $exact[] = [$key, $item->id];
                    } elseif (mb_strlen($needle) >= 3 && str_contains($name, $needle)) {
                        $partial[] = [$key, $item->id];
                    }
                }
            }
            $candidates = $exact ?: $partial;
            if (count($candidates) === 1) {
                [$key, $id] = $candidates[0];
                $matches[$key] = $id;
            }
        }

        // Never replace an explicitly selected criterion with a different one.
        foreach ($matches as $key => $value) {
            if ($filters[$key] !== null && $filters[$key] !== $value) {
                return $filters;
            }
        }
        if ($matches) {
            $filters = array_merge($filters, $matches, ['q' => null]);
        }

        return $filters;
    }

    private function buildActiveFilters(
        array $filters,
        array $queryParams,
        Collection $categorias,
        Collection $autores,
        Collection $libros,
        Collection $series
    ): array {
        $labels = [
            'q' => 'Buscar',
            'categoria_id' => 'Categoría',
            'autor_id' => 'Autor',
            'libro_id' => 'Libro de la Biblia',
            'serie_id' => 'Serie',
            'anio' => 'Año',
        ];

        $chips = [];

        foreach ($labels as $key => $label) {
            $value = $filters[$key] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $display = $value;
            switch ($key) {
                case 'categoria_id':
                    $display = optional($categorias->firstWhere('id', $value))->nombre;
                    break;
                case 'autor_id':
                    $display = optional($autores->firstWhere('id', $value))->nombre;
                    break;
                case 'libro_id':
                    $display = optional($libros->firstWhere('id', $value))->nombre;
                    break;
                case 'serie_id':
                    $display = optional($series->firstWhere('id', $value))->nombre;
                    break;
                case 'anio':
                    $display = (string) $value;
                    break;
                case 'q':
                default:
                    $display = (string) $value;
                    break;
            }

            if (! $display) {
                continue;
            }

            $keysToDrop = [$key, 'page'];
            if ($key === 'q') {
                $keysToDrop[] = 'search';
            }
            if ($key === 'anio') {
                $keysToDrop[] = 'year';
            }

            $without = Arr::except($queryParams, $keysToDrop);
            $chips[] = [
                'key' => $key,
                'label' => $label,
                'value' => $display,
                'remove_url' => route('public.audios', $without),
            ];
        }

        return $chips;
    }
}
