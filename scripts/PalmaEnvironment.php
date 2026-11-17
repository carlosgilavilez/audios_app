<?php

// Operational paths are supplied privately at execution time, never committed.
function palmaApplicationRoot(): string
{
    $configured = getenv('PALMA_LARAVEL_ROOT') ?: '';
    $root = $configured !== '' ? realpath($configured) : false;
    if (! $root || ! is_file($root.'/artisan') || ! is_file($root.'/vendor/autoload.php')) {
        throw new RuntimeException('Set PALMA_LARAVEL_ROOT to the verified application directory');
    }

    return $root;
}

function palmaPrivateDirectory(string $variable): string
{
    $configured = getenv($variable) ?: '';
    $directory = $configured !== '' ? realpath($configured) : false;
    $webRoot = dirname(palmaApplicationRoot());
    if (! $directory || ! is_dir($directory) || $directory === $webRoot ||
        str_starts_with($directory, $webRoot.DIRECTORY_SEPARATOR)) {
        throw new RuntimeException('Set '.$variable.' to an existing directory outside the web root');
    }

    return $directory;
}
