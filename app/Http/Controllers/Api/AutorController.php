<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Autor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class AutorController extends Controller
{
    public function index(Request $request)
    {
        $q = Autor::query();
        if ($search = $request->string('q')->toString()) {
            $q->where(function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                      ->orWhere('comentario', 'like', '%' . $search . '%');
            });
        }
        $sortBy = $request->string('sort_by', 'nombre')->toString();
        $sortDir = $request->string('sort_dir', 'asc')->toString();
        if (!in_array($sortBy, ['nombre', 'created_at'])) {
            $sortBy = 'nombre';
        }
        $q->orderBy($sortBy, $sortDir);
        $perPage = $request->integer('per_page', 15);
        $autores = $q->paginate($perPage);
        return $autores;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150|unique:autores,nombre',
            'comentario' => 'nullable|string|max:10000',
        ]);
        $autor = Autor::create($data);
        return response()->json($autor, 201);
    }

    public function show($id)
    {
        $autor = Autor::findOrFail($id);
        return response()->json($autor);
    }

    public function update(Request $request, $id)
    {
        $autor = Autor::findOrFail($id);
        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('autores')->ignore($autor->id),
            ],
            'comentario' => 'nullable|string|max:10000',
        ]);
        $autor->update($data);
        return response()->json($autor);
    }

    public function destroy($id)
    {
        $autor = Autor::findOrFail($id);
        DB::transaction(function () use ($autor) {
            $autor->audios()->update(['estado' => 'Pendiente']);
            $autor->delete();
        });
        return response()->noContent();
    }
}
