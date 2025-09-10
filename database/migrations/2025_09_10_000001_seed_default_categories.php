<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('categorias')) {
            return;
        }
        $now = now();
        foreach (['Predicaciones', 'Temas Esenciales', 'Conferencias'] as $nombre) {
            DB::table('categorias')->updateOrInsert(
                ['nombre' => $nombre],
                ['nombre' => $nombre, 'updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('categorias')) {
            return;
        }
        DB::table('categorias')->whereIn('nombre', ['Predicaciones', 'Temas Esenciales', 'Conferencias'])->delete();
    }
};

