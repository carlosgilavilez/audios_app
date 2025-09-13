@extends('layouts.dashboard')

@section('title', 'Autores')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-foreground">Autores</h1>
            <a href="{{ route(auth()->user()->role . '.autores.create') }}"
   class="flex items-center gap-2 px-4 h-10 text-base font-medium rounded-xl bg-green-600 text-white hover:bg-green-700">
   <i data-lucide="plus" class="h-5 w-5"></i> Crear Autor
</a>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="p-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-muted-foreground uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-muted-foreground uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-muted-foreground uppercase tracking-wider">Audios</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-muted-foreground uppercase tracking-wider">Comentario</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                       @foreach ($autores as $autor)
                            <tr id="autor-row-{{ $autor->id }}" class="hover:bg-gray-100 dark:hover:bg-gray-700">
                                <td class="px-6 py-3 whitespace-nowrap text-sm">{{ $autor->id }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm">{{ $autor->nombre }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm">{{ $autor->audios_count ?? 0 }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm">{{ $autor->comentario }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route(auth()->user()->role . '.autores.edit', $autor) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-lg text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-500 text-white hover:bg-blue-600 h-7 w-7" title="Editar">
                                        <i data-lucide="pencil" class="h-4 w-4"></i>
                                    </a>
                                    <form method="POST" action="{{ route(auth()->user()->role . '.autores.destroy', $autor) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este autor? Los audios asociados quedarán en estado Pendiente.');" style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-lg text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-7 w-7 ml-2" title="Eliminar">
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

@push('scripts')
<script>
    function openModal(id) {
        document.getElementById('deleteModal-' + id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById('deleteModal-' + id).classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[id^="deleteForm-"]').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const autorId = this.id.replace('deleteForm-', '');
                const row = document.getElementById('autor-row-' + autorId);

                fetch(this.action, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal(autorId);
                        if (row) {
                            row.classList.add('opacity-0', 'transition', 'duration-500');
                            setTimeout(() => {
                                row.remove();
                                // Autor eliminado correctamente.
                            }, 500);
                        }
                    } else {
                        alert('Error al eliminar el autor.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión al eliminar el autor.');
                });
            });
        });
    });
</script>
@endpush
