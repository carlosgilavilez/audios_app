<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Serie;
use App\Models\ActivityLog;
use App\Http\Requests\Admin\SeriesRequest;
use Illuminate\Support\Facades\Auth;

class SerieAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Serie::query();

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('nombre', 'like', '%' . $searchTerm . '%');
        }

        $series = $query->get(); // Fetch all series
        return view('admin.series.index', compact('series'));
    }

    public function create()
    {
        return view('admin.series.create');
    }

    public function store(SeriesRequest $request) // Use SeriesRequest for validation
    {
        $name = $request->nombre;
        $original = $name;
        $counter = 2;

        while (Serie::where('nombre', $name)->exists()) {
            $name = $original . ' ' . $counter;
            $counter++;
        }

        $serie = Serie::create([
            'nombre' => $name,
            'comentario' => $request->comentario,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'entity_type' => 'Serie',
            'entity_id' => $serie->id,
        ]);

        return redirect()->route(auth()->user()->role . '.series.index')
            ->with('success', 'Serie creada correctamente');
    }

    public function edit(Serie $serie)
    {
        return view('admin.series.edit', compact('serie'));
    }

    public function update(SeriesRequest $request, Serie $serie) // Use SeriesRequest for validation
    {
        $serie->update($request->validated());

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'entity_type' => 'Serie',
            'entity_id' => $serie->id,
        ]);

        return redirect()->route(auth()->user()->role . '.series.index')->with('ok', 'Serie actualizada correctamente');
    }

    public function destroy(Serie $serie)
    {
        $id = $serie->id;

        // Reasignar audios
        \App\Models\Audio::where('serie_id', $id)
            ->update(['estado' => 'Pendiente']);

        // Log antes de eliminar
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'entity_type' => 'Serie',
            'entity_id' => $id,
        ]);

        $serie->delete();

        return redirect()->route(auth()->user()->role . '.series.index')->with('ok', 'Serie eliminada correctamente.');
    }
}