<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Services explicitly use the file store; isolate it without touching disk.
        config(['cache.stores.file' => ['driver' => 'array', 'serialize' => false]]);
        Cache::purge('file');
        Http::preventStrayRequests();
    }
}
