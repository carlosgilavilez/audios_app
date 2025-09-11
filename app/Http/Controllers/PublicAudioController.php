<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicAudioController extends Controller
{
    // 📌 Muestra la Biblioteca de audios
    public function index(Request $request)
    {
        $query = Audio::with(['autor', 'serie', 'categoria', 'libro', 'turno'])
            ->where('estado', 'Publicado');

        // Búsqueda general
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
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

        // Paginación
        $perPage = $request->input('per_page', 25);
        $audios = $query->latest('fecha_publicacion')->paginate($perPage);

        return view('public.audios', compact('audios'));
    }

    // 📌 Muestra la Vista pública general
    public function view()
    {
        return view('public.view');
    }

    public function playAudio(Audio $audio)
    {
        if (!Storage::disk('public')->exists($audio->archivo)) {
            abort(404, 'Archivo no encontrado en el almacenamiento.');
        }

        return Storage::disk('public')->response($audio->archivo);
    }

    public function download(Audio $audio)
    {
        if (Storage::disk('public')->exists($audio->archivo)) {
            return Storage::disk('public')->download($audio->archivo, $audio->titulo . '.mp3');
        }
        abort(404, 'Audio not found.');
    }
}