<div class="inline-flex items-center gap-2">
    <label for="per-page" class="text-sm font-medium text-muted-foreground">Mostrar:</label>
    <select id="per-page" name="per_page"
        class="block w-20 sm:w-auto rounded-md border-border bg-transparent py-1.5 pl-3 pr-6 text-sm font-medium text-foreground transition hover:bg-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
        onchange="window.location.href = this.value">
        @php
            $options = [10, 25, 50, 100];
            $currentUrl = url()->current();
            $queryParams = request()->except('per_page', 'page');
        @endphp
        @foreach ($options as $option)
            <option value="{{ $currentUrl }}?{{ http_build_query(array_merge($queryParams, ['per_page' => $option])) }}"
                @if ($perPage == $option) selected @endif>
                {{ $option }}
            </option>
        @endforeach
    </select>
</div>
