<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Audio;
use Illuminate\Support\Facades\Storage;
use getID3;

class FixAudioDurations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audio:fix-durations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Iterate through audios with null duration and calculate it from the file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix audio durations...');

        $audiosToFix = Audio::whereNull('duracion')->orWhere('duracion', '')->get();

        if ($audiosToFix->isEmpty()) {
            $this->info('No audios found with missing duration. All good!');
            return;
        }

        $progressBar = $this->output->createProgressBar($audiosToFix->count());
        $progressBar->start();

        $getID3 = new getID3;
        $fixedCount = 0;

        foreach ($audiosToFix as $audio) {
            // The 'archivo' column stores the public URL like '/storage/audios/file.mp3'
            // We need to convert it back to a storage path: 'public/audios/file.mp3'
            $storagePath = str_replace('/storage/', 'public/', $audio->archivo);

            if (!Storage::exists($storagePath)) {
                $this->error("\nFile not found for audio ID: {$audio->id} at path: {$storagePath}");
                $progressBar->advance();
                continue;
            }

            // getID3 needs an absolute path to the file
            $absolutePath = Storage::path($storagePath);
            $fileInfo = $getID3->analyze($absolutePath);

            if (!empty($fileInfo['playtime_string'])) {
                $audio->duracion = $fileInfo['playtime_string'];
                $audio->save();
                $fixedCount++;
            } else {
                $this->warn("\nCould not extract duration for audio ID: {$audio->id}");
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nFinished. Fixed durations for {$fixedCount} audio(s).");
    }
}