@extends('layouts.dashboard')

@section('title', 'Subir Audio')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-foreground">Subir Audio</h1>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
            <div class="p-6">
                <form id="audio-form" action="{{ route(auth()->user()->role . '.audios.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @if ($errors->any())
                        <div class="rounded-md border border-destructive/40 bg-destructive/10 p-3 text-destructive text-sm">
                            <div class="font-semibold mb-1">Hay errores en el formulario:</div>
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <input type="hidden" name="temp_file_path" id="temp-file-path">
                    <input type="hidden" name="duracion" id="duracion">

                    <div>
                        <label for="archivo-input" class="block text-sm font-medium text-foreground">Archivo de Audio:</label>
                        <div id="drop-area" class="mt-1 flex justify-center items-center px-6 pt-5 pb-6 border-2 border-dashed border-border rounded-md cursor-pointer hover:border-primary transition duration-300 ease-in-out">
                            <div class="space-y-1 text-center">
                                <i data-lucide="upload-cloud" class="mx-auto h-12 w-12 text-muted-foreground"></i>
                                <div class="flex text-sm text-muted-foreground">
                                    <label for="archivo-input" class="relative cursor-pointer rounded-md bg-background font-medium text-primary hover:text-primary/80 focus-within:outline-none focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2">
                                        <span>Sube un archivo</span>
                                        <input id="archivo-input" name="archivo" type="file" class="sr-only" accept="audio/*">
                                    </label>
                                    <p class="pl-1">o arrastra y suelta</p>
                                </div>
                                <p class="text-xs text-muted-foreground">MP3, WAV, AAC hasta 50MB</p>
                            </div>
                        </div>
                        <div id="progress-container" class="mt-4 hidden">
                            <div class="text-sm font-medium text-foreground mb-1" id="file-name"></div>
                            <div class="w-full bg-muted rounded-full h-2.5">
                                <div id="progress-bar" class="bg-primary h-2.5 rounded-full" style="width: 0%"></div>
                            </div>
                            <div class="text-sm text-muted-foreground mt-1" id="progress-text">0%</div>
                        </div>
                        <p id="upload-status" class="mt-2 text-sm"></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="titulo" class="block text-sm font-medium text-foreground">Título:</label>
                            <input type="text" name="titulo" id="titulo" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground placeholder-muted-foreground focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label for="autor_id" class="block text-sm font-medium text-foreground">Autor:</label>
                            <select name="autor_id" id="autor_id" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="">Seleccione un autor</option>
                                @foreach ($autores as $autor)
                                    <option value="{{ $autor->id }}">{{ $autor->nombre }}</option>
                                @endforeach
                            </select>
                            <span id="new-author-message" class="text-xs text-blue-600 hidden"></span>
                        </div>
                        <div>
                            <label for="serie_id" class="block text-sm font-medium text-foreground">Serie:</label>
                            <select name="serie_id" id="serie_id" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="">Seleccione una serie</option>
                                @foreach ($series as $serie)
                                    <option value="{{ $serie->id }}">{{ $serie->nombre }}</option>
                                @endforeach
                            </select>
                            <span id="new-series-message" class="text-xs text-blue-600 hidden"></span>
                        </div>
                        <div>
                            <label for="categoria_id" class="block text-sm font-medium text-foreground">Categoría:</label>
                            <select name="categoria_id" id="categoria_id" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="">Seleccione una categoría</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" data-nombre="{{ $categoria->nombre }}">{{ $categoria->nombre }}</option>
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
                                            <option value="{{ $libro->id }}">{{ $libro->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="cita_biblica" id="cita_biblica" placeholder="Ej: 5:23" class="mt-1 block w-1/2 rounded-md border-border bg-background px-3 py-2 text-foreground placeholder-muted-foreground focus:border-primary focus:ring-primary sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="turno_id" class="block text-sm font-medium text-foreground">Turno:</label>
                            <select name="turno_id" id="turno_id" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="">Seleccione un turno</option>
                                @foreach ($turnos as $turno)
                                    <option value="{{ $turno->id }}">{{ $turno->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="fecha_publicacion" class="block text-sm font-medium text-foreground">Fecha de Publicación:</label>
                            <input type="date" name="fecha_publicacion" id="fecha_publicacion" class="mt-1 block w-full rounded-md border-border bg-background px-3 py-2 text-foreground focus:border-primary focus:ring-primary sm:text-sm">
                            <div id="date-tooltip" class="hidden mt-2 text-sm text-blue-800 bg-blue-100 p-3 rounded-lg dark:bg-gray-700 dark:text-blue-400">
                                <i data-lucide="info" class="inline-block h-4 w-4 mr-1"></i>
                                <span id="date-tooltip-text"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" id="submit-button" class="inline-flex items-center justify-center whitespace-nowrap rounded-2xl text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-success text-success-foreground hover:bg-success-hover h-10 px-4 py-2 disabled:opacity-50" disabled>
                            <i data-lucide="save" class="h-4 w-4 mr-2"></i>
                            Guardar
                        </button>
                        <a href="{{ route('admin.audios.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-2xl text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('audio-form');
            const submitButton = document.getElementById('submit-button');
            const tempFilePathInput = document.getElementById('temp-file-path');
            const duracionInput = document.getElementById('duracion');
            
            const dropArea = document.getElementById('drop-area');
            const fileInput = document.getElementById('archivo-input');
            const progressContainer = document.getElementById('progress-container');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            const fileNameDisplay = document.getElementById('file-name');
            const uploadStatus = document.getElementById('upload-status');

            const categoriaSelect = document.getElementById('categoria_id');
            const libroCitaGroup = document.getElementById('libro-cita-group');
            if (categoriaSelect) {
                function toggleLibroFields() {
                    const selectedOption = categoriaSelect.options[categoriaSelect.selectedIndex];
                    const isPredicaciones = selectedOption && selectedOption.text.toLowerCase() === 'predicaciones';
                    console.log('selectedOptionValue:', categoriaSelect.value);
                    // Nota: en create no pasamos $predicacionesCat desde el controlador.
                    // Evitamos referencia a variable inexistente.
                    // console.log('predicacionesCatId:', '{{ isset($predicacionesCat) ? $predicacionesCat->id : '' }}');
                    console.log('isPredicaciones:', isPredicaciones);
                    libroCitaGroup.classList.toggle('hidden', !isPredicaciones);
                    document.getElementById('libro_id').required = isPredicaciones;
                }
                categoriaSelect.addEventListener('change', toggleLibroFields);
                toggleLibroFields();
            }

            const dateInput = document.getElementById('fecha_publicacion');
            const dateTooltip = document.getElementById('date-tooltip');
            const dateTooltipText = document.getElementById('date-tooltip-text');
            if(dateInput) {
                dateInput.addEventListener('change', function() {
                    const date = this.value;
                    if (!date) { dateTooltip.classList.add('hidden'); return; }
                    fetch(`{{ route(auth()->user()->role . '.audios.checkDate') }}?date=${date}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.count > 0) {
                                dateTooltipText.textContent = `Nota: Ya existen ${data.count} audio(s) en esta fecha.`;
                                dateTooltip.classList.remove('hidden');
                            } else {
                                dateTooltip.classList.add('hidden');
                            }
                        });
                });
            }

            if(dropArea) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropArea.addEventListener(eventName, e => { e.preventDefault(); e.stopPropagation(); }, false);
                });
                ['dragenter', 'dragover'].forEach(eventName => dropArea.addEventListener(eventName, () => dropArea.classList.add('border-primary', 'bg-accent/20')));
                ['dragleave', 'drop'].forEach(eventName => dropArea.addEventListener(eventName, () => dropArea.classList.remove('border-primary', 'bg-accent/20')));
                dropArea.addEventListener('drop', e => handleFiles(e.dataTransfer.files));
            }
            fileInput.addEventListener('change', e => handleFiles(e.target.files));

            function handleFiles(files) {
                if (files.length === 0) return;
                const file = files[0];
                
                fileNameDisplay.textContent = file.name;
                progressContainer.classList.remove('hidden');
                uploadStatus.textContent = 'Subiendo...';
                uploadStatus.classList.remove('text-destructive', 'text-success');
                submitButton.disabled = true;

                uploadFile(file);
            }

            function uploadFile(file) {
                const url = '{{ route(auth()->user()->role . ".audios.uploadTemp") }}';
                const xhr = new XMLHttpRequest();
                const formData = new FormData();
                formData.append('archivo', file);

                xhr.open('POST', url, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                xhr.upload.addEventListener('progress', e => {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        progressBar.style.width = percent + '%';
                        progressText.textContent = percent + '%';
                    }
                });

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        uploadStatus.textContent = '¡Archivo subido! Metadatos aplicados.';
                        uploadStatus.classList.add('text-success');
                        tempFilePathInput.value = response.temp_file_path;
                        
                        populateForm(response.metadata);

                        submitButton.disabled = false;
                    } else if (xhr.readyState === 4) {
                        uploadStatus.textContent = `Error en la subida: ${xhr.statusText}`;
                        uploadStatus.classList.add('text-destructive');
                        submitButton.disabled = true;
                    }
                };
                xhr.send(formData);
            }

            function populateForm(metadata) {
                if (!metadata) return;

                if (metadata.titulo) document.getElementById('titulo').value = metadata.titulo;
                if (metadata.fecha_publicacion) document.getElementById('fecha_publicacion').value = metadata.fecha_publicacion;
                if (metadata.duracion) duracionInput.value = metadata.duracion;

                selectOptionByText(document.getElementById('autor_id'), metadata.artista);
                selectOptionByText(document.getElementById('serie_id'), metadata.serie);
                selectOptionByText(document.getElementById('categoria_id'), metadata.categoria);
                selectOptionByText(document.getElementById('turno_id'), metadata.turno);

                // NEW: Use the pre-processed book and citation from the backend
                if (metadata.libro_nombre) {
                    selectOptionByText(document.getElementById('libro_id'), metadata.libro_nombre);
                }
                if (metadata.cita_biblica) {
                    document.getElementById('cita_biblica').value = metadata.cita_biblica;
                }

                const newAuthorMessage = document.getElementById('new-author-message');
                const newSeriesMessage = document.getElementById('new-series-message');

                if (metadata.new_author_name) {
                    newAuthorMessage.textContent = `"${metadata.new_author_name}" no existe y se creará automáticamente.`;
                    newAuthorMessage.classList.remove('hidden');
                    const newAuthorInput = document.createElement('input');
                    newAuthorInput.type = 'hidden';
                    newAuthorInput.name = 'new_author_name';
                    newAuthorInput.value = metadata.new_author_name;
                    form.appendChild(newAuthorInput);
                } else {
                    newAuthorMessage.classList.add('hidden');
                    const existingNewAuthorInput = form.querySelector('input[name="new_author_name"]');
                    if (existingNewAuthorInput) {
                        existingNewAuthorInput.remove();
                    }
                }

                if (metadata.new_series_name) {
                    newSeriesMessage.textContent = `"${metadata.new_series_name}" no existe y se creará automáticamente.`;
                    newSeriesMessage.classList.remove('hidden');
                    const newSeriesInput = document.createElement('input');
                    newSeriesInput.type = 'hidden';
                    newSeriesInput.name = 'new_series_name';
                    newSeriesInput.value = metadata.new_series_name;
                    form.appendChild(newSeriesInput);
                } else {
                    newSeriesMessage.classList.add('hidden');
                    const existingNewSeriesInput = form.querySelector('input[name="new_series_name"]');
                    if (existingNewSeriesInput) {
                        existingNewSeriesInput.remove();
                    }
                }

                document.getElementById('categoria_id').dispatchEvent(new Event('change'));
                document.getElementById('fecha_publicacion').dispatchEvent(new Event('change'));
            }

            function selectOptionByText(selectElement, text) {
                if (!text || !selectElement) return;
                const lowerText = text.toLowerCase();
                for (let i = 0; i < selectElement.options.length; i++) {
                    if (selectElement.options[i].text.toLowerCase() === lowerText) {
                        selectElement.value = selectElement.options[i].value;
                        return;
                    }
                }
            }
        });
    </script>
@endsection
