<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audios', function (Blueprint $table) {
            // Drop the old foreign key constraint
            $table->dropForeign('audios_autor_id_foreign');

            // IMPORTANT: This requires the doctrine/dbal package: composer require doctrine/dbal
            // Change autor_id column to be nullable
            $table->unsignedBigInteger('autor_id')->nullable()->change();

            // Re-add the foreign key with nullOnDelete behavior
            $table->foreign('autor_id')
                  ->references('id')->on('autores')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('audios', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['autor_id']);

            // Re-add the old foreign key with cascadeOnDelete
            $table->foreign('autor_id', 'audios_autor_id_foreign')
                  ->references('id')->on('autores')
                  ->cascadeOnDelete();

            // Change autor_id back to not nullable
            // Note: This will fail if there are any audios with a null autor_id
            $table->unsignedBigInteger('autor_id')->nullable(false)->change();
        });
    }
};