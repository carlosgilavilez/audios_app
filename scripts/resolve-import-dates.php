<?php

// Complete explicitly confirmed dates only for pending rows from a private import.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$lock = null;
require_once __DIR__.'/PalmaEnvironment.php';
try {
    $mode = $argv[1] ?? '';
    $work = realpath($argv[2] ?? '') ?: '';
    $actorId = (int) ($argv[3] ?? 0);
    if (! in_array($mode, ['check', 'apply'], true) || $work !== palmaPrivateDirectory('PALMA_IMPORT_WORKDIR')) {
        throw new RuntimeException('Usage: php resolve-import-dates.php check|apply PRIVATE_WORK ACTOR_ID');
    }
    umask(0077);
    $lock = fopen($work.'/import.lock', 'c');
    if (! $lock || ! flock($lock, LOCK_EX | LOCK_NB)) {
        throw new RuntimeException('An import operation is running');
    }
    $root = palmaApplicationRoot();
    require $root.'/vendor/autoload.php';
    require __DIR__.'/AudioImportMetadata.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    if (! $app->environment('production') || App\Models\User::find($actorId)?->role !== 'admin') {
        throw new RuntimeException('Expected production and an existing administrator');
    }
    $read = fn ($file) => json_decode(file_get_contents($work.'/'.$file), true, 512, JSON_THROW_ON_ERROR);
    $save = function ($file, $value) use ($work): void {
        file_put_contents($work.'/'.$file, json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL, LOCK_EX);
        chmod($work.'/'.$file, 0600);
    };
    $attrs = fn ($rows) => $rows->map(fn ($row) => $row->getAttributes())->all();
    $fingerprint = fn ($rows) => hash('sha256', json_encode($attrs($rows), JSON_THROW_ON_ERROR));
    $result = $read('result.json');
    $plan = $read('plan.json');
    $source = $read('audio-import-source-2026-11-15.json');
    $overrides = $read('overrides.json');
    $confirmation = $read('confirmed-import-dates.json');
    $created = array_column($result['created'], null, 'id');
    $planned = array_column($plan['missing'], null, 'sourcePath');
    $sources = array_column($source['rows'], null, 'path');
    if ($result['status'] !== 'imported' || $result['sourceHash'] !== $plan['sourceHash'] || count($confirmation['items']) !== 3) {
        throw new RuntimeException('Expected this applied import and exactly three confirmed dates');
    }
    if (is_file($work.'/date-resolution-result.json')) {
        throw new RuntimeException('Date resolution already recorded; inspect its result instead of applying twice');
    }
    $all = App\Models\Audio::orderBy('id')->get();
    $beforeFingerprint = $fingerprint($all);
    $books = App\Models\Libro::orderBy('id')->pluck('nombre')->all();
    $updates = [];
    foreach ($confirmation['items'] as $item) {
        $id = $item['id'];
        $old = $created[$id] ?? null;
        $row = $sources[$item['sourcePath']] ?? null;
        if (isset($updates[$id]) || ! $old || $old['sourcePath'] !== $item['sourcePath'] || $old['estado'] !== 'Pendiente' ||
            ! $row || $item['date'] !== $row['filenameDate']) {
            throw new RuntimeException('Confirmation is not a unique pending filename date');
        }
        $audio = $all->firstWhere('id', $id);
        $meta = $planned[$item['sourcePath']]['metadata'];
        if (! $audio || $audio->estado !== 'Pendiente' || $audio->fecha_publicacion !== null ||
            $audio->archivo !== $old['archivo'] || $audio->titulo !== $meta['title'] || $audio->descripcion !== $meta['description'] ||
            ! in_array('Fecha discrepante pendiente de confirmar', $old['holds'], true)) {
            throw new RuntimeException('Pending row changed since the verified import: '.$id);
        }
        $file = Illuminate\Support\Facades\Storage::disk('public')->path($audio->archivo);
        if (! is_file($file) || hash_file('sha256', $file) !== $old['sha256']) {
            throw new RuntimeException('Imported file changed: '.$id);
        }
        $confirmed = PalmaAudioImportMetadata::build($row, $books, array_replace($overrides[$item['sourcePath']] ?? [], ['date' => $item['date']]));
        if (! $confirmed['readyToPublish'] || $confirmed['title'] !== $audio->titulo || ! $audio->autor_id) {
            throw new RuntimeException('Confirmed metadata is incomplete or changes the title: '.$id);
        }
        if (App\Models\Audio::where('estado', 'Publicado')->where('id', '!=', $id)
            ->whereRaw('LOWER(titulo) = ?', [mb_strtolower($audio->titulo)])
            ->where('autor_id', $audio->autor_id)->where('fecha_publicacion', $item['date'])->exists()) {
            throw new RuntimeException('Another published record has this title, author and date: '.$id);
        }
        $updates[$id] = ['id' => $id, 'sourcePath' => $item['sourcePath'], 'date' => $item['date'],
            'description' => $confirmed['description'], 'status' => 'Publicado'];
    }
    if ($mode === 'check') {
        echo json_encode(['status' => 'ready', 'updates' => array_values($updates)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
        exit;
    }
    $backupName = 'before-date-resolution-'.gmdate('Ymd_His').'.json';
    $save($backupName, ['savedAt' => gmdate('c'), 'audios' => $attrs($all), 'confirmation' => $confirmation]);
    $otherBefore = $fingerprint($all->whereNotIn('id', array_keys($updates))->values());
    Illuminate\Support\Facades\DB::transaction(function () use ($updates, $fingerprint, $beforeFingerprint, $actorId) {
        App\Models\Audio::whereIn('id', array_keys($updates))->lockForUpdate()->get();
        if ($fingerprint(App\Models\Audio::orderBy('id')->get()) !== $beforeFingerprint) {
            throw new RuntimeException('Catalogue changed before date resolution');
        }
        foreach ($updates as $id => $update) {
            App\Models\Audio::findOrFail($id)->update(['fecha_publicacion' => $update['date'],
                'descripcion' => $update['description'], 'estado' => 'Publicado']);
            App\Models\ActivityLog::create(['user_id' => $actorId, 'action' => 'updated', 'entity_type' => 'Audio', 'entity_id' => $id]);
        }
    });
    $others = App\Models\Audio::whereNotIn('id', array_keys($updates))->orderBy('id')->get();
    $report = ['status' => 'resolved', 'resolvedAt' => gmdate('c'), 'importSourceHash' => $result['sourceHash'],
        'confirmationHash' => hash_file('sha256', $work.'/confirmed-import-dates.json'), 'actorId' => $actorId,
        'beforeBackup' => $backupName, 'untouchedRecordsUnchanged' => $fingerprint($others) === $otherBefore,
        'total' => App\Models\Audio::count(), 'states' => App\Models\Audio::selectRaw('estado, COUNT(*) AS total')->groupBy('estado')->pluck('total', 'estado')->all(),
        'resolved' => array_values($updates)];
    $save('date-resolution-result.json', $report);
    foreach (['post-import-verification.json', 'imported-audios.csv'] as $name) {
        $backup = 'initial-'.$name;
        if (is_file($work.'/'.$name) && ! is_file($work.'/'.$backup)) {
            copy($work.'/'.$name, $work.'/'.$backup);
            chmod($work.'/'.$backup, 0600);
        }
    }
    Illuminate\Support\Facades\Artisan::call('cache:clear');
    echo json_encode(array_diff_key($report, ['resolved' => true]) + ['updated' => count($updates)], JSON_UNESCAPED_SLASHES), PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage().PHP_EOL);
    exit(1);
} finally {
    if ($lock) {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}
