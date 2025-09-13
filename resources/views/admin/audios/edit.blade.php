@extends('layouts.dashboard')

@section('title', 'Editar Audio')

@section('content')
    <div class="space-y-6" data-lock-type="App\\Models\\Audio" data-lock-id="{{ $audio->id }}">
        <div id="lock-notice"></div>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-foreground">Editar Audio</h1>
        </div>

        @if ($errors->any())
          <div class="mb-4 rounded-xl p-3 bg-destructive/10 text-destructive">
            <h4 class="font-bold">Errores encontrados:</h4>
            <ul class="list-disc list-inside">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="p-6">
                <form id="edit-form" action="{{ route(auth()->user()->role . '.audios.update', $audio) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    {{-- Mini Player --}}
                    @if($audio->archivo)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-foreground">Audio Actual:</label>
                            <audio controls class="w-full mt-1">
                                <source src="{{ route('public.audios.play', $audio) }}" type="audio/mpeg">
                                Tu navegador no soporta el elemento de audio.
                            </audio>
                        </div>
                    @endif
                    
                    <input type="hidden" name="duracion" id="duracion" value="{{ old('duracion', $audio->duracion) }}">

                    <p class="text-sm text-muted-foreground">Los campos con asterisco (<span class="text-destructive">*</span>) son obligatorios para publicar.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="titulo" class="block text-sm font-medium text-foreground">Título: <span class="text-destructive">*</span></label>
                            <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $audio->titulo) }}" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground placeholder-muted-foreground focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-foreground">Descripción:</label>
                            <textarea name="descripcion" id="descripcion" placeholder="Describe brevemente el contenido del audio..." rows="3" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground placeholder-muted-foreground focus:border-primary focus:ring-primary sm:text-sm">{{ old('descripcion', $audio->descripcion) }}</textarea>
                        </div>
                        <div>
                            <label for="autor_id" class="block text-sm font-medium text-foreground">Autor: <span class="text-destructive">*</span></label>
                            <select name="autor_id" id="autor_id" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="">Seleccione un autor</option>
                                @foreach ($autores as $autor)
                                    <option value="{{ $autor->id }}" @selected(old('autor_id', $audio->autor_id) == $autor->id)>{{ $autor->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="serie_id" class="block text-sm font-medium text-foreground">Serie:</label>
                            <select name="serie_id" id="serie_id" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="">Seleccione una serie</option>
                                @foreach ($series as $serie)
                                    <option value="{{ $serie->id }}" @selected(old('serie_id', $audio->serie_id) == $serie->id)>{{ $serie->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="categoria_id" class="block text-sm font-medium text-foreground">Categoría: <span class="text-destructive">*</span></label>
                            <select name="categoria_id" id="categoria_id" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="">Seleccione una categoría</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" @selected(old('categoria_id', $audio->categoria_id) == $categoria->id)>{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <div id="libro-cita-group" class="hidden">
                                <label class="block text-sm font-medium text-foreground">Cita bíblica <span class="text-destructive">*</span></label>
                                <div class="flex items-center space-x-2">
                                    <select name="libro_id" id="libro_id" class="mt-1 block w-1/2 rounded-md border-border bg-background px-3 py-2 text-foreground focus:border-primary focus:ring-primary sm:text-sm">
                                        <option value="">Seleccione un libro</option>
                                        @foreach ($libros as $libro)
                                            <option value="{{ $libro->id }}" @selected(old('libro_id', $audio->libro_id) == $libro->id)>{{ $libro->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="cita_biblica" id="cita_biblica" value="{{ old('cita_biblica', $audio->cita_biblica) }}" placeholder="Ej: 5:23" class="mt-1 block w-1/2 rounded-md border-border bg-background px-3 py-2 text-foreground placeholder-muted-foreground focus:border-primary focus:ring-primary sm:text-sm">
                                </div>
                            </div>
                        </div>

                        
                        <div>
                            <label for="fecha_publicacion" class="block text-sm font-medium text-foreground">Fecha de Publicación: <span class="text-destructive">*</span></label>
                            <input type="date" name="fecha_publicacion" id="fecha_publicacion" value="{{ old('fecha_publicacion', $audio->fecha_publicacion ? \Carbon\Carbon::parse($audio->fecha_publicacion)->format('Y-m-d') : '') }}" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label for="estado" class="block text-sm font-medium text-foreground">Estado:</label>
                            <select name="estado" id="estado" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="Pendiente" @selected(old('estado', $audio->estado) == 'Pendiente')>Pendiente</option>
                                <option value="Publicado" @selected(old('estado', $audio->estado) == 'Publicado')>Publicar</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-2xl text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-success text-success-foreground hover:bg-success-hover h-10 px-4 py-2">
                            <i data-lucide="save" class="h-4 w-4 mr-2"></i>
                            Actualizar
                        </button>
                        <a href="{{ route(auth()->user()->role . '.audios.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-2xl text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const categoriaSelect = document.getElementById('categoria_id');
        const libroCitaGroup = document.getElementById('libro-cita-group');
        const predicacionesCatId = '{{ $predicacionesCat->id ?? null }}';

        function toggleLibroFields() {
            const selectedOptionValue = categoriaSelect.value;
            const predicacionesCatId = '{{ $predicacionesCat->id ?? null }}'; // Re-fetch here for clarity in logs
            console.log('selectedOptionValue:', selectedOptionValue);
            console.log('predicacionesCatId:', predicacionesCatId);
            const isPredicaciones = selectedOptionValue && predicacionesCatId && selectedOptionValue == predicacionesCatId;
            console.log('isPredicaciones:', isPredicaciones);
            libroCitaGroup.classList.toggle('hidden', !isPredicaciones);
            document.getElementById('libro_id').required = isPredicaciones;
        }

        if (categoriaSelect) {
            categoriaSelect.addEventListener('change', toggleLibroFields);
            toggleLibroFields(); // Run on page load
        }
    });
</script>
@endpush
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const root = document.querySelector('[data-lock-type]');
  const form = document.getElementById('edit-form');
  const notice = document.getElementById('lock-notice');
  if (!root || !form) return;
  const type = root.getAttribute('data-lock-type');
  const id = parseInt(root.getAttribute('data-lock-id'));
  await window.ContentLock.acquire(type, id, form, notice);
});
</script>
@endpush
