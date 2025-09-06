@extends('layouts.dashboard')

@section('title', 'Panel de Administración')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-foreground">Panel de Administración</h1>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
                <div class="flex flex-row items-center justify-between space-y-0 p-6 pb-2">
                    <h3 class="text-base font-medium text-muted-foreground">Autores</h3>
                    <i data-lucide="users" class="h-4 w-4 text-muted-foreground"></i>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold text-foreground">{{ $autoresCount }}</div>
                    <p class="text-base text-muted-foreground mt-1">Autores registrados</p>
                </div>
            </div>
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
                <div class="flex flex-row items-center justify-between space-y-0 p-6 pb-2">
                    <h3 class="text-base font-medium text-muted-foreground">Series</h3>
                    <i data-lucide="music" class="h-4 w-4 text-muted-foreground"></i>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold text-foreground">{{ $seriesCount }}</div>
                    <p class="text-base text-muted-foreground mt-1">Series creadas</p>
                </div>
            </div>
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
                <div class="flex flex-row items-center justify-between space-y-0 p-6 pb-2">
                    <h3 class="text-base font-medium text-muted-foreground">Audios</h3>
                    <i data-lucide="headphones" class="h-4 w-4 text-muted-foreground"></i>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold text-foreground">{{ $audiosCount }}</div>
                    <p class="text-base text-muted-foreground mt-1">Audios subidos</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-xl font-semibold">Acciones Rápidas</h3>
            </div>
            <div class="p-6 pt-0 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('admin.autores.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-xl text-lg font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-500 text-white hover:bg-blue-600 h-12 px-6">
                        <i data-lucide="plus" class="h-5 w-5 mr-2"></i>
                        Nuevo Autor
                    </a>
                    <a href="{{ route('admin.series.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-xl text-lg font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-500 text-white hover:bg-blue-600 h-12 px-6">
                        <i data-lucide="plus" class="h-5 w-5 mr-2"></i>
                        Nueva Serie
                    </a>
                    <a href="{{ route('admin.audios.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-xl text-lg font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-green-600 text-white hover:bg-green-700 h-12 px-6">
                        <i data-lucide="upload" class="h-5 w-5 mr-2"></i>
                        Subir Audio
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-xl font-semibold">Actividad Reciente</h3>
            </div>
            <div class="p-6 pt-0">
                <div class="space-y-3">
                    @forelse ($activityLogs as $log)
                        <div class="flex items-center justify-between py-2 border-b border-border/30">
                            <div>
                                <p class="text-base font-medium">{{ $log->description }}</p>
                                <p class="text-base text-muted-foreground">Por {{ $log->user->name ?? 'Sistema' }} • {{ $log->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-base text-muted-foreground">No hay actividad reciente.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
