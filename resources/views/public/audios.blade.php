@extends('layouts.dashboard')

@section('title', 'Biblioteca de Audios')

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-foreground">Biblioteca de Audios</h1>

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
                                    No hay audios publicados por el momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($audios->hasPages())
                <div class="p-6 border-t border-border">
                    {{ $audios->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
