@extends('layouts.dashboard')

@section('title', 'Crear Nuevo Usuario')

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-foreground">Crear Nuevo Usuario (Editor)</h1>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-muted-foreground">Nombre</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full px-3 py-2 bg-background border border-border rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm @error('name') border-destructive @enderror">
                        @error('name')
                            <p class="mt-2 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-muted-foreground">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required class="mt-1 block w-full px-3 py-2 bg-background border border-border rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm @error('email') border-destructive @enderror">
                        @error('email')
                            <p class="mt-2 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="px-6 py-4 bg-muted/50 border-t border-border flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Crear Usuario y Enviar Invitación
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
