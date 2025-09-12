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

    // Accessor para obtener la entidad relacionada
    public function getRelatedEntityAttribute()
    {
        if ($this->entity_type && $this->entity_id) {
            $modelClass = 'App\Models\' . $this->entity_type;
            if (class_exists($modelClass)) {
                return $modelClass::find($this->entity_id);
            }
        }
        return null;
    }
}