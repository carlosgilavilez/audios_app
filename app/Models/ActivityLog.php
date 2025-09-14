<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo(__FUNCTION__, 'entity_type', 'entity_id');
    }

    public function getDescriptionAttribute()
    {
        $entityName = 'un registro';
        if ($this->subject) {
            $entityName = $this->subject->nombre ?? $this->subject->titulo ?? $this->subject->email ?? "ID: {$this->entity_id}";
        }

        $entityType = strtolower(class_basename($this->entity_type));

        // Artículo correcto por tipo (corrige: la serie)
        $article = 'el';
        $noun = $entityType;
        if ($entityType === 'serie') {
            $article = 'la';
        }

        // Autor para audios (p.ej.: ... audio "X" de Autor Y)
        $authorSuffix = '';
        if ($entityType === 'audio' && $this->subject) {
            try {
                $authorName = optional($this->subject->autor)->nombre;
                if ($authorName) {
                    $authorSuffix = ' de ' . $authorName;
                }
            } catch (\Throwable $e) {
                // Ignorar si la relación no está disponible
            }
        }

        $target = sprintf('%s %s "%s"%s', $article, $noun, $entityName, $authorSuffix);

        switch ($this->action) {
            case 'created':
                return 'creó ' . $target;
            case 'updated':
                return 'actualizó ' . $target;
            case 'deleted':
                return 'eliminó ' . $target;
            default:
                return $this->action;
        }
    }
}
