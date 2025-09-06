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
            // Change columns to be nullable
            $table->foreignId('autor_id')->nullable()->change();
            $table->foreignId('categoria_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audios', function (Blueprint $table) {
            // Revert columns to be not nullable
            $table->foreignId('autor_id')->nullable(false)->change();
            $table->foreignId('categoria_id')->nullable(false)->change();
        });
    }
};