@extends('layouts.dashboard')

@section('title', 'Crear Usuario')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-semibold text-foreground mb-6">Crear Nuevo Usuario</h1>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-foreground">Nombre</label>
                <input type="text" name="name" id="name" class="mt-1 block w-full border-border bg-input text-foreground rounded-md shadow-sm sm:text-sm" required>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-foreground">Email</label>
                <input type="email" name="email" id="email" class="mt-1 block w-full border-border bg-input text-foreground rounded-md shadow-sm sm:text-sm" required>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-foreground">Contrase&ntilde;a</label>
                <input type="password" name="password" id="password" class="mt-1 block w-full border-border bg-input text-foreground rounded-md shadow-sm sm:text-sm" required>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-foreground">Confirmar Contrase&ntilde;a</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-border bg-input text-foreground rounded-md shadow-sm sm:text-sm" required>
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-foreground">Rol</label>
                <select name="role" id="role" class="mt-1 block w-full border-border bg-input text-foreground rounded-md shadow-sm sm:text-sm" required>
                    <option value="editor">Editor</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-foreground bg-secondary hover:bg-secondary/80">Cancelar</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-primary-foreground bg-primary hover:bg-primary/90">Crear Usuario</button>
            </div>
        </form>
    </div>
</div>
@endsection