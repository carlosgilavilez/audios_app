<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use App\Models\Autor;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PublicAudioController extends Controller
{
    public function index(Request $request)
    {
        $previousUrl = url()->previous();
        $homeUrl = route('dashboard');
        $isInvalidBackUrl = !$previousUrl || $previousUrl === url()->current() || str_contains($previousUrl, '/login');
        $backUrl = $isInvalidBackUrl ? $homeUrl : $previousUrl;

        $isEmbed = $request->boolean('embed', false);
        $showPreviewBar = !$isEmbed && $request->integer('preview', 1) === 1;

        $perPage = (int) $request->input('per_page', 25);
        $perPage = max(5, min(100, $perPage));
        $page = max(1, (int) $request->input('page', 1));

        $rawFilters = [
            'q' => $request->query('q', $request->query('search')),
            'categoria_id' => $request->query('categoria_id'),
            'autor_id' => $request->query('autor_id'),
            'anio' => $request->query('anio', $request->query('year')),
        ];

        $filters = [
            'q' => $rawFilters['q'] ? trim((string) $rawFilters['q']) : null,
            'categoria_id' => $this->sanitizeIntegerFilter($rawFilters['categoria_id']),
            'autor_id' => $this->sanitizeIntegerFilter($rawFilters['autor_id']),
            'anio' => $this->sanitizeIntegerFilter($rawFilters['anio']),
        ];

        $baseQuery = Audio::query()
            ->with(['autor', 'serie', 'categoria', 'libro'])
            ->whereIn('estado', ['Publicado', 'Publico']);

        if ($filters['q']) {
            $search = $filters['q'];
            $baseQuery->where(function ($builder) use ($search) {
                $builder->where('titulo', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%")
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
        if ($filters['anio']) {
            $baseQuery->whereYear('fecha_publicacion', $filters['anio']);
        }

        $cacheKey = 'public.audios.list.v2.' . md5(json_encode([
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
        $audios->appends($request->query());

        $categorias = Cache::remember('public.audios.categories', now()->addHour(), function () {
            return Categoria::orderBy('nombre')->get(['id', 'nombre']);
        });
        $autores = Cache::remember('public.audios.authors', now()->addHour(), function () {
            return Autor::orderBy('nombre')->get(['id', 'nombre']);
        });
        $years = Cache::remember('public.audios.years', now()->addHour(), function () {
            return Audio::whereIn('estado', ['Publicado', 'Publico'])
                ->whereNotNull('fecha_publicacion')
                ->select(DB::raw('YEAR(fecha_publicacion) as year'))
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year')
                ->filter()
                ->values();
        });

        $activeFilters = $this->buildActiveFilters(
            $filters,
            $request->query(),
            $categorias,
            $autores,
            $years
        );

        $filterKeys = array_keys($filters);
        $clearFiltersUrl = route('public.audios', Arr::except(
            $request->query(),
            array_merge($filterKeys, ['search', 'year', 'page'])
        ));

        $viewMode = $request->query('view');
        if (!in_array($viewMode, ['table', 'cards'], true)) {
            $viewMode = 'table';
        }

        $dark = $request->boolean('dark', false);
        $allowedWidths = [414, 820, 1280];
        $previewWidth = (int) $request->query('wp_width', 1280);
        if (!in_array($previewWidth, $allowedWidths, true)) {
            $previewWidth = 1280;
        }

        return view('public.audios', [
            'audios' => $audios,
            'filters' => $filters,
            'filterChips' => $activeFilters,
            'clearFiltersUrl' => $clearFiltersUrl,
            'categorias' => $categorias,
            'autores' => $autores,
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
        if (!Storage::disk('public')->exists($audio->archivo)) {
            abort(404, 'Archivo no encontrado en el almacenamiento.');
        }

        return Storage::disk('public')->response($audio->archivo);
    }

    public function download(Audio $audio)
    {
        if (Storage::disk('public')->exists($audio->archivo)) {
            return Storage::disk('public')->download($audio->archivo, $audio->titulo . '.mp3');
        }
        abort(404, 'Audio not found.');
    }

    private function sanitizeIntegerFilter($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function buildActiveFilters(
        array $filters,
        array $queryParams,
        Collection $categorias,
        Collection $autores
    ): array {
        $labels = [
            'q' => 'Buscar',
            'categoria_id' => 'Categoría',
            'autor_id' => 'Autor',
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
                case 'anio':
                    $display = (string) $value;
                    break;
                case 'q':
                default:
                    $display = (string) $value;
                    break;
            }

            if (!$display) {
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















