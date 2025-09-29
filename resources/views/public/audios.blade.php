@extends('layouts.preview')

@section('title', 'Biblioteca de Audios')

@push('body-attrs', 'data-embed-mode="'.($isEmbed ? '1' : '0').'"')
@if($showPreviewBar)
    @push('body-attrs', 'data-wp-preview="1"')
    @push('body-attrs', 'data-wp-width="'.$previewWidth.'"')
@endif

@php
    $queryFor = function (array $overrides = [], array $removals = []) {
        $params = array_merge(request()->query(), $overrides);
        foreach (array_merge(['page'], $removals) as $key) {
            unset($params[$key]);
        }
        return array_filter($params, fn ($value) => $value !== null && $value !== '');
    };
@endphp

@section('content')
    <div class="space-y-6" data-public-audios data-initial-view="{{ $viewMode }}" data-initial-dark="{{ $dark ? '1' : '0' }}" data-preview-width="{{ $previewWidth }}" data-embed="{{ $isEmbed ? '1' : '0' }}">
        @if($showPreviewBar)
            <div data-preview-bar class="rounded-md border border-border bg-card/90 backdrop-blur px-4 py-3 flex flex-wrap items-center justify-end gap-3 text-sm text-muted-foreground">
                <a href="{{ $backUrl }}" target="_top" class="inline-flex items-center rounded-md bg-destructive text-destructive-foreground px-3 py-1.5 text-xs font-medium transition hover:bg-destructive/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-destructive">
                    Salir
                </a>
            </div>
        @endif

        <div class="relative" data-layout-shell>
            <div class="lg:hidden" data-mobile-drawer>
                <div class="pointer-events-none fixed inset-0 z-40 hidden" data-filters-dialog role="dialog" aria-modal="true" aria-labelledby="mobileFiltersTitle">
                    <div class="pointer-events-auto absolute inset-0 bg-black/50" data-filters-scrim></div>
                    <div class="pointer-events-auto absolute inset-y-0 right-0 flex w-full max-w-md translate-x-full flex-col bg-background shadow-2xl transition-transform" data-filters-panel>
                        <div class="flex items-center justify-between border-b border-border px-5 py-4">
                            <div class="space-y-1">
                                <h2 id="mobileFiltersTitle" class="text-lg font-semibold text-foreground">Filtros</h2>
                                <p class="text-xs text-muted-foreground">Selecciona criterios y aplica la búsqueda.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-theme-toggle />
                                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border text-muted-foreground transition hover:bg-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" data-filters-close aria-label="Cerrar filtros">
                                    <i data-lucide="x" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                        <div class="flex-1 overflow-y-auto px-5 py-4">
                            @include('public.partials.filter-form', [
                                'filters' => $filters,
                                'categorias' => $categorias,
                                'autores' => $autores,
                                'years' => $years,
                                'viewMode' => $viewMode,
                                'isEmbed' => $isEmbed,
                                'showPreviewBar' => $showPreviewBar,
                                'clearFiltersUrl' => $clearFiltersUrl,
                                'formId' => 'mobile-filters-form',
                                'hasActiveFilters' => !empty($filterChips),
                            ])
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6 lg:grid lg:grid-cols-[minmax(260px,320px)_1fr] lg:items-start lg:gap-6 xl:grid-cols-[minmax(280px,320px)_1fr]">
                <aside class="hidden lg:block" data-sidebar role="complementary" aria-labelledby="desktopFiltersTitle">
                    <div class="sticky top-4 space-y-4">
                        <div class="rounded-2xl border border-border bg-card/90 p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h2 id="desktopFiltersTitle" class="text-base font-semibold text-foreground">Filtros</h2>
                                    <p class="text-xs text-muted-foreground">Combina criterios y aplica la búsqueda.</p>
                                </div>
                                <x-theme-toggle />
                            </div>
                            <div class="mt-4">
                                @include('public.partials.filter-form', [
                                    'filters' => $filters,
                                    'categorias' => $categorias,
                                    'autores' => $autores,
                                    'years' => $years,
                                    'viewMode' => $viewMode,
                                    'isEmbed' => $isEmbed,
                                    'showPreviewBar' => $showPreviewBar,
                                    'clearFiltersUrl' => $clearFiltersUrl,
                                    'formId' => 'desktop-filters-form',
                                    'hasActiveFilters' => !empty($filterChips),
                                ])
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="flex flex-col gap-5" data-results>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="inline-flex items-center gap-2 rounded-full border border-border bg-card/80 p-1" role="group" aria-label="Cambiar vista">
                            <button type="button" class="view-toggle inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-xs font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" data-view-option="table" aria-pressed="{{ $viewMode === 'table' ? 'true' : 'false' }}">
                                <i data-lucide="list" class="h-4 w-4"></i>
                                <span class="hidden sm:inline">Tabla</span>
                            </button>
                            <button type="button" class="view-toggle inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-xs font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" data-view-option="cards" aria-pressed="{{ $viewMode === 'cards' ? 'true' : 'false' }}">
                                <i data-lucide="grid" class="h-4 w-4"></i>
                                <span class="hidden sm:inline">Tarjetas</span>
                            </button>
                        </div>
                        <div class="flex items-center gap-3">
                            <p class="text-sm text-muted-foreground" data-results-count aria-live="polite">
                                {{ trans_choice(':count audio encontrado|:count audios encontrados', $resultCount, ['count' => $resultCount]) }}
                            </p>
                            <button type="button" class="inline-flex items-center gap-2 rounded-full border border-border px-3 py-1.5 text-sm font-medium text-foreground transition hover:bg-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary lg:hidden" data-filters-toggle aria-haspopup="dialog" aria-expanded="false" aria-controls="mobile-filters-form">
                                <i data-lucide="sliders-horizontal" class="h-4 w-4"></i>
                                <span>Filtros</span>
                            </button>
                        </div>
                    </div>

                    @if(!empty($filterChips))
                        <div class="flex flex-wrap items-center gap-2" data-filter-chips>
                            @foreach($filterChips as $chip)
                                <a href="{{ $chip['remove_url'] }}"
                                   class="group inline-flex items-center gap-1.5 rounded-full border border-border bg-muted/40 px-3 py-1 text-xs font-medium text-foreground transition hover:bg-destructive/10 hover:text-destructive-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                                   aria-label="Quitar {{ strtolower($chip['label']) }} {{ $chip['value'] }}">
                                    <span>{{ $chip['label'] }}: {{ $chip['value'] }}</span>
                                    <span aria-hidden="true" class="text-muted-foreground transition group-hover:text-destructive">×</span>
                                </a>
                            @endforeach
                            <a href="{{ $clearFiltersUrl }}" class="inline-flex items-center gap-1.5 rounded-full border border-border bg-background px-3 py-1 text-xs font-semibold text-muted-foreground transition hover:bg-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                                Limpiar todo
                            </a>
                        </div>
                    @endif

                    <div class="space-y-5" data-view-container>
                        <div data-view-panel="table" @class(['block' => $viewMode === 'table', 'hidden' => $viewMode !== 'table'])>
                            <div class="overflow-x-auto rounded-xl border border-border bg-card shadow-sm">
                                <table class="wp-track-table w-full min-w-[720px] divide-y divide-border text-sm">
                                    <thead class="bg-muted/60 text-muted-foreground tracking-wide text-xs uppercase">
                                        <tr>
                                            <th scope="col" class="px-3 py-3 text-left font-semibold"></th>
                                            <th scope="col" class="px-3 py-3 text-left font-semibold">Título</th>
                                            <th scope="col" class="px-3 py-3 text-left font-semibold">Autor</th>
                                            <th scope="col" class="px-3 py-3 text-left font-semibold table-col--category">Categoría</th>
                                            <th scope="col" class="px-3 py-3 text-left font-semibold table-col--serie">Serie</th>
                                            <th scope="col" class="px-3 py-3 text-left font-semibold table-col--date">Fecha</th>
                                            <th scope="col" class="px-3 py-3 text-left font-semibold table-col--cita"></th>
                                            <th scope="col" class="px-3 py-3 text-left font-semibold table-col--duration"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border bg-card" data-view-active="{{ $viewMode === 'table' ? 'true' : 'false' }}">
                                        @forelse ($audios as $audio)
                                            @php
                                                $publishDate = $audio->fecha_publicacion ? \Illuminate\Support\Carbon::parse($audio->fecha_publicacion)->locale('es') : null;
                                                $formattedDate = '';
                                                $dateParts = [];
                                                $yearValue = null;
                                                $yearLink = null;
                                                if ($publishDate) {
                                                    $formattedDate = \Illuminate\Support\Str::lower(str_replace('.', '', $publishDate->isoFormat('D MMM YYYY')));
                                                    $dateParts = array_values(array_filter(explode(' ', $formattedDate)));
                                                    $yearValue = (int) $publishDate->year;
                                                    $yearLink = route('public.audios', $queryFor(['anio' => $yearValue]));
                                                }
                                                $yearToken = null;
                                                if (!empty($dateParts)) {
                                                    $yearToken = array_pop($dateParts);
                                                }
                                                $dateWithoutYear = trim(implode(' ', $dateParts));
                                                $categoryName = $audio->categoria->nombre ?? '';
                                                $seriesName = $audio->serie->nombre ?? '';
                                                $cita = trim(($audio->libro->nombre ?? '') . ' ' . ($audio->cita_biblica ?? ''));
                                            @endphp
                                            <tr class="transition hover:bg-muted/60">
                                                <td class="px-3 py-3 whitespace-nowrap align-top" data-label="Reproducir">
                                                    <x-player.play-button
                                                        :src="route('public.audios.play', $audio)"
                                                        :title="$audio->titulo ?? ''"
                                                        :author="$audio->autor->nombre ?? ''"
                                                        :download="route('public.download_audio', $audio)"
                                                        :index="$loop->index"
                                                        :category="$categoryName"
                                                        :series="$seriesName"
                                                        :date="$formattedDate"
                                                        :year="$yearValue"
                                                        :year-link="$yearLink"
                                                        :citation="$cita"
                                                    />
                                                </td>
                                                <td class="px-3 py-3 align-top text-foreground font-medium" data-label="Título">{{ $audio->titulo ?? '' }}</td>
                                                <td class="px-3 py-3 align-top whitespace-nowrap" data-label="Autor">
                                                    @php $authorLink = $audio->autor_id ? route('public.audios', $queryFor(['autor_id' => $audio->autor_id])) : null; @endphp
                                                    @if($authorLink)
                                                        <a href="{{ $authorLink }}" class="link-chip" data-filter-link="autor">{{ $audio->autor->nombre ?? '' }}</a>
                                                    @else
                                                        {{ $audio->autor->nombre ?? '' }}
                                                    @endif
                                                </td>
                                                <td class="px-3 py-3 align-top whitespace-nowrap table-col--category" data-label="Categoría">
                                                    @php $categoryLink = $audio->categoria_id ? route('public.audios', $queryFor(['categoria_id' => $audio->categoria_id])) : null; @endphp
                                                    @if($categoryLink)
                                                        <a href="{{ $categoryLink }}" class="link-chip" data-filter-link="categoria">{{ $categoryName }}</a>
                                                    @else
                                                        {{ $categoryName }}
                                                    @endif
                                                </td>
                                                <td class="px-3 py-3 align-top whitespace-nowrap table-col--serie" data-label="Serie">{{ $seriesName }}</td>
                                                <td class="px-3 py-3 align-top whitespace-nowrap table-col--date" data-label="Fecha">
                                                    @if($formattedDate)
                                                        @if($dateWithoutYear)
                                                            <span class="date-prefix">{{ $dateWithoutYear }}</span>
                                                        @endif
                                                        @if($yearToken)
                                                            @if($yearLink)
                                                                <a href="{{ $yearLink }}" class="date-year-link" data-filter-link="anio">{{ $yearToken }}</a>
                                                            @else
                                                                <span class="date-year-link">{{ $yearToken }}</span>
                                                            @endif
                                                        @endif
                                                    @endif
                                                </td>
                                                <td class="px-3 py-3 align-top whitespace-nowrap table-col--cita" data-label="Cita bíblica">{{ $cita }}</td>
                                                <td class="px-3 py-3 align-top whitespace-nowrap table-col--duration" data-label="">{{ $audio->duracion ?? '' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="px-3 py-12 text-center text-muted-foreground">No hay audios que coincidan con la búsqueda.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div data-view-panel="cards" @class(['block' => $viewMode === 'cards', 'hidden' => $viewMode !== 'cards'])>
                            <div class="grid gap-4 sm:grid-cols-2" data-view-active="{{ $viewMode === 'cards' ? 'true' : 'false' }}">
                                @forelse ($audios as $audio)
                                    @php
                                        $publishDate = $audio->fecha_publicacion ? \Illuminate\Support\Carbon::parse($audio->fecha_publicacion)->locale('es') : null;
                                        $formattedDate = '';
                                        $dateParts = [];
                                        $yearValue = null;
                                        $yearLink = null;
                                        if ($publishDate) {
                                            $formattedDate = \Illuminate\Support\Str::lower(str_replace('.', '', $publishDate->isoFormat('D MMM YYYY')));
                                            $dateParts = array_values(array_filter(explode(' ', $formattedDate)));
                                            $yearValue = (int) $publishDate->year;
                                            $yearLink = route('public.audios', $queryFor(['anio' => $yearValue]));
                                        }
                                        $yearToken = null;
                                        if (!empty($dateParts)) {
                                            $yearToken = array_pop($dateParts);
                                        }
                                        $dateWithoutYear = trim(implode(' ', $dateParts));
                                        $categoryName = $audio->categoria->nombre ?? '';
                                        $seriesName = $audio->serie->nombre ?? '';
                                        $cita = trim(($audio->libro->nombre ?? '') . ' ' . ($audio->cita_biblica ?? ''));
                                    @endphp
                                    <article class="rounded-2xl border border-border bg-card p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                                        <div class="flex items-start gap-3">
                                            <x-player.play-button
                                                :src="route('public.audios.play', $audio)"
                                                :title="$audio->titulo ?? ''"
                                                :author="$audio->autor->nombre ?? ''"
                                                :download="route('public.download_audio', $audio)"
                                                :index="$loop->index"
                                                :category="$categoryName"
                                                :series="$seriesName"
                                                :date="$formattedDate"
                                                :year="$yearValue"
                                                :year-link="$yearLink"
                                                :citation="$cita"
                                            />
                                            <div class="min-w-0 space-y-1">
                                                <div class="player-card-title marquee-container" aria-live="polite">
                                                    <span class="marquee" data-marquee="{{ $audio->titulo ?? '' }}">{{ $audio->titulo ?? '' }}</span>
                                                </div>
                                                <p class="text-sm text-muted-foreground">
                                                    @php $authorLink = $audio->autor_id ? route('public.audios', $queryFor(['autor_id' => $audio->autor_id])) : null; @endphp
                                                    @if($authorLink)
                                                        <a href="{{ $authorLink }}" class="link-chip" data-filter-link="autor">{{ $audio->autor->nombre ?? 'Autor desconocido' }}</a>
                                                    @else
                                                        {{ $audio->autor->nombre ?? 'Autor desconocido' }}
                                                    @endif
                                                </p>
                                                @if($categoryName)
                                                    <x-ui.cat-badge :name="$categoryName" />
                                                @endif
                                            </div>
                                        </div>
                                        <dl class="mt-3 grid grid-cols-1 gap-2 text-sm text-muted-foreground">
                                            @if($seriesName)
                                                <div class="flex justify-between gap-2">
                                                    <dt class="font-medium text-foreground">Serie</dt>
                                                    <dd class="text-right">{{ $seriesName }}</dd>
                                                </div>
                                            @endif
                                            @if($formattedDate)
                                                <div class="flex justify-between gap-2">
                                                    <dt class="font-medium text-foreground">Fecha</dt>
                                                    <dd class="text-right">
                                                        @if($dateWithoutYear)
                                                            <span>{{ $dateWithoutYear }}</span>
                                                        @endif
                                                        @if($yearToken)
                                                            @if($yearLink)
                                                                <a href="{{ $yearLink }}" class="date-year-link" data-filter-link="anio">{{ $yearToken }}</a>
                                                            @else
                                                                <span class="date-year-link">{{ $yearToken }}</span>
                                                            @endif
                                                        @endif
                                                    </dd>
                                                </div>
                                            @endif
                                            @if($cita)
                                                <div class="flex justify-between gap-2">
                                                    <dt class="font-medium text-foreground">Cita bíblica</dt>
                                                    <dd class="text-right">{{ $cita }}</dd>
                                                </div>
                                            @endif
                                        </dl>
                                        <div class="mt-4 flex items-center justify-between text-sm text-muted-foreground">
                                            <span><span class="sr-only">Duración:</span> <span class="text-foreground">{{ $audio->duracion ?? '' }}</span></span>
                                            <a href="{{ route('public.download_audio', $audio) }}" class="inline-flex items-center gap-1 rounded-md border border-border bg-background px-3 py-1 text-xs font-medium text-foreground transition hover:bg-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                                                <i data-lucide="download" class="h-4 w-4"></i>
                                                Descargar
                                            </a>
                                        </div>
                                    </article>
                                @empty
                                    <div class="col-span-full rounded-xl border border-dashed border-border bg-muted/40 p-8 text-center text-muted-foreground">
                                        No hay audios que coincidan con la búsqueda.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    @if ($audios->hasPages())
                        <div class="border-t border-border bg-card/70 px-4 py-3 sm:px-6">
                            {{ $audios->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection