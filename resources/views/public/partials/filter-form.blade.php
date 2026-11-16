@php
    $resolvedFilters = array_merge([
        'q' => null,
        'categoria_id' => null,
        'autor_id' => null,
        'libro_id' => null,
        'serie_id' => null,
        'anio' => null,
    ], $filters ?? []);
    $formId = $formId ?? 'filters-form';
    $hasActiveFilters = $hasActiveFilters ?? false;
@endphp

<form id="{{ $formId }}" action="{{ route('public.audios') }}" method="GET" class="audio-filter-form grid gap-4 text-sm" data-filters-form>
    <input type="hidden" name="view" value="{{ request('view', $viewMode ?? 'table') }}">
    <input type="hidden" name="per_page" value="{{ $perPage ?? 50 }}">
    @if(($isEmbed ?? false) || request()->has('embed'))
        <input type="hidden" name="embed" value="{{ request('embed', ($isEmbed ?? false) ? '1' : '0') }}">
    @endif
    @if(!($showPreviewBar ?? true))
        <input type="hidden" name="preview" value="0">
    @elseif(request()->has('preview'))
        <input type="hidden" name="preview" value="{{ request('preview') }}">
    @endif
    @if(request()->has('dark'))
        <input type="hidden" name="dark" value="{{ request('dark') }}">
    @endif
    @if(request()->has('wp_width'))
        <input type="hidden" name="wp_width" value="{{ request('wp_width') }}">
    @endif

    <div class="space-y-2">
        <label for="{{ $formId }}-q" class="text-xs font-semibold tracking-wide text-muted-foreground">Buscar</label>
        <div class="filter-search-wrap">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/></svg>
            <input type="search" name="q" id="{{ $formId }}-q" value="{{ $resolvedFilters['q'] }}" placeholder="Título, autor o cita" class="w-full min-w-0 rounded-full border border-border bg-input px-4 py-2 text-base sm:text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
        </div>
    </div>

    @if($series->isNotEmpty())
    <div class="space-y-2">
        <label for="{{ $formId }}-serie" class="text-xs font-semibold tracking-wide text-muted-foreground">Serie</label>
        <select name="serie_id" id="{{ $formId }}-serie" class="w-full rounded-full border border-border bg-input px-4 py-2 text-base sm:text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            <option value="">Todas</option>
            @foreach($series as $serie)
                <option value="{{ $serie->id }}" @selected($resolvedFilters['serie_id'] == $serie->id)>{{ $serie->nombre }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div class="space-y-2">
        <label for="{{ $formId }}-categoria" class="text-xs font-semibold tracking-wide text-muted-foreground">Categoría</label>
        <select name="categoria_id" id="{{ $formId }}-categoria" class="w-full rounded-full border border-border bg-input px-4 py-2 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            <option value="">Todas</option>
            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected($resolvedFilters['categoria_id'] == $categoria->id)>{{ $categoria->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="space-y-2">
        <label for="{{ $formId }}-autor" class="text-xs font-semibold tracking-wide text-muted-foreground">Autor</label>
        <select name="autor_id" id="{{ $formId }}-autor" class="w-full rounded-full border border-border bg-input px-4 py-2 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            <option value="">Todos</option>
            @foreach($autores as $autor)
                <option value="{{ $autor->id }}" @selected($resolvedFilters['autor_id'] == $autor->id)>{{ $autor->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="space-y-2">
        <label for="{{ $formId }}-anio" class="text-xs font-semibold tracking-wide text-muted-foreground">Año</label>
        <select name="anio" id="{{ $formId }}-anio" class="w-full rounded-full border border-border bg-input px-4 py-2 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            <option value="">Todos</option>
            @foreach($years as $year)
                <option value="{{ $year }}" @selected($resolvedFilters['anio'] == $year)>{{ $year }}</option>
            @endforeach
        </select>
    </div>

    <div class="space-y-2">
        <label for="{{ $formId }}-libro" class="text-xs font-semibold tracking-wide text-muted-foreground">Libro de la Biblia</label>
        <select name="libro_id" id="{{ $formId }}-libro" class="w-full rounded-full border border-border bg-input px-4 py-2 text-base sm:text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            <option value="">Todos</option>
            @foreach($libros as $libro)
                <option value="{{ $libro->id }}" @selected($resolvedFilters['libro_id'] == $libro->id)>{{ $libro->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="filter-actions flex items-center justify-between gap-3 pt-1">
        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-full bg-success px-4 py-2 text-sm font-semibold text-success-foreground transition hover:bg-success-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Buscar</button>
        <a href="{{ $clearFiltersUrl ?? route('public.audios') }}" class="filter-clear">Limpiar</a>
    </div>
</form>
