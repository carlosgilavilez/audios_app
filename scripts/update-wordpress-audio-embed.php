<?php

// Run through WP-CLI eval-file, using the existing WordPress administrator.
if (PHP_SAPI !== 'cli' || ! defined('WP_CLI') || ! WP_CLI) {
    http_response_code(404);
    exit;
}

$pageId = 205;
require_once __DIR__.'/PalmaEnvironment.php';
$inbox = palmaPrivateDirectory('PALMA_DEPLOY_INBOX');
$sourceFile = getenv('PALMA_EMBED_SOURCE') ?: $inbox.'/palma-audio-iframe.html';
$backupFile = getenv('PALMA_EMBED_BACKUP') ?: '';
$apply = getenv('PALMA_EMBED_APPLY') === '1';

try {
    if (! in_array(parse_url(home_url(), PHP_URL_HOST), ['iglesiapalma.org', 'www.iglesiapalma.org'], true)) {
        throw new RuntimeException('Unexpected WordPress site');
    }
    if (! current_user_can('edit_post', $pageId) || ! current_user_can('unfiltered_html')) {
        throw new RuntimeException('Use the existing administrator account with edit_post and unfiltered_html');
    }
    $page = get_post($pageId);
    if (! $page || $page->post_type !== 'page' || $page->post_name !== 'audios-predicaciones' || $page->post_status !== 'publish') {
        throw new RuntimeException('Unexpected target page');
    }
    $realSource = realpath($sourceFile);
    if (! $realSource || ! str_starts_with($realSource, $inbox.'/')) {
        throw new RuntimeException('Snippet must be in the private deployment inbox');
    }
    $snippet = trim(file_get_contents($realSource));
    $document = new DOMDocument;
    $oldErrors = libxml_use_internal_errors(true);
    $document->loadHTML($snippet);
    libxml_clear_errors();
    libxml_use_internal_errors($oldErrors);
    $frames = $document->getElementsByTagName('iframe');
    if ($frames->length !== 1) {
        throw new RuntimeException('Expected exactly one iframe');
    }
    $url = parse_url($frames->item(0)->getAttribute('src'));
    parse_str($url['query'] ?? '', $query);
    if (($url['scheme'] ?? '') !== 'https' || ($url['host'] ?? '') !== 'audios.iglesiapalma.org' ||
        ($url['path'] ?? '') !== '/audios' || ($query['embed'] ?? '') !== '1' || ($query['view'] ?? '') !== 'table') {
        throw new RuntimeException('Unexpected library URL');
    }

    $blocks = parse_blocks($page->post_content);
    $path = [0, 'innerBlocks', 0, 'innerBlocks', 0, 'innerBlocks', 0, 'innerBlocks', 0,
        'attrs', 'content', 'innerContent', 'desktop', 'value'];
    $value = &$blocks;
    foreach ($path as $key) {
        if (! is_array($value) || ! array_key_exists($key, $value)) {
            throw new RuntimeException('The Divi block structure has changed');
        }
        $value = &$value[$key];
    }
    if (! is_string($value) || ! str_contains($value, '<iframe') || ! str_contains($value, 'audios.iglesiapalma.org')) {
        throw new RuntimeException('The selected Divi property is not the audio iframe');
    }
    if (trim($value) === $snippet) {
        WP_CLI::success('Iframe already current');
        return;
    }
    $realBackup = $backupFile !== '' ? realpath($backupFile) : false;
    $backupRoot = palmaPrivateDirectory('PALMA_DEPLOY_BACKUPS');
    if (! $realBackup || ! is_file($realBackup) || ! str_starts_with($realBackup, $backupRoot.'/')) {
        throw new RuntimeException('Set PALMA_EMBED_BACKUP to a fresh private page backup');
    }
    $backupFile = $realBackup;
    $backup = json_decode(file_get_contents($backupFile), true, 512, JSON_THROW_ON_ERROR);
    if ((int) ($backup['ID'] ?? 0) !== $pageId || ($backup['post_content'] ?? null) !== $page->post_content) {
        throw new RuntimeException('Page changed after the backup; inspect and create a fresh backup');
    }
    $oldSnippet = $value;
    $oldValueHash = hash('sha256', $oldSnippet);
    unset($value);

    // Divi adds runtime fields such as orderIndex to parsed blocks. Preserve the
    // complete original serialization and replace only this JSON string token.
    preg_match_all('~"(?:[^"\\\\]|\\\\.)*"~s', $page->post_content, $strings, PREG_OFFSET_CAPTURE);
    $matches = [];
    foreach ($strings[0] as [$token, $offset]) {
        $decoded = json_decode($token, true);
        if (is_string($decoded) && $decoded === $oldSnippet) {
            $matches[] = [$token, $offset];
        }
    }
    if (count($matches) !== 1) {
        throw new RuntimeException('Expected one serialized JSON string for the iframe');
    }
    $encoded = serialize_block_attributes(['_palma_code' => $snippet]);
    if (! preg_match('/^\{"_palma_code":(.*)\}$/s', $encoded, $encodedMatch) || json_decode($encodedMatch[1], true) !== $snippet) {
        throw new RuntimeException('Cannot encode the iframe as a WordPress block attribute');
    }
    [$oldToken, $offset] = $matches[0];
    $content = substr_replace($page->post_content, $encodedMatch[1], $offset, strlen($oldToken));
    $newBlocks = parse_blocks($content);
    $newValue = $newBlocks;
    foreach ($path as $key) {
        if (! is_array($newValue) || ! array_key_exists($key, $newValue)) {
            throw new RuntimeException('The updated block structure is invalid');
        }
        $newValue = $newValue[$key];
    }
    $names = function (array $items) use (&$names): array {
        return array_map(fn ($block) => [$block['blockName'], $names($block['innerBlocks'])], $items);
    };
    if ($newValue !== $snippet || $names($newBlocks) !== $names($blocks)) {
        throw new RuntimeException('Updated content did not preserve the Divi blocks');
    }
    $report = ['page' => $pageId, 'status' => $apply ? 'updated' : 'validated',
        'old_snippet_sha256' => $oldValueHash, 'new_snippet_sha256' => hash('sha256', $snippet),
        'backup' => $backupFile, 'custom_block_structure_preserved' => true, 'method' => 'single_json_string_replacement'];
    if ($apply) {
        $result = wp_update_post(wp_slash(['ID' => $pageId, 'post_content' => $content]), true);
        if (is_wp_error($result)) {
            throw new RuntimeException($result->get_error_message());
        }
        clean_post_cache($pageId);
        $saved = get_post($pageId);
        if ($saved->post_content !== $content || $saved->post_status !== $page->post_status ||
            $saved->post_author !== $page->post_author || $saved->post_name !== $page->post_name) {
            wp_update_post(wp_slash(['ID' => $pageId, 'post_content' => $backup['post_content']]), true);
            throw new RuntimeException('Saved content differed; restored original content');
        }
        file_put_contents(dirname($backupFile).'/wordpress-page-205-update-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    WP_CLI::line(wp_json_encode($report, JSON_UNESCAPED_SLASHES));
} catch (Throwable $error) {
    WP_CLI::error($error->getMessage());
}
