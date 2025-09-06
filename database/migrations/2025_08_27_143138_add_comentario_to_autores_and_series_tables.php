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
        Schema::table('autores', function (Blueprint $table) {
            $table->text('comentario')->nullable()->after('nombre');
        });

        Schema::table('series', function (Blueprint $table) {
            $table->text('comentario')->nullable()->after('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('autores', function (Blueprint $table) {
            $table->dropColumn('comentario');
        });

        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn('comentario');
        });
    }
};