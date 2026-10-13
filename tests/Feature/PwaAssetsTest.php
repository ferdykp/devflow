<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaAssetsTest extends TestCase
{
    public function test_public_pwa_assets_exist_and_manifest_is_valid(): void
    {
        $manifestPath = public_path('manifest.webmanifest');
        $this->assertFileExists($manifestPath);
        $manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('offline.html'));
        foreach ([192, 512] as $size) {
            $this->assertFileExists(public_path("icons/icon-{$size}.png"));
        }
    }

    public function test_sw_does_not_store_authenticated_navigation_or_api_data(): void
    {
        $serviceWorker = file_get_contents(public_path('sw.js'));
        $this->assertStringContainsString("request.mode === 'navigate'", $serviceWorker);
        $this->assertStringNotContainsString('cache.put(', $serviceWorker);
        $this->assertStringContainsString('PRECACHE.includes(url.pathname)', $serviceWorker);
    }
}
