@extends('layouts.dashboard')

@section('title', 'Audios')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-foreground">Audios</h1>
            <a href="{{ route(auth()->user()->role . '.audios.create') }}"
   class="flex items-center gap-2 px-6 h-12 text-lg font-medium rounded-xl bg-green-600 text-white hover:bg-green-700">
   <i data-lucide="upload" class="h-5 w-5"></i> Subir Audio
</a>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="p-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">T&iacute;tulo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Autor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Serie</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Fecha</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                       @foreach ($audios as $audio)
                            <tr class="hover:bg-muted/50">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $audio->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $audio->titulo }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $audio->autor?->nombre ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $audio->serie?->nombre ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $audio->estado }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $audio->fecha_publicacion }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('editor.audios.edit', $audio) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-info text-info-foreground hover:bg-info/90 h-9 w-9">
                                        <i data-lucide="pencil" class="h-4 w-4"></i>
                                    </a>
                                    <form action="{{ route('editor.audios.destroy', $audio) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-9 w-9 ml-2">
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
