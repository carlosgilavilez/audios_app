@extends('layouts.dashboard')

@section('title', 'Audios')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-foreground">Audios</h1>
            <a href="{{ route(auth()->user()->role . '.audios.create') }}"
   class="flex items-center gap-2 px-4 h-10 text-base font-medium rounded-xl bg-green-600 text-white hover:bg-green-700">
   <i data-lucide="upload" class="h-5 w-5"></i> Subir Audio
</a>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
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
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    @if ($audio->estado == 'Publicado' || $audio->estado == 'Normal')
                                        <span class="px-2.5 py-0.5 rounded-full text-sm font-semibold bg-green-200 text-green-800 dark:bg-green-900 dark:text-green-300">
                                            Público
                                        </span>
                                    @elseif ($audio->estado == 'Pendiente')
                                        <span class="px-2.5 py-0.5 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                            Pendiente
                                        </span>
                                    @else
                                        {{ $audio->estado }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $audio->duracion ?? '' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.audios.edit', $audio) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-lg text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-500 text-white hover:bg-blue-600 h-7 w-7" title="Editar">
                                        <i data-lucide="pencil" class="h-4 w-4"></i>
                                    </a>
                                    <form action="{{ route('admin.audios.destroy', $audio) }}" method="POST" class="inline">
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
</script>
@endpush
