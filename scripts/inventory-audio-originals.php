<?php

if ($argc < 3 || $argc > 4 || ($argc === 4 && $argv[3] !== '--sha256')) {
    fwrite(STDERR, "Usage: php scripts/inventory-audio-originals.php source-directory output.json [--sha256]\n");
    exit(1);
}
require __DIR__.'/../vendor/autoload.php';
$root = realpath($argv[1]);
if (! $root) {
    throw new RuntimeException('No se encuentra la carpeta de originales.');
}
$analyzer = new getID3;
$analyzer->option_md5_data = false;
$analyzer->option_md5_data_source = false;
$analyzer->option_save_attachments = false;
$rows = [];
$withHashes = $argc === 4;
$extensions = ['mp3', 'wav', 'aac', 'm4a', 'ogg', 'flac', 'wma'];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
    if (! $file->isFile() || ! in_array(strtolower($file->getExtension()), $extensions, true)) {
        continue;
    }
    $info = $analyzer->analyze($file->getPathname());
    getid3_lib::CopyTagsToComments($info);
    $tags = $info['comments'] ?? [];
    $first = static fn ($key) => $tags[$key][0] ?? null;
    preg_match('/((?:19|20)\d{6})([a-z]\d*)?/i', $file->getBasename(), $date);
    $rows[] = [
        'path' => str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1)),
        'filename' => $file->getBasename(), 'size' => $file->getSize(),
        'filenameDate' => isset($date[1]) ? substr($date[1], 0, 4).'-'.substr($date[1], 4, 2).'-'.substr($date[1], 6, 2) : null,
        'filenameTurn' => strtoupper($date[2] ?? ''),
        'title' => $first('title'), 'artist' => $first('artist'), 'album' => $first('album'),
        'tagDate' => $first('recording_time') ?? $first('date') ?? $first('year'),
        'subtitle' => $first('subtitle'), 'genre' => $first('genre'),
        'comment' => $first('comment'), 'conductor' => $first('conductor'),
        'encodedBy' => $first('encoded_by'), 'trackNumber' => $first('track_number'),
        'sha256' => $withHashes ? hash_file('sha256', $file->getPathname()) : null,
        'durationSeconds' => isset($info['playtime_seconds']) ? round($info['playtime_seconds'], 3) : null,
        'warnings' => $info['warning'] ?? [], 'errors' => $info['error'] ?? [],
    ];
    if (count($rows) % 50 === 0) {
        fwrite(STDERR, 'Inventariados '.count($rows)." archivos\n");
    }
}
usort($rows, static fn ($a, $b) => strcmp($a['path'], $b['path']));
file_put_contents($argv[2], json_encode([
    'source' => $root, 'consultedAt' => gmdate('c'), 'rows' => $rows,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo json_encode(['files' => count($rows), 'bytes' => array_sum(array_column($rows, 'size')),
    'withTitle' => count(array_filter($rows, static fn ($r) => ! empty($r['title']))),
    'withDateInFilename' => count(array_filter($rows, static fn ($r) => ! empty($r['filenameDate']))),
], JSON_UNESCAPED_UNICODE)."\n";
