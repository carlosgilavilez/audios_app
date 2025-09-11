<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Turno;
use Illuminate\Http\Request;

class TurnoController extends Controller
{
    public function index(Request $request)
    {
        $q = Turno::query();
        if ($search = $request->string('q')->toString()) {
            $q->where('nombre', 'like', "%{$search}%");
        }
        return $q->orderBy('nombre')->take($request->integer('limit', 10))->get(['id','nombre']);
    }
}

