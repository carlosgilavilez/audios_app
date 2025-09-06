<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('audios', function (Blueprint $table) {
            $table->string('estado', 25)->default('Pendiente')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audios', function (Blueprint $table) {
            // Revert to the original ENUM definition
            $table->enum('estado', ['Normal', 'Pendiente', 'Revisar', 'Oculto'])->default('Normal')->change();
        });
    }
};