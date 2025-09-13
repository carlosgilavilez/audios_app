@extends('layouts.dashboard')

@section('title', 'Autores')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-foreground">Autores</h1>
            <a href="{{ route(auth()->user()->role . '.autores.create') }}"
   class="flex items-center gap-2 px-6 h-12 text-lg font-medium rounded-xl bg-green-600 text-white hover:bg-green-700">
   <i data-lucide="plus" class="h-5 w-5"></i> Crear Autor
</a>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="p-6">
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
                                <td class="px-6 py-3 whitespace-nowrap text-base">{{ $autor->id }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-base">{{ $autor->nombre }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-base">{{ $autor->audios_count ?? 0 }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-base">{{ $autor->comentario }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-right text-base font-medium">
                                    <a href="{{ route(auth()->user()->role . '.autores.edit', $autor) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-lg text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-500 text-white hover:bg-blue-600 h-10 w-10" title="Editar">
                                        <i data-lucide="pencil" class="h-6 w-6"></i>
                                    </a>
                                    <button type="button" onclick="openModal({{ $autor->id }})" class="inline-flex items-center justify-center whitespace-nowrap rounded-lg text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-red-500 text-white hover:bg-red-600 h-10 w-10 ml-2" title="Eliminar">
                                        <i data-lucide="trash-2" class="h-6 w-6"></i>
                                    </button>

                                    <div id="deleteModal-{{ $autor->id }}" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                                      <div class="bg-white p-6 rounded-xl shadow-lg">
                                        <h2 class="text-xl font-bold mb-4">Confirmar eliminación</h2>
                                        <p>¿Seguro que deseas eliminar este autor? Los audios asociados quedarán en Pendiente.</p>
                                        <div class="flex justify-end mt-6 space-x-3">
                                          <button onclick="closeModal({{ $autor->id }})" class="px-4 py-2 bg-gray-200 rounded-lg">Cancelar</button>
                                          <form id="deleteForm-{{ $autor->id }}" action="{{ route(auth()->user()->role . '.autores.destroy', $autor) }}" method="POST" class="inline">
                                              @csrf
                                              @method('DELETE')
                                              <button type="submit" class="px-4 py-2 bg-red-400 text-white rounded-lg hover:bg-red-500">Eliminar</button>
                                          </form>
                                        </div>
                                      </div>
                                    </div>
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
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        _method: 'DELETE'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal(autorId);
                        if (row) {
                            row.classList.add('opacity-0', 'transition', 'duration-500');
                            setTimeout(() => {
                                row.remove();
                                alert('Autor eliminado correctamente.'); // Replace with a proper toast
                            }, 500);
                        }
                    } else {
                        alert('Error al eliminar el autor.'); // Replace with a proper toast
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión al eliminar el autor.'); // Replace with a proper toast
                });
            });
        });
    });
</script>
@endpush
