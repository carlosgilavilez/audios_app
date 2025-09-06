<?php

namespace Database\Seeders;

use App\Models\Autor;
use Illuminate\Database\Seeder;

class AutorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $autores = [
            'Pr. Juan Pérez',
            'Pr. María López',
            'Pr. Pedro González',
        ];

        foreach ($autores as $autor) {
            Autor::updateOrCreate(['nombre' => $autor]);
        }
    }
}