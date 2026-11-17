<?php

if (PHP_SAPI !== 'cli' || $argc !== 3) {
    exit(1);
}
require __DIR__.'/../vendor/autoload.php';
$inventory = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
$root = realpath($inventory['source']);
if (! $root) {
    throw new RuntimeException('Original directory is unavailable');
}
$analyzer = new getID3;
$analyzer->option_save_attachments = false;
$analyzer->option_md5_data = false;
$analyzer->option_md5_data_source = false;
foreach ($inventory['rows'] as $index => &$row) {
    $path = realpath($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $row['path']));
    if (! $path || ! str_starts_with(strtolower($path), strtolower($root.DIRECTORY_SEPARATOR))) {
        throw new RuntimeException('Source leaves the original directory');
    }
    $info = $analyzer->analyze($path);
    $offset = $info['avdataoffset'] ?? null;
    $end = $info['avdataend'] ?? null;
    if (! is_int($offset) || ! is_int($end) || $offset < 0 || $end <= $offset || $end > filesize($path)) {
        throw new RuntimeException('Invalid MPEG payload bounds: '.$row['path']);
    }
    $full = hash_init('sha256');
    $payload = hash_init('sha256');
    $stream = fopen($path, 'rb');
    $position = 0;
    while (! feof($stream)) {
        $chunk = fread($stream, 1048576);
        $length = strlen($chunk);
        hash_update($full, $chunk);
        $start = max(0, $offset - $position);
        $stop = min($length, $end - $position);
        if ($stop > $start) {
            hash_update($payload, substr($chunk, $start, $stop - $start));
        }
        $position += $length;
    }
    fclose($stream);
    if (hash_final($full) !== $row['sha256']) {
        throw new RuntimeException('Original changed after inventory: '.$row['path']);
    }
    $row['audioSha256'] = hash_final($payload);
    $row['audioDataOffset'] = $offset;
    $row['audioDataLength'] = $end - $offset;
    if (($index + 1) % 100 === 0) {
        fwrite(STDERR, 'Payloads checked: '.($index + 1).PHP_EOL);
    }
}
unset($row);
$inventory['payloadHashedAt'] = gmdate('c');
file_put_contents($argv[2], json_encode($inventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
$groups = array_count_values(array_column($inventory['rows'], 'audioSha256'));
echo json_encode(['files' => count($inventory['rows']), 'distinctMpegPayloads' => count($groups),
    'duplicateGroups' => count(array_filter($groups, fn ($count) => $count > 1))]), PHP_EOL;
