@extends('layouts.dashboard')

@section('title', 'Series')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-foreground">Series</h1>
            <a href="{{ route(auth()->user()->role . '.series.create') }}"
   class="flex items-center gap-2 px-6 h-12 text-lg font-medium rounded-xl bg-green-600 text-white hover:bg-green-700">
   <i data-lucide="plus" class="h-4 w-4"></i> Crear Serie
</a>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="p-6">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Audios</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Comentario</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                       @foreach ($series as $serie)
                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                                <td class="px-6 py-3 whitespace-nowrap text-xs text-muted-foreground font-mono">{{ $serie->id }}</td>
                                <td class="px-6 py-3 whitespace-nowrap">{{ $serie->nombre }}</td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <a class="text-blue-600 hover:underline" title="Ver audios de {{ $serie->nombre }}" href="{{ route(auth()->user()->role . '.audios.index', ['serie_id' => $serie->id]) }}">
                                        {{ $serie->audios_count ?? 0 }}
                                    </a>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">{{ $serie->comentario }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('editor.series.edit', $serie) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-lg text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-400 text-white hover:bg-blue-500 h-9 w-9" title="Editar">
                                        <i data-lucide="pencil" class="h-6 w-6"></i>
                                    </a>
                                    <form action="{{ route(auth()->user()->role . '.series.destroy', $serie->id) }}" method="POST" 
      onsubmit="return confirm('⚠️ ¿Estás seguro de eliminar esta serie? Los audios asociados quedarán en estado Pendiente.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-lg text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-red-400 text-white hover:bg-red-500 h-9 w-9 ml-2" title="Eliminar">
                                            <i data-lucide="trash-2" class="h-6 w-6"></i>
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
