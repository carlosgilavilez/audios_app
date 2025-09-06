<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Audio;
use Illuminate\Support\Facades\Storage;
use getID3;

class RecoverAudios extends Command
{
    protected $signature = 'audio:recover';
    protected $description = 'Recover audios from storage that are not in the database.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting audio recovery process...');

        $files = Storage::disk('public')->files('audios');
        $recoveredCount = 0;

        foreach ($files as $file) {
            $path = Storage::disk('public')->path($file);
            
            if (Audio::where('archivo', $file)->exists()) {
                $this->line("Skipping: Audio for file {$file} already exists in the database.");
                continue;
            }

            $this->line("Found new audio file: {$file}");

            // Extract metadata using getID3
            $getID3 = new getID3;
            $fileInfo = $getID3->analyze($path);
            $duration = $fileInfo['playtime_string'] ?? null;

            Audio::create([
                'titulo' => basename($file),
                'archivo' => $file, // Store the relative path
                'estado' => 'Pendiente',
                'duracion' => $duration,
            ]);

            $recoveredCount++;
            $this->info("Recovered: " . basename($file));
        }

        $this->info("Audio recovery process finished. Recovered {$recoveredCount} audios.");
        return 0;
    }
}