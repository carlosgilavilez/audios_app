<?php

// Private CLI workflow: plan, check transferred files, then explicitly apply.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__.'/PalmaEnvironment.php';
$mode = $argv[1] ?? '';
$sourceFile = realpath($argv[2] ?? '') ?: '';
$work = realpath($argv[3] ?? '') ?: '';
$actorId = (int) ($argv[4] ?? 0);
$lock = null;
try {
    $root = palmaApplicationRoot();
    $expectedWork = palmaPrivateDirectory('PALMA_IMPORT_WORKDIR');
    if (! in_array($mode, ['plan', 'check', 'apply'], true) ||
        $work !== $expectedWork ||
        ! $sourceFile || ! str_starts_with($sourceFile, $work.'/') || ! is_dir($work.'/staging')) {
        throw new RuntimeException('Usage: php import-local-audios.php plan|check|apply PRIVATE_SOURCE_JSON PRIVATE_WORKDIR [ACTOR_ID]');
    }
    $lock = fopen($work.'/import.lock', 'c');
    if (! $lock || ! flock($lock, LOCK_EX | LOCK_NB)) {
        throw new RuntimeException('Another import operation is running');
    }
    umask(0077);
    require $root.'/vendor/autoload.php';
    require __DIR__.'/AudioImportMetadata.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    if (! $app->environment('production')) {
        throw new RuntimeException('Expected production Laravel');
    }
    $disk = Illuminate\Support\Facades\Storage::disk('public');
    $readJson = fn ($file) => json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    $saveJson = function ($file, $value): void {
        $data = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
        if (file_put_contents($file, $data, LOCK_EX) === false) {
            throw new RuntimeException('Cannot save private report: '.basename($file));
        }
        chmod($file, 0600);
    };
    $attributes = fn ($rows) => $rows->map(fn ($row) => $row->getAttributes())->all();
    $fingerprint = fn ($rows) => hash('sha256', json_encode($attributes($rows), JSON_THROW_ON_ERROR));
    $source = $readJson($sourceFile);
    $overridesFile = $work.'/overrides.json';
    if (is_file($overridesFile)) {
        $source['overrides'] = array_replace($source['overrides'] ?? [], $readJson($overridesFile));
    }
    $sourceFingerprint = fn () => hash('sha256', hash_file('sha256', $sourceFile).'|'.(is_file($overridesFile) ? hash_file('sha256', $overridesFile) : ''));
    $sourceHash = $sourceFingerprint();
    $codeHash = hash('sha256', hash_file('sha256', __FILE__).'|'.hash_file('sha256', __DIR__.'/AudioImportMetadata.php'));
    $current = App\Models\Audio::orderBy('id')->get();

    if ($mode === 'plan') {
        $hashCacheFile = $work.'/existing-file-hashes.json';
        $cache = is_file($hashCacheFile) ? $readJson($hashCacheFile) : [];
        $hashIndex = [];
        $payloadIndex = [];
        $analyzer = new getID3;
        $analyzer->option_save_attachments = false;
        $analyzer->option_md5_data = false;
        $analyzer->option_md5_data_source = false;
        $recordInfo = [];
        $oldMatches = array_column($source['previousMatches'] ?? [], null, 'path');
        foreach ($current as $index => $audio) {
            if (! $audio->archivo || ! $disk->exists($audio->archivo)) {
                throw new RuntimeException('Existing record has no file: '.$audio->id);
            }
            $file = $disk->path($audio->archivo);
            $stat = stat($file);
            $signature = [$stat['size'], $stat['mtime'], $stat['ctime'], $stat['ino']];
            $cached = $cache[$audio->archivo] ?? null;
            $hash = $cached && $cached['stat'] === $signature ? $cached['sha256'] : hash_file('sha256', $file);
            if ($cached && $cached['stat'] === $signature && ! empty($cached['audioSha256'])) {
                $payloadHash = $cached['audioSha256'];
            } else {
                $info = $analyzer->analyze($file);
                $offset = $info['avdataoffset'] ?? null;
                $end = $info['avdataend'] ?? null;
                if (! is_int($offset) || ! is_int($end) || $end <= $offset || $end > $stat['size']) {
                    throw new RuntimeException('Invalid existing MPEG payload: '.$audio->id);
                }
                $stream = fopen($file, 'rb');
                fseek($stream, $offset);
                $payloadContext = hash_init('sha256');
                hash_update_stream($payloadContext, $stream, $end - $offset);
                fclose($stream);
                $payloadHash = hash_final($payloadContext);
            }
            $cache[$audio->archivo] = ['stat' => $signature, 'sha256' => $hash, 'audioSha256' => $payloadHash];
            $hashIndex[$hash][] = $audio->id;
            $payloadIndex[$payloadHash][] = $audio->id;
            $recordInfo[$audio->id] = ['title' => PalmaAudioImportMetadata::titleKey($audio->titulo), 'size' => $stat['size']];
            if (($index + 1) % 100 === 0) {
                echo 'Existing files checked: '.($index + 1).PHP_EOL;
                $saveJson($hashCacheFile, $cache);
            }
        }
        $saveJson($hashCacheFile, $cache);
        $books = App\Models\Libro::orderBy('id')->pluck('nombre')->all();
        $seenPaths = [];
        $seenNames = [];
        $seenHashes = [];
        $seenPayloads = [];
        $missing = [];
        $skipped = [];
        foreach ($source['rows'] as $row) {
            $name = basename($row['path']);
            if (! preg_match('/^[\pL\pN_. ()-]+\.mp3$/iu', $name) || str_contains($row['path'], '..') ||
                ! preg_match('/^[0-9a-f]{64}$/', $row['sha256'] ?? '') || ($row['size'] ?? 0) <= 0 ||
                isset($seenPaths[$row['path']]) || isset($seenNames[$name])) {
                throw new RuntimeException('Invalid or duplicate source path/name: '.$row['path']);
            }
            $seenPaths[$row['path']] = true;
            $seenNames[$name] = true;
            $match = $hashIndex[$row['sha256']][0] ?? null;
            $method = 'sha256';
            $audioHash = $row['audioSha256'] ?? null;
            if ($audioHash !== null && ! preg_match('/^[0-9a-f]{64}$/', $audioHash)) {
                throw new RuntimeException('Invalid source audio payload hash');
            }
            if (! $match && $audioHash && isset($payloadIndex[$audioHash])) {
                $match = $payloadIndex[$audioHash][0];
                $method = 'mpeg_payload_sha256';
            }
            if (! $match && isset($oldMatches[$row['path']])) {
                $prior = $oldMatches[$row['path']];
                $existing = $recordInfo[$prior['id']] ?? null;
                if ($existing && $existing['size'] === $row['size'] &&
                    $existing['title'] === PalmaAudioImportMetadata::titleKey($prior['title'])) {
                    $match = $prior['id'];
                    $method = 'previous_verified_metadata_and_exact_size';
                }
            }
            if ($match) {
                $skipped[] = ['path' => $row['path'], 'id' => $match, 'method' => $method];

                continue;
            }
            if (isset($seenHashes[$row['sha256']])) {
                $skipped[] = ['path' => $row['path'], 'sameAsLocal' => $seenHashes[$row['sha256']], 'method' => 'duplicate_local_hash'];

                continue;
            }
            if ($audioHash && isset($seenPayloads[$audioHash])) {
                $skipped[] = ['path' => $row['path'], 'sameAsLocal' => $seenPayloads[$audioHash], 'method' => 'duplicate_local_mpeg_payload'];

                continue;
            }
            $seenHashes[$row['sha256']] = $row['path'];
            if ($audioHash) {
                $seenPayloads[$audioHash] = $row['path'];
            }
            $metadataOverride = $source['overrides'][$row['path']] ?? [];
            if (($source['overrides']['_settings']['datePolicy'] ?? '') === 'filename' && ! array_key_exists('date', $metadataOverride)) {
                $metadataOverride['date'] = $row['filenameDate'];
            }
            $metadata = PalmaAudioImportMetadata::build($row, $books, $metadataOverride);
            if (str_contains($name, '_eng_') && ! str_contains(mb_strtolower($metadata['title']), 'inglés')) {
                $metadata['title'] .= ' (inglés)';
            }
            $missing[] = ['sourcePath' => $row['path'], 'filename' => $name, 'size' => $row['size'],
                'sha256' => $row['sha256'], 'metadata' => $metadata];
        }
        if ($sourceFingerprint() !== $sourceHash) {
            throw new RuntimeException('Source changed during planning');
        }
        $plan = ['createdAt' => gmdate('c'), 'sourceHash' => $sourceHash, 'codeHash' => $codeHash, 'root' => $root,
            'baselineFingerprint' => $fingerprint($current), 'baselineCount' => $current->count(),
            'baselineStates' => $current->countBy('estado')->all(), 'originals' => count($source['rows']),
            'skipped' => $skipped, 'missing' => $missing];
        $saveJson($work.'/plan.json', $plan);
        echo json_encode(['status' => 'planned', 'originals' => $plan['originals'], 'existingRecords' => $plan['baselineCount'],
            'alreadyInProduction' => count($skipped), 'missing' => count($missing),
            'readyToPublish' => count(array_filter($missing, fn ($row) => $row['metadata']['readyToPublish'])),
            'missingBytes' => array_sum(array_column($missing, 'size')),
            'report' => $work.'/plan.json'], JSON_UNESCAPED_SLASHES), PHP_EOL;
    } else {
        $plan = $readJson($work.'/plan.json');
        if ($plan['root'] !== $root || $plan['sourceHash'] !== $sourceHash || ($plan['codeHash'] ?? null) !== $codeHash || $plan['baselineFingerprint'] !== $fingerprint($current)) {
            throw new RuntimeException('Source or catalogue changed; recreate the plan before continuing');
        }
        $verifiedFile = $work.'/verified-files.json';
        $verified = is_file($verifiedFile) ? $readJson($verifiedFile) : [];
        $verifiedStats = $verified['files'] ?? [];
        $problems = [];
        foreach ($plan['missing'] as $index => $row) {
            $file = $work.'/staging/'.$row['filename'];
            if (! is_file($file) || filesize($file) !== $row['size']) {
                $problems[] = $row['filename'];

                continue;
            }
            $stat = stat($file);
            $signature = [$stat['size'], $stat['mtime'], $stat['ctime'], $stat['ino']];
            $previous = $verifiedStats[$row['filename']] ?? null;
            $hash = $previous && $previous['stat'] === $signature && $previous['sha256'] === $row['sha256']
                ? $previous['sha256'] : hash_file('sha256', $file);
            if ($hash !== $row['sha256']) {
                throw new RuntimeException('Transferred file hash differs: '.$row['filename']);
            }
            $verifiedStats[$row['filename']] = ['stat' => $signature, 'sha256' => $hash];
            if (($index + 1) % 50 === 0) {
                echo 'Transferred files checked: '.($index + 1).PHP_EOL;
            }
        }
        if ($problems) {
            $saveJson($work.'/files-not-ready.json', $problems);
            throw new RuntimeException('Transfer incomplete: '.count($problems).' files are absent or incomplete');
        }
        $saveJson($verifiedFile, ['sourceHash' => $sourceHash, 'files' => $verifiedStats, 'checkedAt' => gmdate('c')]);
        if ($mode === 'check') {
            echo json_encode(['status' => 'validated', 'files' => count($plan['missing']), 'sha256' => 'all_match']), PHP_EOL;
        } else {
            $actor = App\Models\User::find($actorId);
            if (! $actor || $actor->role !== 'admin') {
                throw new RuntimeException('Pass the existing administrator ID as ACTOR_ID');
            }
            $backup = ['createdAt' => gmdate('c'), 'sourceHash' => $sourceHash, 'audios' => $attributes($current)];
            foreach (['autores' => App\Models\Autor::class, 'series' => App\Models\Serie::class,
                'categorias' => App\Models\Categoria::class, 'libros' => App\Models\Libro::class, 'turnos' => App\Models\Turno::class] as $key => $class) {
                $backup[$key] = $attributes($class::orderBy('id')->get());
            }
            $saveJson($work.'/before-database.json', $backup);
            $destinationPrefix = 'audios/import-2026-11-15';
            $destinationDirectory = $disk->path($destinationPrefix);
            if (! is_dir($destinationDirectory) && ! mkdir($destinationDirectory, 0755, true)) {
                throw new RuntimeException('Cannot create audio destination');
            }
            chmod($destinationDirectory, 0755);
            $moved = [];
            $created = [];
            $mapping = [];
            $resolve = function (string $class, ?string $name, bool $canCreate = true) use (&$mapping): ?int {
                if (! $name) {
                    return null;
                }
                $key = $class.'|'.$name;
                if (array_key_exists($key, $mapping)) {
                    return $mapping[$key];
                }
                $items = $class::orderBy('id')->get();
                $item = $items->firstWhere('nombre', $name) ?: $items->first(fn ($row) => PalmaAudioImportMetadata::normalize($row->nombre) === PalmaAudioImportMetadata::normalize($name));
                if (! $item && $canCreate) {
                    $item = $class::create(['nombre' => $name]);
                }
                if (! $item) {
                    throw new RuntimeException('Unknown required metadata name: '.$name);
                }

                return $mapping[$key] = $item->id;
            };
            try {
                Illuminate\Support\Facades\DB::transaction(function () use ($plan, $fingerprint, $resolve, $actorId,
                    $work, $disk, $destinationPrefix, &$moved, &$created) {
                    if ($fingerprint(App\Models\Audio::orderBy('id')->get()) !== $plan['baselineFingerprint']) {
                        throw new RuntimeException('Catalogue changed before transaction');
                    }
                    foreach ($plan['missing'] as $row) {
                        $metadata = $row['metadata'];
                        $relativePath = $destinationPrefix.'/'.$row['filename'];
                        $from = $work.'/staging/'.$row['filename'];
                        $to = $disk->path($relativePath);
                        if (file_exists($to) || App\Models\Audio::where('archivo', $relativePath)->exists()) {
                            throw new RuntimeException('Destination already exists: '.$row['filename']);
                        }
                        $authorId = $resolve(App\Models\Autor::class, $metadata['author']);
                        if ($metadata['readyToPublish'] && App\Models\Audio::where('estado', 'Publicado')
                            ->whereRaw('LOWER(titulo) = ?', [mb_strtolower($metadata['title'])])
                            ->where('autor_id', $authorId)->where('fecha_publicacion', $metadata['date'])->exists()) {
                            throw new RuntimeException('Duplicate published title/author/date: '.$metadata['title']);
                        }
                        if (! rename($from, $to)) {
                            throw new RuntimeException('Cannot move verified audio: '.$row['filename']);
                        }
                        $moved[] = [$from, $to];
                        chmod($to, 0644);
                        $audio = App\Models\Audio::create(['titulo' => $metadata['title'], 'descripcion' => $metadata['description'],
                            'archivo' => $relativePath, 'autor_id' => $authorId, 'serie_id' => $resolve(App\Models\Serie::class, $metadata['series']),
                            'categoria_id' => $resolve(App\Models\Categoria::class, $metadata['category'], false),
                            'libro_id' => $resolve(App\Models\Libro::class, $metadata['book'], false),
                            'turno_id' => $resolve(App\Models\Turno::class, $metadata['turn'], false),
                            'cita_biblica' => $metadata['citation'], 'duracion' => $metadata['duration'],
                            'fecha_publicacion' => $metadata['date'], 'estado' => $metadata['readyToPublish'] ? 'Publicado' : 'Pendiente']);
                        App\Models\ActivityLog::create(['user_id' => $actorId, 'action' => 'created', 'entity_type' => 'Audio', 'entity_id' => $audio->id]);
                        $created[] = ['id' => $audio->id, 'sourcePath' => $row['sourcePath'], 'archivo' => $relativePath,
                            'estado' => $audio->estado, 'sha256' => $row['sha256'], 'holds' => $metadata['holds']];
                    }
                });
            } catch (Throwable $error) {
                foreach (array_reverse($moved) as [$from, $to]) {
                    if (is_file($to) && ! file_exists($from)) {
                        rename($to, $from);
                    }
                }
                throw $error;
            }
            $afterOriginals = App\Models\Audio::whereIn('id', $current->pluck('id'))->orderBy('id')->get();
            $report = ['status' => 'imported', 'finishedAt' => gmdate('c'), 'sourceHash' => $sourceHash,
                'actorId' => $actorId, 'originalRecordsUnchanged' => $fingerprint($afterOriginals) === $plan['baselineFingerprint'],
                'beforeCount' => $plan['baselineCount'], 'afterCount' => App\Models\Audio::count(),
                'states' => App\Models\Audio::query()->selectRaw('estado,COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado')->all(),
                'created' => $created];
            $saveJson($work.'/result.json', $report);
            Illuminate\Support\Facades\Artisan::call('cache:clear');
            echo json_encode(array_diff_key($report, ['created' => true]) + ['createdCount' => count($created)], JSON_UNESCAPED_SLASHES), PHP_EOL;
        }
    }
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage().PHP_EOL);
    exit(1);
} finally {
    if ($lock) {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}
