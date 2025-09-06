<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AudioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'archivo' => $this->archivo,
            'estado' => $this->estado,
            'fecha_publicacion' => optional($this->fecha_publicacion)->toDateString(),

            'autor' => $this->whenLoaded('autor', fn() => [
                'id' => $this->autor->id,
                'nombre' => $this->autor->nombre,
                'comentario' => $this->autor->comentario,
            ]),
            'serie' => $this->whenLoaded('serie', fn() => [
                'id' => optional($this->serie)->id,
                'nombre' => optional($this->serie)->nombre,
                'comentario' => optional($this->serie)->comentario,
            ]),
            'categoria' => $this->whenLoaded('categoria', fn() => [
                'id' => $this->categoria->id,
                'nombre' => $this->categoria->nombre,
            ]),
            'libro' => $this->whenLoaded('libro', fn() => [
                'id' => optional($this->libro)->id,
                'nombre' => optional($this->libro)->nombre,
                'abreviatura' => optional($this->libro)->abreviatura,
            ]),
            'turno' => $this->whenLoaded('turno', fn() => [
                'id' => optional($this->turno)->id,
                'nombre' => optional($this->turno)->nombre,
            ]),
            'tags' => $this->whenLoaded('tags', fn() => $this->tags->map(fn($t) => ['id'=>$t->id,'nombre'=>$t->nombre])->all()),

            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
