<div @class([
    'space-y-6',
    'pt-10 lg:pt-12' => $isEmbed,
])
     data-public-audios
     data-initial-view="{{ $viewMode }}"
     data-initial-dark="{{ $dark ? '1' : '0' }}"
     data-preview-width="{{ $previewWidth }}"
     data-embed="{{ $isEmbed ? '1' : '0' }}">
    @if ($showPreviewBar)
        <div data-preview-bar
             class="rounded-md border border-border bg-card/90 backdrop-blur px-4 py-3 flex flex-wrap items-center justify-end gap-3 text-sm text-muted-foreground">
            <a href="{{ $backUrl }}"
               target="_top"
               class="inline-flex items-center rounded-md bg-destructive text-destructive-foreground px-3 py-1.5 text-xs font-medium transition hover:bg-destructive/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-destructive">
                Salir
            </a>
        </div>
    @endif

    <div class="relative" data-layout-shell>
        <div class="lg:hidden" data-mobile-drawer>
            <div class="pointer-events-none fixed inset-0 z-[51] hidden"
                 id="public-audio-filters"
                 data-filters-dialog
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="mobileFiltersTitle">
                <div class="pointer-events-auto absolute inset-0 bg-black/50" data-filters-scrim></div>
                <div class="pointer-events-auto absolute inset-y-0 right-0 flex w-full max-w-md translate-x-full flex-col bg-card/95 dark:bg-background/90 backdrop-blur-md border-l border-border/40 shadow-2xl transition-transform"
                     data-filters-panel>
                    <div class="flex shrink-0 items-center justify-between border-b border-border px-5 py-4">
                        <div class="space-y-1">
                            <h2 id="mobileFiltersTitle" class="text-lg font-semibold text-foreground">Filtros</h2>
                            <p class="text-xs text-muted-foreground">Encuentra el mensaje que buscas.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-theme-toggle />
                            <button type="button"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border text-muted-foreground transition hover:bg-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                                    data-filters-close
                                    aria-label="Cerrar filtros">
                                <i data-lucide="x" class="h-4 w-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-4" data-filters-scroll>
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

        <div class="flex flex-col gap-6 lg:grid lg:grid-cols-[minmax(220px,280px)_minmax(0,1fr)] lg:gap-6 xl:grid-cols-[minmax(240px,300px)_minmax(0,1fr)]">
            <aside class="hidden lg:block" data-sidebar role="complementary" aria-labelledby="desktopFiltersTitle">
                <div class="sticky top-4 max-h-[calc(100dvh-2rem)] space-y-4 overflow-y-auto">
                    <div class="rounded-2xl border border-border bg-card/90 p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 id="desktopFiltersTitle" class="text-base font-semibold text-foreground">Filtros</h2>
                                <p class="text-xs text-muted-foreground">Encuentra el mensaje que buscas.</p>
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

            <div class="flex min-w-0 flex-col gap-5" data-results>
                @php
                    $resultLabel = trans_choice(':count audio encontrado|:count audios encontrados', $resultCount, ['count' => $resultCount]);
                @endphp
                <div class="sticky top-0 z-30 border-b border-border/50 bg-card/90 px-3 py-2 backdrop-blur-md shadow-sm mobile-results-toolbar lg:static lg:border-0 lg:bg-transparent lg:px-0 lg:py-0 lg:shadow-none" data-mobile-toolbar>
                    <div class="flex flex-col gap-1 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center justify-between gap-2">
                            @include('public.partials.per-page-selector', ['perPage' => $perPage])
                            <button type="button"
                                    class="inline-flex items-center gap-2 rounded-full bg-primary px-3 py-1.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary lg:hidden"
                                    data-filters-toggle
                                    aria-label="Filtros"
                                    aria-haspopup="dialog"
                                    aria-expanded="false"
                                    aria-controls="public-audio-filters">
                                <i data-lucide="sliders-horizontal" class="h-4 w-4"></i>
                                <span>Filtros</span>
                            </button>
                        </div>
                        <span class="hidden text-sm font-medium text-foreground lg:inline">{{ $resultLabel }}</span>
                    </div>
                    <span class="mobile-results-count text-[11px] uppercase tracking-wide text-muted-foreground lg:hidden">{{ $resultLabel }}</span>
                </div>

                @if (!empty($filterChips))
                    <div class="flex flex-wrap items-center gap-2" data-filter-chips>
                        @foreach ($filterChips as $chip)
                            <a href="{{ $chip['remove_url'] }}"
                               class="group inline-flex items-center gap-1.5 rounded-full border border-border bg-muted/40 px-3 py-1 text-xs font-medium text-foreground transition hover:bg-destructive/10 hover:text-destructive-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                               aria-label="Quitar {{ strtolower($chip['label']) }} {{ $chip['value'] }}">
                                <span>{{ $chip['label'] }}: {{ $chip['value'] }}</span>
                                <span aria-hidden="true" class="text-muted-foreground transition group-hover:text-destructive">&times;</span>
                            </a>
                        @endforeach
                        <a href="{{ $clearFiltersUrl }}"
                           class="filter-clear filter-clear-all">
                            Limpiar todo
                        </a>
                    </div>
                @endif

                @if ($audios->isEmpty())
                    <div class="rounded-xl border border-border bg-muted/40 px-4 py-10 text-center text-sm text-muted-foreground">
                        No hay audios que coincidan con la busqueda.
                    </div>
                @else
                    <div class="md:hidden space-y-3">
                        @foreach ($audios as $audio)
                            @php
                                $publishDate = $audio->fecha_publicacion
                                    ? \Illuminate\Support\Carbon::parse($audio->fecha_publicacion)->locale('es')
                                    : null;
                                $formattedDate = '';
                                $yearValue = null;
                                $yearLink = null;
                                if ($publishDate) {
                                    $formattedDate = \Illuminate\Support\Str::lower(str_replace('.', '', $publishDate->isoFormat('D MMM YYYY')));
                                    $yearValue = (int) $publishDate->year;
                                    $yearLink = route('public.audios', $queryFor(['anio' => $yearValue]));
                                }
                                $categoryName = $audio->categoria->nombre ?? '';
                                $seriesName = $audio->serie->nombre ?? '';
                                $cita = trim(($audio->libro->nombre ?? '') . ' ' . ($audio->cita_biblica ?? ''));
                            @endphp
                            <article class="flex items-start gap-3 rounded-2xl border border-border bg-card/90 px-4 py-3 shadow-sm">
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
                                <div class="mobile-track-content min-w-0 flex-1">
                                    <div class="mobile-track-title text-sm font-semibold text-foreground break-words">{{ $audio->titulo ?? '' }}</div>
                                    <div class="mobile-track-author text-xs text-muted-foreground">
                                        @if ($audio->autor_id)
                                            <a href="{{ route('public.audios', $queryFor(['autor_id' => $audio->autor_id])) }}" class="link-chip" data-filter-link="autor">{{ $audio->autor->nombre ?? '' }}</a>
                                        @endif
                                    </div>
                                    @if ($cita)
                                        <div class="mobile-track-citation text-xs text-muted-foreground">
                                            @if ($audio->libro_id)
                                                <a href="{{ route('public.audios', $queryFor(['libro_id' => $audio->libro_id])) }}" class="link-chip" data-filter-link="libro">{{ $cita }}</a>
                                            @else
                                                {{ $cita }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="hidden md:block">
                        <div class="overflow-x-auto rounded-xl border border-border bg-card shadow-sm custom-hscroll">
                            <table class="wp-track-table w-full table-fixed divide-y divide-border text-sm">
                                <thead class="bg-muted/60 text-muted-foreground tracking-wide text-xs uppercase">
                                    <tr>
                                        <th scope="col" class="w-12 px-4 py-4 text-left font-semibold"></th>
                                        <th scope="col" class="px-4 py-4 text-left font-semibold">T&iacute;tulo</th>
                                        <th scope="col" class="px-4 py-4 text-left font-semibold table-col--date w-32">Fecha</th>
                                        <th scope="col" class="hidden lg:table-cell px-4 py-5 text-left font-semibold table-col--serie">Serie</th>
                                        <th scope="col" class="hidden xl:table-cell px-4 py-5 text-left font-semibold table-col--cita"></th>
                                        <th scope="col" class="hidden md:table-cell w-24 px-4 py-5 text-left font-semibold table-col--duration"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border bg-card">
                                    @foreach ($audios as $audio)
                                        @php
                                            $publishDate = $audio->fecha_publicacion
                                                ? \Illuminate\Support\Carbon::parse($audio->fecha_publicacion)->locale('es')
                                                : null;
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
                                            <td class="px-4 py-5 whitespace-nowrap align-top">
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
                                            <td class="px-4 py-4 align-top break-words">
                                                <div class="text-foreground font-medium break-words">{{ $audio->titulo ?? '' }}</div>
                                                <div class="text-muted-foreground text-xs break-words mt-1">
                                                    @php
                                                        $authorLink = $audio->autor_id
                                                            ? route('public.audios', $queryFor(['autor_id' => $audio->autor_id]))
                                                            : null;
                                                    @endphp
                                                    @if ($authorLink)
                                                        <a href="{{ $authorLink }}" class="link-chip" data-filter-link="autor">{{ $audio->autor->nombre ?? '' }}</a>
                                                    @else
                                                        {{ $audio->autor->nombre ?? '' }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 align-top break-words table-col--date">
                                                @if ($formattedDate)
                                                    <div class="flex flex-wrap items-baseline gap-1">
                                                        @if ($dateWithoutYear)
                                                            <span class="date-prefix">{{ $dateWithoutYear }}</span>
                                                        @endif
                                                        @if ($yearToken)
                                                            @if ($yearLink)
                                                                <a href="{{ $yearLink }}" class="date-year-link font-semibold" data-filter-link="anio">{{ $yearToken }}</a>
                                                            @else
                                                                <span class="date-year-link font-semibold">{{ $yearToken }}</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @endif
                                                @if ($categoryName)
                                                    <div class="mt-1 text-[11px] text-muted-foreground">
                                                        {{ $categoryName }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="hidden lg:table-cell px-4 py-5 align-top break-words table-col--serie">
                                                {{ $seriesName }}
                                            </td>
                                            <td class="hidden xl:table-cell px-4 py-5 align-top break-words table-col--cita">
                                                @if ($audio->libro_id)
                                                    <a href="{{ route('public.audios', $queryFor(['libro_id' => $audio->libro_id])) }}" class="link-chip" data-filter-link="libro">{{ $cita }}</a>
                                                @else
                                                    {{ $cita }}
                                                @endif
                                            </td>
                                            <td class="hidden md:table-cell px-4 py-5 align-top table-col--duration break-words">
                                                {{ $audio->duracion ?? '' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if ($audios->hasPages())
                    <div class="border-t border-border bg-card/70 px-4 py-3 sm:px-6">
                        {{ $audios->onEachSide(1)->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>




