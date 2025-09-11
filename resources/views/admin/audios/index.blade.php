@extends('layouts.dashboard')

@section('title', 'Audios')

@section('content')
<div class="space-y-6">
  @if (session('ok') || session('success') || session('error'))
    <div class="rounded-md p-3 text-sm {{ session('error') ? 'bg-destructive/10 text-destructive border border-destructive/40' : 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300 border border-green-300/50' }}">
      {{ session('ok') ?? session('success') ?? session('error') }}
    </div>
  @endif
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-foreground">Audios</h1>
    <a href="{{ route(auth()->user()->role . '.audios.create') }}"
       class="flex items-center gap-2 px-4 h-10 text-base font-medium rounded-xl bg-green-600 text-white hover:bg-green-700">
       <i data-lucide="upload" class="h-5 w-5"></i> Subir Audio
    </a>
  </div>

  <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
    <div class="p-6 space-y-4">
      <form method="GET" action="{{ route(auth()->user()->role . '.audios.index') }}" class="flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por título, autor, serie o cita" class="w-72 max-w-full rounded-md border px-3 py-2 text-sm bg-background" />
        <select name="estado" class="rounded-md border px-3 py-2 text-sm bg-background">
          <option value="">Todos los estados</option>
          <option value="Publicado" @selected(request('estado')==='Publicado')>Publicado</option>
          <option value="Pendiente" @selected(request('estado')==='Pendiente')>Pendiente</option>
          <option value="Normal" @selected(request('estado')==='Normal')>Normal</option>
        </select>
        <select name="per_page" class="rounded-md border px-3 py-2 text-sm bg-background" onchange="this.form.submit()">
            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 por página</option>
            <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 por página</option>
            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 por página</option>
            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 por página</option>
        </select>
        <button type="submit" class="inline-flex items-center gap-2 px-4 h-9 text-sm font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90">Filtrar</button>
        @if (request()->has('search') || request()->has('estado'))
          <a href="{{ route(auth()->user()->role . '.audios.index') }}" class="inline-flex items-center justify-center px-4 h-9 border border-border text-sm font-medium rounded-md shadow-sm text-muted-foreground bg-muted hover:bg-muted/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ring">Limpiar</a>
        @endif
        <span class="text-sm text-muted-foreground ml-auto">{{ $audios->total() }} resultados</span>
      </form>
      <div class="relative max-w-full min-w-0">
        <div id="audios-scroll-top"
             class="overflow-x-auto overflow-y-hidden custom-hscroll h-6 -mx-4 md:-mx-6"
             aria-hidden="true" style="display:none">
          <div id="audios-scroll-top-spacer" style="height:1px;width:0"></div>
        </div>

        <div class="-mx-4 md:-mx-6">
          <div id="audios-scroll-bottom" class="overflow-x-auto overflow-y-hidden custom-hscroll">
            <table id="audios-table" class="min-w-full w-full table-auto text-sm divide-y divide-border [&>tbody>tr>td]:px-2 md:[&>tbody>tr>td]:px-3">
              <thead class="bg-muted/50">
                <tr class="[&>th]:px-4 [&>th]:py-3 [&>th]:text-left [&>th]:whitespace-nowrap">
                  <th scope="col"></th> {{-- Play --}}
                  <th scope="col" class="w-[280px]">Nombre</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Categoría</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Autor</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Serie</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Fecha</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Cita Bíblica</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Turno</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Estado</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Duración</th>
                  <th scope="col" class="relative px-4 py-3">
                    <span class="sr-only">Acciones</span>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-card divide-y divide-border">
              @foreach ($audios as $audio)
                <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-sm">
                    <x-player.play-button
                      :src="route('public.audios.play', $audio)"
                      :title="$audio->titulo ?? 'Unknown Title'"
                      :author="$audio->autor?->nombre ?? 'Unknown Artist'"
                      :download="route('public.download_audio', $audio)"
                      :index="$loop->index"
                    />
                  </td>
                  <td class="px-2 md:px-3 py-3 text-sm font-medium w-[280px] leading-tight">
                    @php
                      $words = preg_split('/\s+/', trim($audio->titulo ?? ''));
                      $chunks = array_chunk($words, 3);
                    @endphp
                    @foreach($chunks as $i => $chunk)
                      <span class="block {{ $i>0 ? 'opacity-90' : '' }}">{{ implode(' ', $chunk) }}</span>
                    @endforeach
                  </td>
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-sm">
                    <x-ui.cat-badge :name="$audio->categoria?->nombre ?? ''" />
                  </td>
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-sm">{{ $audio->autor?->nombre ?? '' }}</td>
                  <td class="px-2 md:px-3 py-3 text-sm whitespace-normal break-words" style="max-width: 220px;">{{ $audio->serie?->nombre ?? '' }}</td>
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-sm">{{ $audio->fecha_publicacion ? \Carbon\Carbon::parse($audio->fecha_publicacion)->format('d/m/Y') : '' }}</td>
                  <td class="px-2 md:px-3 py-3 text-sm whitespace-normal break-words" style="max-width: 260px;">{{ trim(($audio->libro?->nombre ?? '') . ' ' . ($audio->cita_biblica ?? '')) }}</td>
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-sm">{{ $audio->turno?->nombre ?? '' }}</td>
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-sm">
                    @if ($audio->estado == 'Publicado' || $audio->estado == 'Normal')
                      <span class="px-2.5 py-0.5 rounded-full text-sm font-semibold bg-green-200 text-green-800 dark:bg-green-900 dark:text-green-300">Público</span>
                    @elseif ($audio->estado == 'Pendiente')
                      <span class="px-2.5 py-0.5 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">Pendiente</span>
                    @else
                      {{ $audio->estado }}
                    @endif
                  </td>
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-sm" style="width: 80px;">{{ $audio->duracion ?? '' }}</td>
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-left text-sm font-medium">
                    <a href="{{ route(auth()->user()->role . '.audios.edit', $audio) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-lg text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-500 text-white hover:bg-blue-600 h-7 w-7" title="Editar">
                      <i data-lucide="pencil" class="h-4 w-4"></i>
                    </a>
                    <form action="{{ route(auth()->user()->role . '.audios.destroy', $audio) }}" method="POST" class="inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" onclick="return confirm('¿Estás seguro de que quieres eliminar este audio? Esta acción no se puede deshacer.');" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-7 w-7 ml-2">
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  window.audioPlaylist = [
    @foreach ($audios as $audio)
    {
      id: {{ $audio->id }},
      play_url: '{{ route('public.audios.play', $audio) }}',
      title: '{{ $audio->titulo ?? 'Unknown Title' }}',
      artist: '{{ $audio->autor?->nombre ?? 'Unknown Artist' }}',
      download_url: '{{ route('public.download_audio', $audio) }}'
    },
    @endforeach
  ];

  // Sincroniza barras superior e inferior y muestra la superior solo si hay overflow
  document.addEventListener('DOMContentLoaded', function () {
    const top    = document.getElementById('audios-scroll-top');
    const bottom = document.getElementById('audios-scroll-bottom');
    const table  = document.getElementById('audios-table');
    if (!top || !bottom || !table) return;

    const topFiller = top.querySelector('div');
    const sync = (from, to) => { to.scrollLeft = from.scrollLeft; };

    top.addEventListener('scroll', () => sync(top, bottom));
    bottom.addEventListener('scroll', () => sync(bottom, top));

    const update = () => {
      try {
        const w = table.scrollWidth || table.offsetWidth;
        if (topFiller) topFiller.style.width = w + 'px';
        const hasOverflow = w > bottom.clientWidth + 1;
        top.style.display = hasOverflow ? 'block' : 'none';
      } catch (e) {}
    };

    update();
    if (window.ResizeObserver) new ResizeObserver(update).observe(table);
    window.addEventListener('resize', update);
  });
</script>
@endpush
