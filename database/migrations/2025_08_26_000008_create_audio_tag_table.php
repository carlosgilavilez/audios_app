<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audio_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('audio_id');
            $table->unsignedBigInteger('tag_id');
            $table->timestamps();

            $table->primary(['audio_id', 'tag_id']);

            $table->foreign('audio_id')->references('id')->on('audios')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('audio_tag');
    }
};
