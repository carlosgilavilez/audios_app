<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audio extends Model
{
    use HasFactory;

    protected $table = 'audios';

    protected $fillable = [
        'titulo','descripcion','archivo','estado','fecha_publicacion',
        'autor_id','serie_id','categoria_id','libro_id','turno_id',
        'duracion', 'cita_biblica'
    ];

    public function autor()  { return $this->belongsTo(Autor::class); }
    public function serie()  { return $this->belongsTo(Serie::class); }
    public function categoria() { return $this->belongsTo(Categoria::class); }
    public function libro()  { return $this->belongsTo(Libro::class); }
    public function turno()  { return $this->belongsTo(Turno::class); }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
