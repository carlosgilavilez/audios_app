@php
    $resolvedFilters = array_merge([
        'q' => null,
        'categoria_id' => null,
        'autor_id' => null,
        'anio' => null,
    ], $filters ?? []);
    $formId = $formId ?? 'filters-form';
    $hasActiveFilters = $hasActiveFilters ?? false;
    $clearButtonClasses = $hasActiveFilters
        ? 'inline-flex items-center justify-center rounded-full border border-primary/40 bg-primary/10 px-3 py-1 text-xs font-semibold text-primary shadow-sm transition hover:bg-primary/15 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary'
        : 'inline-flex items-center justify-center rounded-full border border-transparent px-3 py-1 text-xs font-semibold text-muted-foreground transition hover:bg-muted/60 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary';
@endphp

<form id="{{ $formId }}" action="{{ route('public.audios') }}" method="GET" class="grid gap-4 text-sm" data-filters-form>
    <input type="hidden" name="view" value="{{ request('view', $viewMode ?? 'table') }}">
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
        <input type="search" name="q" id="{{ $formId }}-q" value="{{ $resolvedFilters['q'] }}" placeholder="Título, autor" class="w-full rounded-full border border-border bg-background px-4 py-2 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
    </div>

    <div class="space-y-2">
        <label for="{{ $formId }}-categoria" class="text-xs font-semibold tracking-wide text-muted-foreground">Categoría</label>
        <select name="categoria_id" id="{{ $formId }}-categoria" class="w-full rounded-full border border-border bg-background px-4 py-2 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            <option value="">Todas</option>
            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected($resolvedFilters['categoria_id'] == $categoria->id)>{{ $categoria->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="space-y-2">
        <label for="{{ $formId }}-autor" class="text-xs font-semibold tracking-wide text-muted-foreground">Autor</label>
        <select name="autor_id" id="{{ $formId }}-autor" class="w-full rounded-full border border-border bg-background px-4 py-2 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            <option value="">Todos</option>
            @foreach($autores as $autor)
                <option value="{{ $autor->id }}" @selected($resolvedFilters['autor_id'] == $autor->id)>{{ $autor->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="space-y-2">
        <label for="{{ $formId }}-anio" class="text-xs font-semibold tracking-wide text-muted-foreground">Año</label>
        <select name="anio" id="{{ $formId }}-anio" class="w-full rounded-full border border-border bg-background px-4 py-2 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            <option value="">Todos</option>
            @foreach($years as $year)
                <option value="{{ $year }}" @selected($resolvedFilters['anio'] == $year)>{{ $year }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex items-center justify-between gap-3 pt-1">
        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-success px-4 py-2 text-sm font-semibold text-success-foreground transition hover:bg-success-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Aplicar</button>
        <a href="{{ $clearFiltersUrl ?? route('public.audios') }}" class="{{ $clearButtonClasses }}">Limpiar</a>
    </div>
</form>
