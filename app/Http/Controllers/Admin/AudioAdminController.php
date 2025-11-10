<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audio;
use App\Models\Autor;
use App\Models\Serie;
use App\Models\Categoria;
use App\Models\Libro;
use App\Models\Turno;
use App\Models\Libro as LibroModel;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // <-- IMPORT LOG FACADE
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use getID3;

class AudioAdminController extends Controller
{
    private const BULK_UPLOAD_MAX_FILES = 50;

    private function ensureDefaultCategories(): void
    {
        try {
            foreach (['Predicaciones', 'Temas Esenciales', 'Conferencias'] as $nombre) {
                \App\Models\Categoria::firstOrCreate(['nombre' => $nombre]);
            }
        } catch (\Throwable $e) {
            // Si la BD no está disponible, no bloquear la vista.
            \Log::warning('No se pudieron asegurar categorías por defecto: '.$e->getMessage());
        }
    }

    private function ensureDefaultTurnos(): void
    {
        try {
            // Eliminar entrada obsoleta si existe
            \App\Models\Turno::where('nombre', 'Noche')->delete();
            // Asegurar valores base
            foreach (['Mañana', 'Tarde'] as $nombre) {
                \App\Models\Turno::firstOrCreate(['nombre' => $nombre]);
            }
        } catch (\Throwable $e) {
            \Log::warning('No se pudieron asegurar turnos por defecto: '.$e->getMessage());
        }
    }

    private function ensureDefaultLibros(): void
    {
        try {
            // Si hay menos de 60, resembrar usando el seeder (idempotente)
            if (LibroModel::count() < 60) {
                (new \Database\Seeders\LibroSeeder())->run();
            }
        } catch (\Throwable $e) {
            \Log::warning('No se pudieron asegurar libros por defecto: '.$e->getMessage());
        }
    }

