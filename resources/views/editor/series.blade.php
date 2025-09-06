@extends('layouts.dashboard')

@section('title', 'Gestión de Series')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-foreground">Gestión de Series</h1>
            <a href="{{ url('/series/new') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-success text-success-foreground hover:bg-success-hover h-10 px-4 py-2">
                <span class="h-4 w-4 mr-2">➕</span>
                Nueva Serie
            </a>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-lg font-medium">Lista de Series</h3>
                <div class="flex items-center space-x-2 pt-2">
                    <div class="relative flex-1 max-w-sm">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4">🔍</span>
                        <input
                            type="text"
                            placeholder="Buscar series..."
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-9"
                        />
                    </div>
                </div>
            </div>
            <div class="p-6 pt-0">
                <div class="w-full overflow-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">ID</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Nombre</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Comentario</th>
                                <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            @php
                                $series = [
                                    [ 'id' => 1, 'name' => 'Meditación para Principiantes', 'comment' => 'Serie introductoria de 10 sesiones de meditación' ],
                                    [ 'id' => 2, 'name' => 'Mindfulness Diario', 'comment' => 'Prácticas de atención plena para cada día' ],
                                    [ 'id' => 3, 'name' => 'Relajación Profunda', 'comment' => 'Técnicas avanzadas de relajación y descanso' ],
                                    [ 'id' => 4, 'name' => 'Gestión del Estrés', 'comment' => 'Herramientas para manejar la ansiedad y el estrés' ],
                                    [ 'id' => 5, 'name' => 'Sueño Reparador', 'comment' => 'Audios para mejorar la calidad del descanso nocturno' ]
                                ];
                            @endphp
                            @foreach($series as $serie)
                                <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                    <td class="p-4 align-middle font-medium">{{ $serie['id'] }}</td>
                                    <td class="p-4 align-middle font-medium">{{ $serie['name'] }}</td>
                                    <td class="p-4 align-middle text-muted-foreground">{{ $serie['comment'] }}</td>
                                    <td class="p-4 align-middle text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8 p-0">
                                                ✏️
                                            </button>
                                            <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8 p-0 text-destructive hover:text-destructive">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
