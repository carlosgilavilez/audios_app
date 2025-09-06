<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audios', function (Blueprint $table) {
            $table->id();

            // Datos básicos
            $table->string('titulo', 255);
            $table->text('descripcion')->nullable();
            $table->string('archivo', 255);
            $table->enum('estado', ['Normal', 'Pendiente', 'Revisar', 'Oculto'])->default('Normal');
            $table->date('fecha_publicacion')->nullable();

            // Relaciones
            $table->foreignId('autor_id')->constrained('autores')->cascadeOnDelete();
            $table->foreignId('serie_id')->nullable()->constrained('series')->nullOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->foreignId('libro_id')->nullable()->constrained('libros')->nullOnDelete();
            $table->foreignId('turno_id')->nullable()->constrained('turnos')->nullOnDelete();

            // Timestamps
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('audios');
    }
};