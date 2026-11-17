<?php

// Read-only verification of an applied private import; writes reports only.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__.'/PalmaEnvironment.php';
try {
    $work = realpath($argv[1] ?? '') ?: '';
    $root = palmaApplicationRoot();
    if ($work !== palmaPrivateDirectory('PALMA_IMPORT_WORKDIR')) {
        throw new RuntimeException('Pass the existing private import directory');
    }
    require $root.'/vendor/autoload.php';
    require __DIR__.'/AudioImportMetadata.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    if (! $app->environment('production')) {
        throw new RuntimeException('Expected production Laravel');
    }
    $read = fn ($name) => json_decode(file_get_contents($work.'/'.$name), true, 512, JSON_THROW_ON_ERROR);
    $plan = $read('plan.json');
    $result = $read('result.json');
    $before = $read('before-database.json');
    $heads = array_column($read('head-final.json')['rows'], null, 'id');
    if ($result['status'] !== 'imported' || $result['sourceHash'] !== $plan['sourceHash']) {
        throw new RuntimeException('Result does not belong to this applied plan');
    }
    $disk = Illuminate\Support\Facades\Storage::disk('public');
    $planned = array_column($plan['missing'], null, 'sourcePath');
    $resolved = [];
    if (is_file($work.'/date-resolution-result.json')) {
        $resolution = $read('date-resolution-result.json');
        if ($resolution['status'] !== 'resolved' || $resolution['importSourceHash'] !== $plan['sourceHash'] ||
            $resolution['confirmationHash'] !== hash_file('sha256', $work.'/confirmed-import-dates.json')) {
            throw new RuntimeException('Date resolution does not match the applied import and confirmation');
        }
        $resolved = array_column($resolution['resolved'], null, 'id');
    }
    $audios = App\Models\Audio::with(['autor', 'serie', 'categoria', 'libro', 'turno'])
        ->whereIn('id', array_column($result['created'], 'id'))->get()->keyBy('id');
    $issues = [];
    $rows = [];
    foreach ($result['created'] as $created) {
        $audio = $audios->get($created['id']);
        $expected = $planned[$created['sourcePath']];
        $meta = $expected['metadata'];
        $expectedStatus = $created['estado'];
        $holds = $created['holds'];
        if (isset($resolved[$created['id']])) {
            $correction = $resolved[$created['id']];
            if ($correction['sourcePath'] !== $created['sourcePath'] || $correction['status'] !== 'Publicado') {
                throw new RuntimeException('Invalid resolved source path or state');
            }
            $meta['date'] = $correction['date'];
            $meta['description'] = $correction['description'];
            $expectedStatus = 'Publicado';
            $holds = [];
        }
        if (! $audio) {
            $issues[] = ['id' => $created['id'], 'problem' => 'Missing record'];

            continue;
        }
        $actual = ['title' => $audio->titulo, 'description' => $audio->descripcion,
            'date' => $audio->fecha_publicacion, 'duration' => $audio->duracion, 'citation' => $audio->cita_biblica];
        foreach ($actual as $field => $value) {
            if ($value !== $meta[$field]) {
                $issues[] = ['id' => $audio->id, 'field' => $field, 'problem' => 'Metadata differs'];
            }
        }
        foreach (['author' => 'autor', 'series' => 'serie', 'category' => 'categoria', 'book' => 'libro', 'turn' => 'turno'] as $field => $relation) {
            if (PalmaAudioImportMetadata::normalize($audio->$relation?->nombre) !== PalmaAudioImportMetadata::normalize($meta[$field])) {
                $issues[] = ['id' => $audio->id, 'field' => $field, 'problem' => 'Relation differs'];
            }
        }
        $head = $heads[$audio->id] ?? [];
        $file = $disk->path($audio->archivo);
        if ($audio->archivo !== $created['archivo'] || $audio->estado !== $expectedStatus ||
            ! is_readable($file) || filesize($file) !== $expected['size']) {
            $issues[] = ['id' => $audio->id, 'problem' => 'State, path or file differs'];
        }
        if (($head['status'] ?? null) !== 200 || ($head['size'] ?? null) !== $expected['size']) {
            $issues[] = ['id' => $audio->id, 'problem' => 'HTTP status or served size differs'];
        }
        $rows[] = ['id' => $audio->id, 'sourcePath' => $created['sourcePath'], 'title' => $audio->titulo,
            'author' => $audio->autor?->nombre, 'category' => $audio->categoria?->nombre, 'series' => $audio->serie?->nombre,
            'date' => $audio->fecha_publicacion, 'book' => $audio->libro?->nombre, 'citation' => $audio->cita_biblica,
            'duration' => $audio->duracion, 'description' => $audio->descripcion, 'status' => $audio->estado,
            'url' => 'https://audios.iglesiapalma.org/play-audio/'.$audio->id, 'holds' => $holds];
    }
    $old = App\Models\Audio::whereIn('id', array_column($before['audios'], 'id'))->orderBy('id')
        ->get()->map(fn ($audio) => $audio->getAttributes())->all();
    $unchanged = hash('sha256', json_encode($old, JSON_THROW_ON_ERROR)) === hash('sha256', json_encode($before['audios'], JSON_THROW_ON_ERROR));
    $logs = App\Models\ActivityLog::where('user_id', $result['actorId'])->where('action', 'created')
        ->where('entity_type', 'Audio')->whereIn('entity_id', array_column($result['created'], 'id'))->count();
    $summary = ['imported' => count($rows), 'published' => count(array_filter($rows, fn ($r) => $r['status'] === 'Publicado')),
        'pending' => count(array_filter($rows, fn ($r) => $r['status'] === 'Pendiente')), 'metadataAndFileIssues' => count($issues),
        'originalRecordsUnchanged' => $unchanged, 'creationLogs' => $logs, 'headVerified' => count($heads)];
    umask(0077);
    file_put_contents($work.'/post-import-verification.json', json_encode(['verifiedAt' => gmdate('c'),
        'summary' => $summary, 'issues' => $issues, 'rows' => $rows], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    chmod($work.'/post-import-verification.json', 0600);
    $csv = fopen($work.'/imported-audios.csv', 'wb');
    fwrite($csv, "\xEF\xBB\xBF");
    $keys = array_keys($rows[0] ?? []);
    fputcsv($csv, $keys, ';', '"', '');
    foreach ($rows as $row) {
        $row['holds'] = implode(' | ', $row['holds']);
        fputcsv($csv, $row, ';', '"', '');
    }
    fclose($csv);
    chmod($work.'/imported-audios.csv', 0600);
    echo json_encode($summary, JSON_UNESCAPED_SLASHES), PHP_EOL;
    if ($issues || ! $unchanged || count($rows) !== count($plan['missing']) || $logs !== count($rows)) {
        exit(2);
    }
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage().PHP_EOL);
    exit(1);
}
