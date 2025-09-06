@extends('layouts.dashboard')

@section('title', 'Subir Nuevo Audio')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-foreground">Subir Nuevo Audio</h1>
            <a href="{{ url('/audios') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                Cancelar
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Audio Upload Section -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-lg font-medium">Archivo de Audio</h3>
                </div>
                <div class="p-6 pt-0">
                    <div
                        class="border-2 border-dashed rounded-lg p-8 text-center transition-colors border-border hover:border-success/50"
                        {{-- These events require JavaScript for drag & drop functionality --}}
                        {{-- onDragEnter="handleDrag(event)" onDragLeave="handleDrag(event)" onDragOver="handleDrag(event)" onDrop="handleDrop(event)" --}}
                    >
                        <span class="mx-auto h-12 w-12 text-muted-foreground mb-4 block">⬆️</span>
                        <p class="text-lg font-medium mb-2">Arrastra tu archivo aquí</p>
                        <p class="text-muted-foreground mb-4">
                            o haz clic para seleccionar
                        </p>
                        <input
                            type="file"
                            accept="audio/*"
                            {{-- onChange="handleFileInput(event)" --}}
                            class="hidden"
                            id="audio-upload"
                        />
                        <button
                            type="button"
                            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2"
                            onclick="document.getElementById('audio-upload').click()"
                        >
                            Seleccionar Archivo
                        </button>
                        <p class="text-xs text-muted-foreground mt-2">
                            Formatos soportados: MP3, WAV, AAC (máx. 50MB)
                        </p>
                    </div>
                    <div id="upload-progress-container" class="mt-4 hidden">
                        <div class="flex justify-between text-sm mb-1">
                            <span id="upload-file-name"></span>
                            <span id="upload-progress-text">0%</span>
                        </div>
                        <div class="relative h-2 w-full overflow-hidden rounded-full bg-primary/20">
                            <div id="upload-progress-bar" class="h-full w-0 flex-1 bg-primary transition-all"></div>
                        </div>
                    </div>
                    {{-- Placeholder for uploaded file display --}}
                    {{-- <div class="border border-border rounded-lg p-4 mt-4 hidden" id="uploaded-file-display">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="h-10 w-10 text-success">🎵</span>
                                <div>
                                    <p class="font-medium" id="uploaded-file-name"></p>
                                    <p class="text-sm text-muted-foreground" id="uploaded-file-size"></p>
                                </div>
                            </div>
                            <button type="button" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-8 w-8 p-0 text-destructive hover:text-destructive" onclick="removeFile()">
                                ❌
                            </button>
                        </div>
                    </div> --}}
                </div>
            </div>

            <!-- Audio Information Form -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-lg font-medium">Información del Audio</h3>
                </div>
                <div class="p-6 pt-0 space-y-6">
                    <div class="space-y-2">
                        <label for="titulo" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Título *</label>
                        <input type="text" id="titulo" name="titulo" placeholder="Ej: Meditación Matinal" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" />
                    </div>

                    <div class="space-y-2">
                        <label for="description" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Descripción</label>
                        <textarea id="description" placeholder="Describe brevemente el contenido del audio..." rows="3" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Autor *</label>
                            <select class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                <option value="">Seleccionar autor</option>
                                <option value="juan-perez">Juan Pérez</option>
                                <option value="maria-garcia">María García</option>
                                <option value="carlos-ruiz">Carlos Ruiz</option>
                                <option value="ana-lopez">Ana López</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Serie *</label>
                            <select class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                <option value="">Seleccionar serie</option>
                                <option value="meditacion-principiantes">Meditación para Principiantes</option>
                                <option value="mindfulness-diario">Mindfulness Diario</option>
                                <option value="relajacion-profunda">Relajación Profunda</option>
                                <option value="gestion-estres">Gestión del Estrés</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Categoría *</label>
                        <select name="categoria_id" id="categoria_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                            <option value="">Seleccionar categoría</option>
                            <option value="relajacion">Relajación</option>
                            <option value="sueno">Sueño</option>
                            <option value="mindfulness">Mindfulness</option>
                            <option value="bienestar">Bienestar</option>
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="fecha_publicacion" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Fecha de Publicación</label>
                            <input type="date" name="fecha_publicacion" id="fecha_publicacion" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Estado</label>
                            <select class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                <option value="normal">Normal</option>
                                <option value="pendiente">Pendiente</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Queue (Static Placeholder) -->
            {{-- This section's dynamic behavior (adding/removing files, progress updates) requires JavaScript --}}
            {{-- For a static Blade conversion, we can show an example or keep it hidden until JS is added --}}
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-lg font-medium">Cola de Archivos (Ejemplo Estático)</h3>
                </div>
                <div class="p-6 pt-0">
                    <div class="space-y-4">
                        <div class="border border-border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-3">
                                    <span class="h-8 w-8 text-primary">🎵</span>
                                    <div>
                                        <p class="font-medium">ejemplo_audio.mp3</p>
                                        <p class="text-sm text-muted-foreground">5.23 MB</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="h-5 w-5 text-success">✅</span> {{-- Completed Icon --}}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="flex justify-between text-sm">
                                    <span>Progreso</span>
                                    <span>100%</span>
                                </div>
                                <div class="relative h-2 w-full overflow-hidden rounded-full bg-primary/20">
                                    <div class="h-full w-full flex-1 bg-primary transition-all" style="transform: translateX(-0%);"></div>
                                </div>
                            </div>
                        </div>
                        <div class="border border-border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-3">
                                    <span class="h-8 w-8 text-primary">🎵</span>
                                    <div>
                                        <p class="font-medium">otro_audio_pendiente.wav</p>
                                        <p class="text-sm text-muted-foreground">12.80 MB</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="h-5 w-5 text-destructive">⚠️</span> {{-- Error/Pending Icon --}}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="flex justify-between text-sm">
                                    <span>Progreso</span>
                                    <span>50%</span>
                                </div>
                                <div class="relative h-2 w-full overflow-hidden rounded-full bg-primary/20">
                                    <div class="h-full w-1/2 flex-1 bg-primary transition-all" style="transform: translateX(-0%);"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ url('/audios') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                Cancelar
            </a>
            <button type="submit" id="save-audio-button" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-success text-success-foreground hover:bg-success-hover h-10 px-4 py-2 min-w-[120px]">
                Guardar Audio
            </button>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const audioUploadInput = document.getElementById('audio-upload');
        const saveAudioButton = document.getElementById('save-audio-button');
        const uploadProgressContainer = document.getElementById('upload-progress-container');
        const uploadFileName = document.getElementById('upload-file-name');
        const uploadProgressBar = document.getElementById('upload-progress-bar');
        const uploadProgressText = document.getElementById('upload-progress-text');

        // Initially disable the save button
        saveAudioButton.disabled = true;
        saveAudioButton.classList.add('disabled:pointer-events-none', 'disabled:opacity-50');

        audioUploadInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                uploadFileName.textContent = file.name;
                uploadProgressContainer.classList.remove('hidden');
                uploadProgressBar.style.width = '0%';
                uploadProgressText.textContent = '0%';
                saveAudioButton.disabled = true; // Disable button during new upload
                saveAudioButton.classList.add('disabled:pointer-events-none', 'disabled:opacity-50');

                const formData = new FormData();
                formData.append('audio', file);

                // Assuming a Laravel route for audio upload
                const uploadUrl = '{{ route("admin.audios.store") }}'; // This route needs to exist and handle the upload

                const xhr = new XMLHttpRequest();

                xhr.open('POST', uploadUrl, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                xhr.upload.onprogress = function (event) {
                    if (event.lengthComputable) {
                        const percentComplete = (event.loaded / event.total) * 100;
                        uploadProgressBar.style.width = percentComplete + '%';
                        uploadProgressText.textContent = Math.round(percentComplete) + '%';
                    }
                };

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        // Upload successful
                        uploadProgressText.textContent = '100%';
                        uploadProgressBar.style.width = '100%';
                        saveAudioButton.disabled = false; // Enable save button
                        saveAudioButton.classList.remove('disabled:pointer-events-none', 'disabled:opacity-50');
                        alert('Audio subido correctamente. Ahora puedes guardar la información.');
                    } else {
                        // Handle upload error
                        uploadProgressContainer.classList.add('hidden');
                        alert('Error al subir el audio. Por favor, inténtalo de nuevo.');
                        console.error('Upload failed:', xhr.responseText);
                    }
                };

                xhr.onerror = function () {
                    uploadProgressContainer.classList.add('hidden');
                    alert('Error de red al subir el audio. Por favor, verifica tu conexión.');
                    console.error('Network error during upload.');
                };

                xhr.send(formData);
            }
        const categoriaSelect = document.getElementById('categoria_id');
        const libroCitaGroup = document.getElementById('libro-cita-group');
        // This needs to be passed from the controller
        const predicacionesCatId = '{{ $predicacionesCat->id ?? null }}';

        function toggleLibroFields() {
            const selectedOptionValue = categoriaSelect.value;
            const isPredicaciones = selectedOptionValue && predicacionesCatId && selectedOptionValue == predicacionesCatId;
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