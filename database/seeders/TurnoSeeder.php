<?php

namespace Database\Seeders;

use App\Models\Turno;
use Illuminate\Database\Seeder;

class TurnoSeeder extends Seeder
{
    public function run(): void
    {
        // First, delete the specific entry if it exists
        Turno::where('nombre', 'Noche')->delete();

        // Then, ensure the correct ones exist
        $turnos = ['Mañana', 'Tarde'];

        foreach ($turnos as $nombre) {
            Turno::updateOrCreate(['nombre' => $nombre]);
        }
    }
}
