<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // The compiled Blade path must exist before Laravel starts rendering views.
        // Fresh clones and source archives may omit empty storage directories.
        foreach (['views', 'cache', 'sessions'] as $directory) {
            $path = dirname(__DIR__).'/storage/framework/'.$directory;

            if (! is_dir($path) && ! mkdir($path, 0775, true) && ! is_dir($path)) {
                throw new \RuntimeException("Cannot create Laravel storage directory: {$path}");
            }
        }

        parent::setUp();

        $this->withoutVite();
    }
}
