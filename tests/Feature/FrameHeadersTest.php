<?php

test('pages can be framed by this site only', function () {
    $response = $this->get(route('mushaf.reader'))->assertOk();

    expect($response->headers->get('X-Frame-Options'))->toBe('SAMEORIGIN')
        ->and($response->headers->get('Content-Security-Policy'))->toBe("frame-ancestors 'self'");
});
