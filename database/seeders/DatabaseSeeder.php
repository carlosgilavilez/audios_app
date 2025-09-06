<?php 

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuario de prueba único (no se duplicará si ya existe)
        User::updateOrCreate(
            ['email' => 'test@example.com'], // criterio de búsqueda
            [
                'name' => 'Test User',
                'password' => \Illuminate\Support\Facades\Hash::make('password')
            ]
        );

        // Llamar a tus seeders personalizados
        $this->call([
            AutorSeeder::class,
            LibroSeeder::class,
            CategoriaSeeder::class,
            TurnoSeeder::class,
        ]);
    }
}
