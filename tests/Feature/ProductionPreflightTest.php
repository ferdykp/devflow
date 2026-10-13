<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductionPreflightTest extends TestCase
{
    public function test_strict_preflight_refuses_unsafe_development_configuration(): void
    {
        config()->set('app.debug', true);
        config()->set('app.url', 'http://localhost:8000');
        $this->artisan('devflow:preflight', ['--strict' => true])->assertExitCode(1);
    }

    public function test_strict_preflight_rejects_invalid_encryption_key(): void
    {
        config()->set('app.key', 'short-key');
        config()->set('app.url', 'https://devflow.example.com');
        $this->artisan('devflow:preflight', ['--strict' => true])->assertExitCode(1);
    }

    public function test_strict_preflight_rejects_fake_https_localhost_url(): void
    {
        config()->set('app.url', 'https://localhost');
        $this->artisan('devflow:preflight', ['--strict' => true])->assertExitCode(1);
    }

    public function test_non_strict_preflight_is_diagnostic(): void
    {
        config()->set('app.debug', true);
        $this->artisan('devflow:preflight')->assertExitCode(0);
    }
}
