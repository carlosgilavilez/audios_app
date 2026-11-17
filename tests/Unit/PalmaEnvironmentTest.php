<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;

require_once __DIR__.'/../../scripts/PalmaEnvironment.php';

class PalmaEnvironmentTest extends TestCase
{
    private array $previous;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previous = [getenv('PALMA_LARAVEL_ROOT'), getenv('PALMA_IMPORT_WORKDIR')];
        putenv('PALMA_LARAVEL_ROOT='.dirname(__DIR__, 2));
        putenv('PALMA_IMPORT_WORKDIR');
    }

    protected function tearDown(): void
    {
        foreach (['PALMA_LARAVEL_ROOT', 'PALMA_IMPORT_WORKDIR'] as $index => $name) {
            putenv($this->previous[$index] === false ? $name : $name.'='.$this->previous[$index]);
        }
        parent::tearDown();
    }

    public function test_application_root_must_be_explicitly_configured(): void
    {
        putenv('PALMA_LARAVEL_ROOT');
        $this->expectException(RuntimeException::class);
        palmaApplicationRoot();
    }

    public function test_private_directory_must_be_explicitly_configured(): void
    {
        $this->expectException(RuntimeException::class);
        palmaPrivateDirectory('PALMA_IMPORT_WORKDIR');
    }

    public function test_a_directory_under_the_web_root_is_rejected(): void
    {
        putenv('PALMA_IMPORT_WORKDIR='.dirname(__DIR__, 2).'/public');
        $this->expectException(RuntimeException::class);
        palmaPrivateDirectory('PALMA_IMPORT_WORKDIR');
    }

    public function test_a_configured_existing_private_directory_is_accepted(): void
    {
        $private = realpath(dirname(__DIR__, 4));
        putenv('PALMA_IMPORT_WORKDIR='.$private);
        $this->assertSame($private, palmaPrivateDirectory('PALMA_IMPORT_WORKDIR'));
    }
}
