@extends('layouts.dashboard')

@section('title', 'Editar Audio')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-foreground">Editar Audio</h1>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="p-6">
                <form action="{{ route(auth()->user()->role . '.audios.update', $audio) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="titulo" class="block text-sm font-medium text-foreground">T&iacute;tulo:</label>
                        <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $audio->titulo) }}" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground placeholder-muted-foreground focus:border-primary focus:ring-primary sm:text-sm">
                    </div>

                    <div>
                        <label for="autor_id" class="block text-sm font-medium text-foreground">Autor:</label>
                        <select name="autor_id" id="autor_id" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="">Seleccione un autor</option>
                            @foreach ($autores as $autor)
                                <option value="{{ $autor->id }}" {{ old('autor_id', $audio->autor_id) == $autor->id ? 'selected' : '' }}>{{ $autor->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="serie_id" class="block text-sm font-medium text-foreground">Serie:</label>
                        <select name="serie_id" id="serie_id" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="">Seleccione una serie</option>
                            @foreach ($series as $serie)
                                <option value="{{ $serie->id }}" {{ old('serie_id', $audio->serie_id) == $serie->id ? 'selected' : '' }}>{{ $serie->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="categoria_id" class="block text-sm font-medium text-foreground">Categoría:</label>
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
                        <label for="fecha_publicacion" class="block text-sm font-medium text-foreground">Fecha de Publicaci&oacute;n:</label>
                        <input type="date" name="fecha_publicacion" id="fecha_publicacion" value="{{ old('fecha_publicacion', $audio->fecha_publicacion) }}" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground focus:border-primary focus:ring-primary sm:text-sm">
                    </div>

                    <div>
                        <label for="url_audio" class="block text-sm font-medium text-foreground">URL del Audio (temporal):</label>
                        <input type="text" name="url_audio" id="url_audio" value="{{ old('url_audio', $audio->url_audio) }}" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground placeholder-muted-foreground focus:border-primary focus:ring-primary sm:text-sm">
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-2xl text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-success text-success-foreground hover:bg-success-hover h-12 px-4 py-2">
                            <i data-lucide="save" class="h-4 w-4 mr-2"></i>
                            Actualizar
                        </button>
                        <a href="{{ route('editor.audios.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-2xl text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-12 px-4 py-2">
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
            const isPredicaciones = selectedOptionValue && predicacionesCatId && selectedOptionValue == predicacionesCatId;
            console.log('selectedOptionValue:', selectedOptionValue);
            console.log('predicacionesCatId:', predicacionesCatId);
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
