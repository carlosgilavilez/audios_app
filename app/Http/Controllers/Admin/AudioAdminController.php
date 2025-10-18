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
use getID3;

class AudioAdminController extends Controller
{
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
        $metadata['titulo'] = $raw_metadata['title'][0] ?? null;
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

        $rolePrefix = auth()->check() ? auth()->user()->role : 'admin';
        return redirect()->route($rolePrefix . '.audios.index')->with('ok', 'Audio subido y procesado correctamente.');
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

        if ($data['estado'] === 'Publicado') {
            $query = Audio::where('id', '!=', $audio->id)
                ->where('estado', 'Publicado')
                ->whereRaw('LOWER(titulo) = ?', [strtolower($data['titulo'])])
                ->where('autor_id', $data['autor_id'])
                ->where('fecha_publicacion', $data['fecha_publicacion']);

            $existingAudio = $query->first();

            if ($existingAudio) {
                $matchingFields = ['título', 'autor', 'fecha de publicación'];
                
                if (($data['serie_id'] ?? null) !== null && $existingAudio->serie_id == ($data['serie_id'] ?? null)) {
                    $matchingFields[] = 'serie';
                }
                if (($data['categoria_id'] ?? null) !== null && $existingAudio->categoria_id == ($data['categoria_id'] ?? null)) {
                    $matchingFields[] = 'categoría';
                }
                if (($data['libro_id'] ?? null) !== null && $existingAudio->libro_id == ($data['libro_id'] ?? null)) {
                    $matchingFields[] = 'libro';
                }
                if (($data['turno_id'] ?? null) !== null && $existingAudio->turno_id == ($data['turno_id'] ?? null)) {
                    $matchingFields[] = 'turno';
                }
                if (($data['cita_biblica'] ?? null) !== null && trim($data['cita_biblica']) !== '' && $existingAudio->cita_biblica == ($data['cita_biblica'] ?? null)) {
                    $matchingFields[] = 'cita bíblica';
                }

                $errorMessage = 'No se puede publicar el audio porque ya existe uno con el mismo ' . implode(', ', $matchingFields) . '.';
                
                return back()->withErrors(['estado' => $errorMessage])->withInput();
            }
        }

        $audio->update($data);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'entity_type' => 'Audio',
            'entity_id' => $audio->id,
        ]);

        $rolePrefix = auth()->check() ? auth()->user()->role : 'admin';
        return redirect()->route($rolePrefix . '.audios.index')->with('ok', 'Audio actualizado correctamente.');
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
}
