<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Libro extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','abreviatura'];

    public function audios()
    {
        return $this->hasMany(Audio::class);
    }
}
