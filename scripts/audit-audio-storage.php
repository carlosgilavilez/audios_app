<?php

// Read-only production audit. Store its JSON report outside public_html.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__.'/PalmaEnvironment.php';
$root = palmaApplicationRoot();
$output = $argv[1] ?? '';
try {
    if (! $output || ! is_dir(dirname($output))) {
        throw new RuntimeException('Pass a JSON destination in an existing private directory');
    }
    $outputDirectory = realpath(dirname($output));
    $webRoot = realpath(dirname($root));
    if (! $webRoot || $outputDirectory === $webRoot || str_starts_with($outputDirectory, $webRoot.DIRECTORY_SEPARATOR)) {
        throw new RuntimeException('The report must be outside public_html');
    }
    require $root.'/vendor/autoload.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    if (! $app->environment('production')) {
        throw new RuntimeException('Expected the production Laravel application');
    }
    $disk = Illuminate\Support\Facades\Storage::disk('public');
    $records = App\Models\Audio::query()->get(['id', 'archivo', 'estado']);
    $references = [];
    $missing = [];
    foreach ($records as $record) {
        $path = (string) $record->archivo;
        $references[$path][] = $record->id;
        if ($path === '' || ! $disk->exists($path)) {
            $missing[] = ['id' => $record->id, 'path' => $path];
        }
    }
    $extensions = ['mp3', 'm4a', 'wav', 'ogg', 'aac', 'flac', 'aif', 'aiff', 'wma', 'opus', 'mp4'];
    $audioFiles = [];
    $unregistered = [];
    $otherFileCount = 0;
    foreach ($disk->allFiles() as $path) {
        if (! in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $extensions, true)) {
            $otherFileCount++;
            continue;
        }
        $file = ['path' => $path, 'size' => $disk->size($path)];
        $audioFiles[] = $file;
        if (! isset($references[$path])) {
            $unregistered[] = $file;
        }
    }
    $duplicates = array_filter($references, fn ($ids) => count($ids) > 1);
    $summary = [
        'records' => $records->count(),
        'states' => $records->countBy('estado')->all(),
        'uniqueRegisteredPaths' => count($references),
        'audioFiles' => count($audioFiles),
        'audioBytes' => array_sum(array_column($audioFiles, 'size')),
        'missingRecords' => count($missing),
        'unregisteredAudioFiles' => count($unregistered),
        'duplicateReferences' => count($duplicates),
        'otherFiles' => $otherFileCount,
    ];
    $report = ['source' => $root, 'consultedAt' => gmdate('c'), 'disk' => 'public',
        'summary' => $summary, 'missingRecords' => $missing, 'unregisteredAudioFiles' => $unregistered,
        'duplicateReferences' => $duplicates, 'audioFiles' => $audioFiles];
    $previousMask = umask(0077);
    $written = file_put_contents($output, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
    umask($previousMask);
    if ($written === false) {
        throw new RuntimeException('Could not save the audit report');
    }
    chmod($output, 0600);
    echo json_encode(['report' => $output, 'summary' => $summary], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage().PHP_EOL);
    exit(1);
}
