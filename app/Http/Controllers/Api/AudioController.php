<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AudioResource;
use App\Models\Audio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AudioController extends Controller
{
    /**
     * GET /api/audios
     * Lista pública de audios en estado Normal.
     * Filtros opcionales: q (texto en título), autor_id, serie_id, categoria_id, libro_id, turno_id, year
     */
    public function index(Request $request)
    {
        $q = Audio::query()->where('estado', 'Normal')
            ->with(['autor','serie','categoria','libro','turno']);

        if ($search = $request->string('q')->toString()) {
            $q->where('titulo', 'like', '%' . $search . '%');
        }

        foreach (['autor_id','serie_id','categoria_id','libro_id','turno_id'] as $fk) {
            if ($request->filled($fk)) {
                $q->where($fk, $request->integer($fk));
            }
        }

        if ($year = $request->integer('year')) {
            $q->whereYear('fecha_publicacion', $year);
        }

        $q->orderByDesc('fecha_publicacion')->orderByDesc('id');

        return AudioResource::collection($q->paginate(20));
    }

    /**
     * POST /api/audios
     * Crea un audio (pensado para admin/editor).
     * Nota: la subida real de archivos se hará en otra ruta; aquí guardamos metadatos.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => ['required','string','max:255'],
            'descripcion' => ['nullable','string'],
            'archivo' => ['required','string','max:255'], // ruta en storage
            'estado' => ['required', Rule::in(['Normal','Pendiente','Revisar','Oculto'])],
            'fecha_publicacion' => ['nullable','date'],

            'autor_id' => ['required','exists:autores,id'],
            'serie_id' => ['nullable','exists:series,id'],
            'categoria_id' => ['required','exists:categorias,id'],
            'libro_id' => ['nullable','exists:libros,id'],
            'turno_id' => ['nullable','exists:turnos,id'],
            'tag_ids' => ['array'],
            'tag_ids.*' => ['integer','exists:tags,id'],
        ]);

        // Validación sencilla anti-duplicados por (titulo, fecha_publicacion, autor_id)
        $exists = Audio::query()
            ->where('titulo', $data['titulo'])
            ->when(isset($data['fecha_publicacion']), fn($q) => $q->whereDate('fecha_publicacion', $data['fecha_publicacion']))
            ->where('autor_id', $data['autor_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Ya existe un audio con el mismo título/autor/fecha.'
            ], 422);
        }

        $tags = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $audio = Audio::create($data);
        if (!empty($tags)) {
            $audio->tags()->sync($tags);
        }

        return new AudioResource($audio->load(['autor','serie','categoria','libro','turno','tags']));
    }
}
