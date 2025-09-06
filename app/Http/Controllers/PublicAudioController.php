<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicAudioController extends Controller
{
    // 📌 Muestra la Biblioteca de audios
    public function index()
    {
        $audios = Audio::with(['autor', 'serie', 'categoria', 'libro', 'turno'])
            ->where('estado', 'Publicado')
            ->latest('fecha_publicacion')
            ->paginate(20);

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