    // ... other methods ...
    public function index(Request $request)
    {
        $query = Audio::with(['autor', 'serie', 'categoria', 'libro', 'turno']);

        // Búsqueda de texto simple (título, autor, serie, cita)
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('cita_biblica', 'like', "%{$search}%")
                  ->orWhereHas('autor', function ($qa) use ($search) {
                      $qa->where('nombre', 'like', "%{$search}%");
                  })
                  ->orWhereHas('serie', function ($qs) use ($search) {
                      $qs->where('nombre', 'like', "%{$search}%");
                  })
                  ->orWhereHas('turno', function ($qt) use ($search) {
                      $qt->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro opcional por estado
        if ($estado = $request->string('estado')->toString()) {
            if (in_array($estado, ['Pendiente', 'Publicado'])) {
                $query->where('estado', $estado);
            }
        }

        // Filtro por autor (cuando viene desde listado de autores)
        if ($autorId = $request->input('autor_id')) {
            $query->where('autor_id', $autorId);
        }

        // Filtro por serie (cuando viene desde listado de series)
        if ($serieId = $request->input('serie_id')) {
            $query->where('serie_id', $serieId);
        }

        // Filtro por categoría
        if ($categoria_id = $request->input('categoria_id')) {
            $query->where('categoria_id', $categoria_id);
        }

        // Filtro por año
        if ($year = $request->input('year')) {
            $query->whereYear('fecha_publicacion', $year);
        }

        $perPage = $request->input('per_page', 25);
        $audios = $query->latest()->paginate($perPage)->withQueryString();
        $categorias = Categoria::all();

        $years = Audio::query()
            ->select(DB::raw('YEAR(fecha_publicacion) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter();

        return view('admin.audios.index', [
            'audios' => $audios,
            'categorias' => $categorias,
            'years' => $years,
            'search' => $search ?? '',
            'estado' => $estado ?? '',
            'bulkUploadLimit' => $this->getBulkUploadLimit(),
        ]);
    }

    public function create()
    {
        $this->ensureDefaultCategories();
        $this->ensureDefaultTurnos();
        $this->ensureDefaultLibros();
        $autores = Autor::all();
        $series = Serie::all();
        $categorias = Categoria::all();
        $libros = Libro::all();
        $turnos = Turno::all();
        return view('admin.audios.create', compact('autores', 'series', 'categorias', 'libros', 'turnos'));
    }

    public function uploadTemp(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:mp3,wav,aac|max:51200', // 50MB
        ]);

        $uploadedFile = $request->file('archivo');

        // --- Metadata Extraction & LOGGING ---
        $getID3 = new getID3;
        $fileInfo = $getID3->analyze($uploadedFile->getRealPath());

        // Store the file explicitly on the 'local' disk to avoid env-dependent defaults
        $tempPath = $uploadedFile->store('temp', 'local');
        
        // LOG THE ENTIRE RAW FILEINFO ARRAY
        Log::info('getID3 Raw File Info:', $fileInfo);

        $getID3->CopyTagsToComments($fileInfo);
        $raw_metadata = $fileInfo['comments'] ?? [];

        // LOG THE COPIED COMMENTS
        Log::info('getID3 Comments Array:', $raw_metadata);

        $metadata = [];
        $metadata['duracion'] = $fileInfo['playtime_string'] ?? null;
        $metadata['titulo'] = $this->sanitizeTitulo($raw_metadata['title'][0] ?? null);
        $metadata['artista'] = $raw_metadata['artist'][0] ?? null;
        $metadata['serie'] = $raw_metadata['subtitle'][0] ?? null;
        $metadata['categoria'] = $raw_metadata['genre'][0] ?? null;
        $metadata['turno'] = $raw_metadata['conductor'][0] ?? null;
        
        // Check for existing Author
        $authorName = $raw_metadata['artist'][0] ?? null;
        $metadata['new_author_name'] = null;
        if ($authorName) {
            $author = Autor::where('nombre', $authorName)->first();
            if (!$author) {
                $metadata['new_author_name'] = $authorName;
            }
        }

        // Check for existing Series
        $seriesName = $raw_metadata['subtitle'][0] ?? null;
        $metadata['new_series_name'] = null;
        if ($seriesName) {
            $series = Serie::where('nombre', $seriesName)->first();
            if (!$series) {
                $metadata['new_series_name'] = $seriesName;
            }
        }

        // --- Handle Date ---
        $dateTag = $raw_metadata['encoded_by'][0] ?? $raw_metadata['recording_time'][0] ?? $raw_metadata['year'][0] ?? null;
        $metadata['fecha_publicacion'] = null; // Default to null (User Option A)
        
        Log::info('Attempting to parse date from tag: ' . $dateTag); // Add logging

        if ($dateTag) {
            try {
                // First, try to parse the specific d/m/Y format
                $date = \DateTime::createFromFormat('d/m/Y', $dateTag);
                if ($date === false) {
                    // If that fails, fall back to the generic parser for other formats like Y-m-d
                    $date = new \DateTime($dateTag);
                }
                $metadata['fecha_publicacion'] = $date->format('Y-m-d');
            } catch (\Exception $e) {
                Log::error('Date parsing failed: ' . $e->getMessage());
                // If any parsing fails, it remains null
            }
        }

        // --- Handle Album -> Libro & Cita ---
        $albumString = $raw_metadata['album'][0] ?? null;
        $metadata['libro_nombre'] = null;
        $metadata['cita_biblica'] = null;

        if ($albumString && isset($metadata['categoria']) && strtolower($metadata['categoria']) === 'predicaciones') {
            $libros = Libro::all()->pluck('nombre')->toArray();
            // Sort by length descending to match longer names first (e.g. "1 Juan" before "Juan")
            usort($libros, function ($a, $b) {
                return strlen($b) - strlen($a);
            });

            $normalizedAlbum = trim(str_replace(['ª', 'º', '.'], '', $albumString));

            foreach ($libros as $libroNombre) {
                $normalizedLibro = trim(str_replace(['ª', 'º', '.'], '', $libroNombre));
                if (stripos($normalizedAlbum, $normalizedLibro) === 0) {
                    $metadata['libro_nombre'] = $libroNombre; // Use original book name
                    $cita = trim(substr($albumString, strlen($libroNombre)));
                    $metadata['cita_biblica'] = $cita;
                    break; // Stop after first match
                }
            }
        }

        // LOG THE FINAL METADATA WE ARE SENDING
        Log::info('Final Metadata Sent to Frontend:', $metadata);

        return response()->json([
            'temp_file_path' => $tempPath,
            'metadata' => $metadata
        ]);
    }

    public function store(Request $request)
    {
        // ... store method is fine for now ...
        $predicacionesCat = Categoria::where('nombre', 'Predicaciones')->first();
        $predicacionesCatId = $predicacionesCat ? $predicacionesCat->id : null;

        $libroRule = 'nullable|exists:libros,id';
        if ($predicacionesCatId) {
            $libroRule = 'required_if:categoria_id,' . $predicacionesCatId . '|' . $libroRule;
        }

        $data = $request->validate([
            'temp_file_path' => 'required|string',
            'titulo' => 'nullable|string|max:255',
            'autor_id' => 'nullable|exists:autores,id',
            'serie_id' => 'nullable|exists:series,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'libro_id' => $libroRule,
            'turno_id' => 'nullable|exists:turnos,id',
            'cita_biblica' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'fecha_publicacion' => 'nullable|date',
            'duracion' => 'nullable|string|max:20',
            'new_author_name' => 'nullable|string|max:255',
            'new_series_name' => 'nullable|string|max:255',
        ]);

        if (array_key_exists('titulo', $data)) {
            $data['titulo'] = $this->sanitizeTitulo($data['titulo']);
        }

        $tempPath = $data['temp_file_path'];
        if (!Storage::disk('local')->exists($tempPath)) {
            return back()->withErrors(['archivo' => 'El archivo subido no se ha encontrado. Por favor, súbelo de nuevo.'])->withInput();
        }

        $filename = basename($tempPath);
        $finalRelativePath = 'audios/' . $filename;

        // Asegurar carpeta destino y mover archivo
        Storage::disk('public')->makeDirectory('audios');
        try {
            $fileContent = Storage::disk('local')->get($tempPath);
            $ok = Storage::disk('public')->put($finalRelativePath, $fileContent);
            if (!$ok) {
                return back()->withErrors(['archivo' => 'No se pudo guardar el archivo en el almacenamiento público. Verifique permisos de storage/public.'])->withInput();
            }
        } catch (\Throwable $e) {
            \Log::error('Fallo al mover audio desde temp a public: '.$e->getMessage(), ['temp' => $tempPath, 'dest' => $finalRelativePath]);
            return back()->withErrors(['archivo' => 'Error al mover el archivo. Intente nuevamente.'])->withInput();
        } finally {
            // Borrar el temporal si existe
            try { Storage::disk('local')->delete($tempPath); } catch (\Throwable $e) { /* ignore */ }
        }
        
        if ($request->input('new_author_name')) {
            $autor = Autor::create(['nombre' => $request->input('new_author_name')]);
            $data['autor_id'] = $autor->id;
        }

        if ($request->input('new_series_name')) {
            $serie = Serie::create(['nombre' => $request->input('new_series_name')]);
            $data['serie_id'] = $serie->id;
        }

        $audio = Audio::create([
            'titulo' => $data['titulo'] ?? null,
            'archivo' => $finalRelativePath,
            'autor_id' => $data['autor_id'] ?? null,
            'serie_id' => $data['serie_id'] ?? null,
            'categoria_id' => $data['categoria_id'] ?? null,
            'libro_id' => $data['libro_id'] ?? null,
            'turno_id' => $data['turno_id'] ?? null,
            'cita_biblica' => $data['cita_biblica'] ?? null,
            'duracion' => $data['duracion'] ?? null,
            'descripcion' => $data['description'] ?? null,
            'fecha_publicacion' => $data['fecha_publicacion'] ?? null,
            'estado' => $this->determinarEstadoConDatos($data),
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'entity_type' => 'Audio',
            'entity_id' => $audio->id,
        ]);

        Cache::forget('public.audios.years');

        $rolePrefix = auth()->check() ? auth()->user()->role : 'admin';
        return redirect()->route($rolePrefix . '.audios.index')->with('ok', 'Audio subido y procesado correctamente.');
    }

    public function bulkUpload(Request $request)
    {
        $maxFiles = $this->getBulkUploadLimit();

        Validator::make(
            $request->all(),
            [
                'audios' => ['required', 'array', 'min:1', 'max:' . $maxFiles],
                'audios.*' => 'file|mimes:mp3,wav,aac|max:51200',
            ],
            [
                'audios.max' => "Solo puedes subir {$maxFiles} archivos por tanda.",
            ]
        )->validate();

        $files = $request->file('audios', []);

        if (count($files) > $maxFiles) {
            throw ValidationException::withMessages([
                'audios' => "Solo puedes subir {$maxFiles} archivos por tanda.",
            ]);
        }

        $created = [];
        $errors = [];

        foreach ($files as $file) {
            try {
                $audio = $this->createAudioFromUploadedFile($file);
                $created[] = [
                    'id' => $audio->id,
                    'titulo' => $audio->titulo,
                    'autor' => $audio->autor?->nombre,
                    'estado' => $audio->estado,
                    'fecha_publicacion' => $audio->fecha_publicacion,
                ];
            } catch (\Throwable $e) {
                Log::error('Bulk audio upload failed', [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);

                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'message' => $e->getMessage(),
                ];
            }
        }

        if (!empty($created)) {
            Cache::forget('public.audios.years');
        }

        return response()->json([
            'created' => $created,
            'errors' => $errors,
        ]);
    }

    // ... other methods ...
    public function edit(Audio $audio)
    {
        $this->ensureDefaultCategories();
        $this->ensureDefaultTurnos();
        $this->ensureDefaultLibros();
        $autores = Autor::all();
        $series = Serie::all();
        $categorias = Categoria::all();
        $libros = Libro::all();
        $turnos = Turno::all();
        $predicacionesCat = Categoria::where('nombre', 'Predicaciones')->first();
        \Log::info('predicacionesCatId from controller: ' . ($predicacionesCat ? $predicacionesCat->id : 'null'));

        return view('admin.audios.edit', compact('audio', 'autores', 'series', 'categorias', 'libros', 'turnos', 'predicacionesCat'));
    }

    public function update(Request $request, Audio $audio)
    {
        $predicacionesCat = Categoria::where('nombre', 'Predicaciones')->first();
        $predicacionesCatId = $predicacionesCat ? $predicacionesCat->id : null;

        $data = $request->validate([
            'titulo' => 'required_if:estado,Publicado|nullable|string|max:255',
            'autor_id' => 'required_if:estado,Publicado|nullable|exists:autores,id',
            'serie_id' => 'nullable|exists:series,id',
            'categoria_id' => 'required_if:estado,Publicado|nullable|exists:categorias,id',
            'libro_id' => 'required_if:categoria_id,' . $predicacionesCatId . '|nullable|exists:libros,id',
            'turno_id' => 'nullable|exists:turnos,id',
            'cita_biblica' => 'nullable|string|max:100',
            'fecha_publicacion' => 'required_if:estado,Publicado|nullable|date',
            'duracion' => 'nullable|string|max:20',
            'estado' => 'required|string|in:Pendiente,Publicado',
            'new_author_name' => 'nullable|string|max:255',
            'new_series_name' => 'nullable|string|max:255',
        ]);

        if (array_key_exists('titulo', $data)) {
            $data['titulo'] = $this->sanitizeTitulo($data['titulo']);
        }

        $publishCheckPayload = [
            'estado' => $data['estado'],
            'titulo' => $data['titulo'] ?? $audio->titulo,
            'autor_id' => $data['autor_id'] ?? $audio->autor_id,
            'fecha_publicacion' => $data['fecha_publicacion'] ?? $audio->fecha_publicacion,
            'serie_id' => $data['serie_id'] ?? $audio->serie_id,
            'categoria_id' => $data['categoria_id'] ?? $audio->categoria_id,
            'libro_id' => $data['libro_id'] ?? $audio->libro_id,
            'turno_id' => $data['turno_id'] ?? $audio->turno_id,
            'cita_biblica' => $data['cita_biblica'] ?? $audio->cita_biblica,
        ];

        try {
            $this->guardAgainstDuplicatePublished($audio, $publishCheckPayload);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $audio->update($data);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'entity_type' => 'Audio',
            'entity_id' => $audio->id,
        ]);

        Cache::forget('public.audios.years');

        $rolePrefix = auth()->check() ? auth()->user()->role : 'admin';
        return redirect()->route($rolePrefix . '.audios.index')->with('ok', 'Audio actualizado correctamente.');
    }

    public function updateEstado(Request $request, Audio $audio)
    {
        $data = $request->validate([
            'estado' => 'required|string|in:Pendiente,Publicado',
        ]);

        $payload = [
            'estado' => $data['estado'],
            'titulo' => $audio->titulo,
            'autor_id' => $audio->autor_id,
            'fecha_publicacion' => $audio->fecha_publicacion,
            'serie_id' => $audio->serie_id,
            'categoria_id' => $audio->categoria_id,
            'libro_id' => $audio->libro_id,
            'turno_id' => $audio->turno_id,
            'cita_biblica' => $audio->cita_biblica,
        ];

        try {
            $this->guardAgainstDuplicatePublished($audio, $payload);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        $audio->estado = $data['estado'];
        $audio->save();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'entity_type' => 'Audio',
            'entity_id' => $audio->id,
        ]);

        Cache::forget('public.audios.years');

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'audio' => [
                'id' => $audio->id,
                'estado' => $audio->estado,
            ],
        ]);
    }

    public function bulkAction(Request $request)
    {
        $maxSelection = 50;

        $data = $request->validate([
            'action' => 'required|string|in:delete,pendiente,publicar',
            'audio_ids' => 'required|array',
            'audio_ids.*' => 'integer|exists:audios,id',
        ], [
            'audio_ids.required' => 'Selecciona al menos un audio.',
            'audio_ids.*.exists' => 'Alguno de los audios seleccionados no existe.',
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['audio_ids'])));
        if (!count($ids)) {
            return redirect()->back()->with('error', 'Selecciona al menos un audio.');
        }
        if (count($ids) > $maxSelection) {
            return redirect()->back()->with('error', "Solo puedes seleccionar hasta {$maxSelection} audios por acci\u00f3n.");
        }

        $audios = Audio::whereIn('id', $ids)->get();
        if ($audios->isEmpty()) {
            return redirect()->back()->with('error', 'No se encontraron los audios seleccionados.');
        }

        $affected = 0;
        $action = $data['action'];

        if ($action === 'delete') {
            foreach ($audios as $audio) {
                if ($audio->archivo) {
                    $relativePath = str_replace('/storage/', '', $audio->archivo);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
                $audio->delete();
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'deleted',
                    'entity_type' => 'Audio',
                    'entity_id' => $audio->id,
                ]);
                $affected++;
            }

            Cache::forget('public.audios.years');

            return redirect()->back()->with(
                'success',
                $affected === 1 ? 'Se elimin\u00f3 1 audio.' : "Se eliminaron {$affected} audios."
            );
        }

        if ($action === 'pendiente') {
            foreach ($audios as $audio) {
                if ($audio->estado !== 'Pendiente') {
                    $audio->estado = 'Pendiente';
                    $audio->save();
                    ActivityLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'updated',
                        'entity_type' => 'Audio',
                        'entity_id' => $audio->id,
                    ]);
                    $affected++;
                }
            }

            Cache::forget('public.audios.years');

            if ($affected === 0) {
                return redirect()->back()->with('success', 'Los audios seleccionados ya estaban en estado Pendiente.');
            }

            return redirect()->back()->with(
                'success',
                $affected === 1 ? 'Se actualiz\u00f3 1 audio a estado Pendiente.' : "Se actualizaron {$affected} audios a estado Pendiente."
            );
        }

        if ($action === 'publicar') {
            $publishable = [];
            $publishErrors = [];

            foreach ($audios as $audio) {
                $issues = [];

                if ($audio->estado === 'Publicado') {
                    $issues[] = 'ya est\u00e1 publicado';
                }

                $missing = [];
                if (!$audio->titulo) {
                    $missing[] = 't\u00edtulo';
                }
                if (!$audio->autor_id) {
                    $missing[] = 'autor';
                }
                if (!$audio->fecha_publicacion) {
                    $missing[] = 'fecha de publicaci\u00f3n';
                }

                foreach ($missing as $label) {
                    $issues[] = "falta {$label}";
                }

                if (!$issues) {
                    try {
                        $this->guardAgainstDuplicatePublished($audio, [
                            'estado' => 'Publicado',
                            'titulo' => $audio->titulo,
                            'autor_id' => $audio->autor_id,
                            'fecha_publicacion' => $audio->fecha_publicacion,
                        ]);
                    } catch (ValidationException $e) {
                        $errorsArray = $e->errors();
                        $issues[] = $errorsArray['estado'][0] ?? $e->getMessage() ?? 'No se puede publicar por duplicado.';
                    }
                }

                if ($issues) {
                    $publishErrors[] = $this->formatAudioLabel($audio) . ': ' . implode(', ', $issues);
                    continue;
                }

                $publishable[] = $audio;
            }

            if (!count($publishable)) {
                $message = 'No se pudo publicar ninguno de los audios seleccionados.';
                if ($publishErrors) {
                    $message .= ' ' . implode(' ', $publishErrors);
                }
                return redirect()->back()->with('error', $message);
            }

            $publishedCount = 0;
            foreach ($publishable as $audio) {
                if ($audio->estado !== 'Publicado') {
                    $audio->estado = 'Publicado';
                    $audio->save();
                    ActivityLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'updated',
                        'entity_type' => 'Audio',
                        'entity_id' => $audio->id,
                    ]);
                }
                $publishedCount++;
            }

            Cache::forget('public.audios.years');

            $successMessage = $publishedCount === 1
                ? 'Se public\u00f3 1 audio.'
                : "Se publicaron {$publishedCount} audios.";

            if ($publishErrors) {
                $successMessage .= ' No se pudieron publicar: ' . implode(' | ', $publishErrors);
            }

            return redirect()->back()->with('success', $successMessage);
        }
    }

    private function formatAudioLabel(Audio $audio): string
    {
        $title = $audio->titulo ? '«' . $audio->titulo . '»' : 'Audio sin t\u00edtulo';
        return $title . ' (#' . $audio->id . ')';
    }

    public function destroy(Audio $audio)
    {
        if ($audio->archivo && Storage::disk('public')->exists(str_replace('/storage/', '', $audio->archivo))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $audio->archivo));
        }
        $audio->delete();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'entity_type' => 'Audio',
            'entity_id' => $audio->id,
        ]);
        Cache::forget('public.audios.years');
        return redirect()->route('admin.audios.index')->with('success', 'Audio eliminado exitosamente.');
    }

    public function checkDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $count = Audio::where('fecha_publicacion', $request->input('date'))->count();

        return response()->json(['count' => $count]);
    }

    private function determinarEstadoConDatos(array $data)
    {
        return 'Pendiente';
    }

    private function guardAgainstDuplicatePublished(?Audio $currentAudio, array $data): void
    {
        if (($data['estado'] ?? null) !== 'Publicado') {
            return;
        }

        $titulo = $data['titulo'] ?? ($currentAudio?->titulo);
        $autorId = $data['autor_id'] ?? ($currentAudio?->autor_id);
        $fecha = $data['fecha_publicacion'] ?? ($currentAudio?->fecha_publicacion);

        if (!$titulo || !$autorId || !$fecha) {
            throw ValidationException::withMessages([
                'estado' => 'Para publicar debes definir titulo, autor y fecha de publicacion.',
            ]);
        }

        $query = Audio::query()
            ->where('estado', 'Publicado')
            ->whereRaw('LOWER(titulo) = ?', [Str::lower($titulo)])
            ->where('autor_id', $autorId)
            ->where('fecha_publicacion', $fecha);

        if ($currentAudio && $currentAudio->exists) {
            $query->where('id', '!=', $currentAudio->id);
        }

        $match = $query->first();

        if (!$match) {
            return;
        }

        $matchingFields = ['titulo', 'autor', 'fecha de publicacion'];

        $serieId = $data['serie_id'] ?? ($currentAudio?->serie_id);
        if ($serieId !== null && $match->serie_id == $serieId) {
            $matchingFields[] = 'serie';
        }

        $categoriaId = $data['categoria_id'] ?? ($currentAudio?->categoria_id);
        if ($categoriaId !== null && $match->categoria_id == $categoriaId) {
            $matchingFields[] = 'categoria';
        }

        $libroId = $data['libro_id'] ?? ($currentAudio?->libro_id);
        if ($libroId !== null && $match->libro_id == $libroId) {
            $matchingFields[] = 'libro';
        }

        $turnoId = $data['turno_id'] ?? ($currentAudio?->turno_id);
        if ($turnoId !== null && $match->turno_id == $turnoId) {
            $matchingFields[] = 'turno';
        }

        $cita = $data['cita_biblica'] ?? ($currentAudio?->cita_biblica);
        if ($cita !== null && trim($cita) !== '' && $match->cita_biblica === $cita) {
            $matchingFields[] = 'cita biblica';
        }

        throw ValidationException::withMessages([
            'estado' => 'No se puede publicar el audio porque ya existe uno con el mismo ' . implode(', ', $matchingFields) . '.',
        ]);
    }
    private function createAudioFromUploadedFile(UploadedFile $file): Audio
    {
        $metadata = $this->extractMetadataFromFile($file);
        $metadata['titulo'] = $this->sanitizeTitulo($metadata['titulo'] ?? null);

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'mp3');
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $baseName = $baseName ?: 'audio';
        $storedName = $baseName . '-' . Str::random(8) . '.' . $extension;
        $relativePath = 'audios/' . $storedName;

        Storage::disk('public')->makeDirectory('audios');
        Storage::disk('public')->putFileAs('audios', $file, $storedName);

        $categoriaId = $this->resolveModelIdByName(Categoria::class, $metadata['categoria'] ?? null);
        $serieId = $this->resolveModelIdByName(Serie::class, $metadata['serie'] ?? null);
        $turnoId = $this->resolveModelIdByName(Turno::class, $metadata['turno'] ?? null);
        $autorId = $this->resolveModelIdByName(Autor::class, $metadata['autor'] ?? null);
        $libroId = $this->resolveModelIdByName(Libro::class, $metadata['libro'] ?? null);

        $defaultTitulo = $this->sanitizeTitulo(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

        $audio = Audio::create([
            'titulo' => $metadata['titulo'] ?? $defaultTitulo,
            'archivo' => $relativePath,
            'autor_id' => $autorId,
            'serie_id' => $serieId,
            'categoria_id' => $categoriaId,
            'libro_id' => $libroId,
            'turno_id' => $turnoId,
            'cita_biblica' => $metadata['cita_biblica'] ?? null,
            'duracion' => $metadata['duracion'] ?? null,
            'descripcion' => null,
            'fecha_publicacion' => $metadata['fecha_publicacion'] ?? null,
            'estado' => 'Pendiente',
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'entity_type' => 'Audio',
            'entity_id' => $audio->id,
        ]);

        return $audio;
    }

    private function extractMetadataFromFile(UploadedFile $file): array
    {
        $metadata = [
            'titulo' => null,
            'autor' => null,
            'serie' => null,
            'categoria' => null,
            'turno' => null,
            'libro' => null,
            'cita_biblica' => null,
            'duracion' => null,
            'fecha_publicacion' => null,
        ];

        try {
            $getID3 = new getID3();
            $fileInfo = $getID3->analyze($file->getRealPath());
            $getID3->CopyTagsToComments($fileInfo);
            $comments = $fileInfo['comments'] ?? [];

            $metadata['duracion'] = $fileInfo['playtime_string'] ?? null;
            $metadata['titulo'] = $this->sanitizeTitulo($comments['title'][0] ?? null);
            $metadata['autor'] = $comments['artist'][0] ?? null;
            $metadata['serie'] = $comments['subtitle'][0] ?? null;
            $metadata['categoria'] = $comments['genre'][0] ?? null;
            $metadata['turno'] = $comments['conductor'][0] ?? null;

            $dateTag = $comments['encoded_by'][0] ?? $comments['recording_time'][0] ?? $comments['year'][0] ?? null;
            if ($dateTag) {
                try {
                    $date = \DateTime::createFromFormat('d/m/Y', $dateTag);
                    if ($date === false) {
                        $date = new \DateTime($dateTag);
                    }
                    $metadata['fecha_publicacion'] = $date->format('Y-m-d');
                } catch (\Throwable $e) {
                    Log::warning('Bulk upload date parsing failed: ' . $e->getMessage());
                }
            }

            $album = $comments['album'][0] ?? null;
            if ($album && isset($metadata['categoria']) && Str::lower($metadata['categoria']) === 'predicaciones') {
                $libros = Libro::all()->pluck('nombre')->toArray();
                usort($libros, function ($a, $b) {
                    return strlen($b) <=> strlen($a);
                });

                $normalizedAlbum = trim(str_replace(['�', '�', '.'], '', $album));
                foreach ($libros as $nombre) {
                    $normalizedLibro = trim(str_replace(['�', '�', '.'], '', $nombre));
                    if (stripos($normalizedAlbum, $normalizedLibro) === 0) {
                        $metadata['libro'] = $nombre;
                        $metadata['cita_biblica'] = trim(substr($album, strlen($nombre)));
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo leer metadata del archivo: ' . $e->getMessage());
        }

        return $metadata;
    }

    private function sanitizeTitulo(?string $titulo): ?string
    {
        if ($titulo === null) {
            return null;
        }

        $parts = explode('|', $titulo);
        $cleaned = trim($parts[0]);

        return $cleaned === '' ? null : $cleaned;
    }

    private function getBulkUploadLimit(): int
    {
        $serverLimit = (int) ini_get('max_file_uploads');
        if ($serverLimit <= 0) {
            $serverLimit = self::BULK_UPLOAD_MAX_FILES;
        }

        return min(self::BULK_UPLOAD_MAX_FILES, $serverLimit);
    }

    private function resolveModelIdByName(string $modelClass, ?string $name): ?int
    {
        if (!$name || !class_exists($modelClass)) {
            return null;
        }

        return $modelClass::whereRaw('LOWER(nombre) = ?', [Str::lower($name)])->value('id');
    }
}








