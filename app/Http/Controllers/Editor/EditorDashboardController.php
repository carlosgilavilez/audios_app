<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Audio;
use App\Models\Autor;
use App\Models\Serie;
use Illuminate\Support\Facades\Auth;

class EditorDashboardController extends Controller
{
    public function index()
    {
        $audios = Audio::with(['autor', 'serie'])
                       ->orderBy('created_at', 'desc')
                       ->take(5)
                       ->get();

        $autoresCount = Autor::count();
        $seriesCount = Serie::count();
        $audiosCount = Audio::count();

        return view('editor.dashboard', compact('audios', 'autoresCount', 'seriesCount', 'audiosCount'));
    }
}
