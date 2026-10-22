<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Behind Laravel Cloud / Cloudflare: trust the proxy so URLs and the scheme are right.
        $middleware->trustProxies(at: '*');

        // The Mushaf and library readers are iframes of this same site.
        $middleware->web(append: [\App\Http\Middleware\AllowSameOriginFrames::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
