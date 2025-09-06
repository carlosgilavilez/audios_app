@extends('layouts.dashboard')

@section('title', 'Crear Serie')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-foreground">Crear Serie</h1>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="p-6">
                <form method="POST" action="{{ route(auth()->user()->role . '.series.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="nombre" class="block text-sm font-medium text-foreground">Nombre</label>
                        <input type="text" id="nombre" name="nombre" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground placeholder-muted-foreground focus:border-primary focus:ring-primary sm:text-sm" value="{{ old('nombre') }}" required autofocus>
                        @error('nombre')
                            <p class="mt-2 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="comentario" class="block text-sm font-medium text-foreground">Comentario</label>
                        <textarea id="comentario" name="comentario" rows="4" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground placeholder-muted-foreground focus:border-primary focus:ring-primary sm:text-sm">{{ old('comentario') }}</textarea>
                        @error('comentario')
                            <p class="mt-2 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-2xl text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-success text-success-foreground hover:bg-success-hover h-12 px-4 py-2">
                            <i data-lucide="save" class="h-4 w-4 mr-2"></i>
                            Guardar
                        </button>
                        <a href="{{ route('editor.series.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-2xl text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-12 px-4 py-2">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
