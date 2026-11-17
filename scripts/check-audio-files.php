<?php

// Read-only HEAD requests: verify the files linked by the admin catalogue.
// No credentials, file downloads, uploads or database writes are performed.
if ($argc < 3 || $argc > 4 || ($argc === 4 && ! preg_match('/^--sequential-delay-ms=([0-9]{1,5})$/', $argv[3], $delayMatch))) {
    fwrite(STDERR, "Usage: php scripts/check-audio-files.php catalogue.json output.json [--sequential-delay-ms=1000]\n");
    exit(1);
}
$sequential = $argc === 4;
$delayMs = $sequential ? (int) $delayMatch[1] : 0;
if ($delayMs > 10000) {
    fwrite(STDERR, "Sequential delay must be between 0 and 10000 milliseconds.\n");
    exit(1);
}
$concurrency = $sequential ? 1 : 4;
$catalogue = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
$queue = $catalogue['rows'];
$multi = curl_multi_init();
$active = [];
$results = [];
$start = static function (array $row) use ($multi, &$active): void {
    $id = filter_var($row['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (! $id) {
        throw new RuntimeException('Invalid audio ID.');
    }
    $handle = curl_init('https://audios.iglesiapalma.org/play-audio/'.$id);
    curl_setopt_array($handle, [CURLOPT_NOBODY => true, CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_TIMEOUT => 20,
        CURLOPT_USERAGENT => 'IglesiaPalmaAudioInventory/1.0']);
    $active[spl_object_id($handle)] = ['id' => $id, 'handle' => $handle];
    curl_multi_add_handle($multi, $handle);
};
while ($queue && count($active) < $concurrency) {
    $start(array_shift($queue));
}
do {
    curl_multi_exec($multi, $running);
    while ($message = curl_multi_info_read($multi)) {
        $handle = $message['handle'];
        $id = $active[spl_object_id($handle)]['id'];
        $info = curl_getinfo($handle);
        $results[] = ['id' => $id, 'status' => $info['http_code'],
            'size' => $info['download_content_length'] >= 0 ? (int) $info['download_content_length'] : null,
            'contentType' => $info['content_type'], 'error' => curl_error($handle) ?: null, 'attempts' => 1];
        curl_multi_remove_handle($multi, $handle);
        unset($active[spl_object_id($handle)]);
        curl_close($handle);
        if ($queue) {
            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
            $start(array_shift($queue));
        }
    }
    if ($active) {
        curl_multi_select($multi, 0.5);
    }
} while ($active);
curl_multi_close($multi);
// Confirm a transient server/network error once before classifying a file as failed.
foreach ($results as &$result) {
    if ($result['status'] !== 0 && $result['status'] < 500) {
        continue;
    }
    if ($delayMs > 0) {
        usleep($delayMs * 1000);
    }
    $handle = curl_init('https://audios.iglesiapalma.org/play-audio/'.$result['id']);
    curl_setopt_array($handle, [CURLOPT_NOBODY => true, CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_TIMEOUT => 20,
        CURLOPT_USERAGENT => 'IglesiaPalmaAudioInventory/1.0']);
    curl_exec($handle);
    $info = curl_getinfo($handle);
    $result = ['id' => $result['id'], 'previousStatus' => $result['status'], 'status' => $info['http_code'],
        'size' => $info['download_content_length'] >= 0 ? (int) $info['download_content_length'] : null,
        'contentType' => $info['content_type'], 'error' => curl_error($handle) ?: null, 'attempts' => 2];
    curl_close($handle);
}
unset($result);
usort($results, static fn ($a, $b) => $a['id'] <=> $b['id']);
file_put_contents($argv[2], json_encode(['consultedAt' => gmdate('c'),
    'method' => 'HEAD, maximum '.$concurrency.' simultaneous requests, no media downloaded',
    'sequentialDelayMs' => $delayMs, 'rows' => $results],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo json_encode(['checked' => count($results), 'statuses' => array_count_values(array_column($results, 'status'))])."\n";
