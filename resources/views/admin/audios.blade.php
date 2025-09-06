@extends('layouts.dashboard')

@section('title', 'Gestión de Audios')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-foreground">Gestión de Audios</h1>
            <a href="{{ route('admin.audios.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-success text-success-foreground hover:bg-success-hover h-10 px-4 py-2">
                <span class="h-4 w-4 mr-2">➕</span>
                Subir Audio
            </a>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-lg font-medium flex items-center gap-2">
                    <span class="h-5 w-5">⚙️</span>
                    Filtros y Búsqueda
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 pt-2">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4">🔍</span>
                        <input
                            type="text"
                            placeholder="Buscar audios..."
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-9"
                        />
                    </div>
                    <!-- Autor Filter -->
                    <select class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="all">Todos los autores</option>
                        <option value="Juan Pérez">Juan Pérez</option>
                        <option value="María García">María García</option>
                        <option value="Ana López">Ana López</option>
                        <option value="Carlos Ruiz">Carlos Ruiz</option>
                    </select>
                    <!-- Serie Filter -->
                    <select class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="all">Todas las series</option>
                        <option value="Meditación para Principiantes">Meditación para Principiantes</option>
                        <option value="Sueño Reparador">Sueño Reparador</option>
                        <option value="Mindfulness Diario">Mindfulness Diario</option>
                        <option value="Gestión del Estrés">Gestión del Estrés</option>
                    </select>
                    <!-- Categoría Filter -->
                    <select class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="all">Todas las categorías</option>
                        <option value="Relajación">Relajación</option>
                        <option value="Sueño">Sueño</option>
                        <option value="Mindfulness">Mindfulness</option>
                        <option value="Bienestar">Bienestar</option>
                    </select>
                    <!-- Estado Filter -->
                    <select class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="all">Todos los estados</option>
                        <option value="Normal">Normal</option>
                        <option value="Pendiente">Pendiente</option>
                    </select>
                </div>
            </div>
            <div class="p-6 pt-0">
                <div class="w-full overflow-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">ID</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Nombre</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Autor</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Serie</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Categoría</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Estado</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Fecha</th>
                                <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            @foreach($audios as $audio)
                                <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                    <td class="p-4 align-middle font-medium">{{ $audio->id }}</td>
                                    <td class="p-4 align-middle font-medium">{{ $audio->titulo }}</td>
                                    <td class="p-4 align-middle">{{ $audio->autor->nombre ?? 'N/A' }}</td>
                                    <td class="p-4 align-middle text-muted-foreground">{{ $audio->serie->nombre ?? 'N/A' }}</td>
                                    <td class="p-4 align-middle">
                                        <x-ui.cat-badge :name="optional($audio->categoria)->nombre" />
                                    </td>
                                    <td class="p-4 align-middle">
                                        @if($audio->estado === 'Normal')
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 bg-success/10 text-success border-success/20">Normal</span>
                                        @elseif($audio->estado === 'Pendiente')
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-warning/10 text-warning border-warning/20">Pendiente</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">{{ $audio->estado }}</span>
                                        @endif
                                    </td>
                                    <td class="p-4 align-middle text-muted-foreground">{{ $audio->fecha_publicacion }}</td>
                                    <td class="p-4 align-middle text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <x-player.play-button
                                              :src="$audio->play_url"
                                              :download="$audio->download_url ?? $audio->play_url"
                                              :title="$audio->titulo"
                                              :author="optional($audio->autor)->nombre"
                                              :index="$loop->index"
                                            />
                                            <a href="{{ route('admin.audios.edit', $audio->id) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8 p-0">
                                                ✏️
                                            </a>
                                            <form action="{{ route('admin.audios.destroy', $audio->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este audio?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8 p-0 text-destructive hover:text-destructive">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-6 pt-0">
                    {{ $audios->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
