<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tilawa shows the Mushaf and library books inside iframes of its own pages.
 * Allow framing by this site only; other sites still cannot embed it.
 * (Browsers ignore X-Frame-Options when CSP frame-ancestors is present, so this also
 * wins over a blanket "X-Frame-Options: deny" added in front of the app.)
 */
class AllowSameOriginFrames
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'self'");

        return $response;
    }
}
