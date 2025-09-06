<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Serie;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class SerieController extends Controller
{
    public function index(Request $request)
    {
        $q = Serie::query();
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
        $series = $q->paginate($perPage);
        return $series;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150|unique:series,nombre',
            'comentario' => 'nullable|string|max:10000',
        ]);
        $serie = Serie::create($data);
        return response()->json($serie, 201);
    }

    public function show($id)
    {
        $serie = Serie::findOrFail($id);
        return response()->json($serie);
    }

    public function update(Request $request, $id)
    {
        $serie = Serie::findOrFail($id);
        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('series')->ignore($serie->id),
            ],
            'comentario' => 'nullable|string|max:10000',
        ]);
        $serie->update($data);
        return response()->json($serie);
    }

    public function destroy($id)
    {
        $serie = Serie::findOrFail($id);
        DB::transaction(function () use ($serie) {
            $serie->audios()->update(['estado' => 'Pendiente']);
            $serie->delete();
        });
        return response()->noContent();
    }
}