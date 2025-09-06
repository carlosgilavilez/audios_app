<?php

namespace Database\Seeders;

use App\Models\Libro;
use Illuminate\Database\Seeder;

class LibroSeeder extends Seeder
{
    public function run(): void
    {
        $libros = [
            ['Génesis', 'Gn'], ['Éxodo', 'Ex'], ['Levítico', 'Lv'], ['Números', 'Nm'], ['Deuteronomio', 'Dt'],
            ['Josué', 'Jos'], ['Jueces', 'Jue'], ['Rut', 'Rt'], ['1 Samuel', '1S'], ['2 Samuel', '2S'],
            ['1 Reyes', '1R'], ['2 Reyes', '2R'], ['1 Crónicas', '1Cr'], ['2 Crónicas', '2Cr'], ['Esdras', 'Esd'],
            ['Nehemías', 'Neh'], ['Ester', 'Est'], ['Job', 'Job'], ['Salmos', 'Sal'], ['Proverbios', 'Prov'],
            ['Eclesiastés', 'Ecl'], ['Cantares', 'Cnt'], ['Isaías', 'Is'], ['Jeremías', 'Jer'], ['Lamentaciones', 'Lam'],
            ['Ezequiel', 'Ez'], ['Daniel', 'Dan'], ['Oseas', 'Os'], ['Joel', 'Jl'], ['Amós', 'Am'],
            ['Abdías', 'Abd'], ['Jonás', 'Jon'], ['Miqueas', 'Mi'], ['Nahúm', 'Nah'], ['Habacuc', 'Hab'],
            ['Sofonías', 'Sof'], ['Hageo', 'Hag'], ['Zacarías', 'Zac'], ['Malaquías', 'Mal'],
            ['Mateo', 'Mt'], ['Marcos', 'Mc'], ['Lucas', 'Lc'], ['Juan', 'Jn'], ['Hechos', 'Hch'],
            ['Romanos', 'Rom'], ['1 Corintios', '1Co'], ['2 Corintios', '2Co'], ['Gálatas', 'Gal'], ['Efesios', 'Ef'],
            ['Filipenses', 'Fil'], ['Colosenses', 'Col'], ['1 Tesalonicenses', '1Ts'], ['2 Tesalonicenses', '2Ts'],
            ['1 Timoteo', '1Tm'], ['2 Timoteo', '2Tm'], ['Tito', 'Tit'], ['Filemón', 'Flm'], ['Hebreos', 'Heb'],
            ['Santiago', 'Stg'], ['1 Pedro', '1P'], ['2 Pedro', '2P'], ['1 Juan', '1Jn'], ['2 Juan', '2Jn'],
            ['3 Juan', '3Jn'], ['Judas', 'Jud'], ['Apocalipsis', 'Ap']
        ];

        foreach ($libros as $libro) {
            Libro::updateOrCreate(
                ['nombre' => $libro[0]],
                ['abreviatura' => $libro[1]]
            );
        }
    }
}