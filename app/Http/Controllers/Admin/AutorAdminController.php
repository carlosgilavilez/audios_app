<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Autor;
use App\Models\ActivityLog;
use App\Http\Requests\Admin\AuthorRequest; // Import the AuthorRequest
use Illuminate\Support\Facades\Auth;

class AutorAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Autor::query()->withCount('audios');

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('nombre', 'like', '%' . $searchTerm . '%');
        }

        $autores = $query->get(); // Fetch all authors with audios_count
        return view('admin.autores.index', compact('autores'));
    }

    public function create()
    {
        return view('admin.autores.create');
    }

    public function store(AuthorRequest $request) // Use AuthorRequest for validation
    {
        $name = $request->nombre;
        $original = $name;
        $counter = 2;

        while (Autor::where('nombre', $name)->exists()) {
            $name = $original . ' ' . $counter;
            $counter++;
        }

        $autor = Autor::create([
            'nombre' => $name,
            'comentario' => $request->comentario,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'entity_type' => 'Autor',
            'entity_id' => $autor->id,
        ]);

        return redirect()->route(auth()->user()->role . '.autores.index')->with('success', 'Autor creado correctamente');
    }

    public function edit(Autor $autor)
    {
        return view('admin.autores.edit', compact('autor'));
    }

    public function update(AuthorRequest $request, Autor $autor) // Use AuthorRequest for validation
    {
        $autor->update($request->validated());

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'entity_type' => 'Autor',
            'entity_id' => $autor->id,
        ]);

        return redirect()->route(auth()->user()->role . '.autores.index')->with('ok', 'Autor actualizado correctamente');
    }

    public function destroy(Autor $autor)
    {
        $id = $autor->id;

        // Reasignar audios
        \App\Models\Audio::where('autor_id', $id)
            ->update(['estado' => 'Pendiente']);

        // Log antes de eliminar
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'entity_type' => 'Autor',
            'entity_id' => $id,
        ]);

        $autor->delete();

        return redirect()->route(auth()->user()->role . '.autores.index')->with('ok', 'Autor eliminado correctamente.');
    }
}
