@extends('layouts.dashboard')

@section('title', 'Gestión de Usuarios')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-foreground">Gestión de Usuarios</h1>
            <a href="{{ route('admin.users.create') }}"
               class="flex items-center gap-2 px-4 h-10 text-base font-medium rounded-xl bg-green-600 text-white hover:bg-green-700">
                <i data-lucide="plus" class="h-5 w-5"></i> Crear Usuario
            </a>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="p-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-muted-foreground uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-muted-foreground uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-muted-foreground uppercase tracking-wider">Rol</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-foreground">{{ $user->name }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-muted-foreground">{{ $user->email }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-muted-foreground">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->role === 'admin' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    @if ($user->role === 'editor' && $user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('¿Estás seguro de que quieres eliminar a este usuario? Esta acción no se puede deshacer.');"
                                                    class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-7 w-7">
                                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-muted-foreground">
                                    No hay usuarios para mostrar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
