<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = ['Predicaciones', 'Temas Esenciales', 'Conferencias'];

        foreach ($categorias as $nombre) {
            Categoria::updateOrCreate(['nombre' => $nombre]);
        }
    }
}