@extends('layouts.dashboard')

@section('title', 'Biblioteca de Audios')

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-foreground">Biblioteca de Audios</h1>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="p-6">
                <form action="{{ route('public.audios') }}" method="GET" class="space-y-4 md:space-y-0 md:flex md:items-end md:space-x-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-muted-foreground">Buscar</label>
                        <input type="text" name="search" id="search" class="mt-1 w-72 max-w-full rounded-md border px-3 py-2 text-sm bg-background" value="{{ request('search') }}" placeholder="Título, autor, serie, turno...">
                    </div>
                    <div>
                        <label for="per_page" class="block text-sm font-medium text-muted-foreground">Por página</label>
                        <select name="per_page" id="per_page" class="mt-1 block w-full pl-3 pr-10 py-2 bg-background border-border border rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Filtrar
                        </button>
                        <a href="{{ route('public.audios') }}" class="inline-flex items-center justify-center px-4 py-2 border border-border text-sm font-medium rounded-md shadow-sm text-muted-foreground bg-muted hover:bg-muted/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ring">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>
            <div class="p-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted/50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider"></th> {{-- Play --}}
                            <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Categoría</th>
                            <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Autor</th>
                            <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Serie</th>
                            <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Fecha</th>
                            <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Cita Bíblica</th>
                            <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Turno</th>
                            <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Duración</th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        @forelse ($audios as $audio)
                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <x-player.play-button
                                        :src="route('public.audios.play', $audio)"
                                        :title="$audio->titulo ?? 'Unknown Title'"
                                        :author="$audio->autor?->nombre ?? 'Unknown Artist'"
                                        :download="route('public.download_audio', $audio)"
                                        :index="$loop->index"
                                    />
                                </td>
                                <td class="px-4 py-3 text-sm font-medium" style="max-width: 250px;">{{ $audio->titulo ?? '' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <x-ui.cat-badge :name="$audio->categoria?->nombre ?? ''" />
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $audio->autor?->nombre ?? '' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $audio->serie?->nombre ?? '' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $audio->fecha_publicacion ? \Carbon\Carbon::parse($audio->fecha_publicacion)->format('d/m/Y') : '' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">{{ trim(($audio->libro?->nombre ?? '') . ' ' . ($audio->cita_biblica ?? '')) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $audio->turno?->nombre ?? '' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $audio->duracion ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-muted-foreground">
                                    No hay audios que coincidan con la búsqueda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($audios->hasPages())
                <div class="p-6 border-t border-border">
                    {{ $audios->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
