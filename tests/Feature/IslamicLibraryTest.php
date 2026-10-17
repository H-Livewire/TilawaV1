<?php

use App\Livewire\IslamicLibrary;
use Livewire\Livewire;

test('guests can browse the library and open the actual book reader', function () {
    Livewire::test(IslamicLibrary::class)
        ->assertOk()
        ->assertSee('Islamic library')
        ->assertSee('Hisnul Muslim')
        ->assertSeeHtml(route('library.hisnul-muslim.reader'));

    $this->get(route('library.hisnul-muslim.reader'))
        ->assertOk()
        ->assertSeeHtml(route('library.hisnul-muslim.pdf'))
        ->assertSee('Previous page')
        ->assertSee('Zoom in');
});

test('the book endpoint serves the supplied PDF inline with range support', function () {
    $response = $this->get(route('library.hisnul-muslim.pdf'));
    $response->assertOk()->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Accept-Ranges', 'bytes')
        ->assertHeader('Content-Disposition', 'inline; filename="hisnul-muslim.pdf"');

    expect(hash_file('sha256', $response->baseResponse->getFile()->getPathname()))
        ->toBe('885b33e890a382550db227c4d09b9137945851bffcc4605bcd2067f7272ff634');

    $this->withHeader('Range', 'bytes=0-99')->get(route('library.hisnul-muslim.pdf'))
        ->assertStatus(206)->assertHeader('Content-Length', '100');
});

test('library has no arbitrary book file endpoint', function () {
    $this->get('/library/unknown/pdf')->assertNotFound();
});

test('a missing book returns a friendly not found response', function () {
    $originalStorage = storage_path();
    app()->useStoragePath(sys_get_temp_dir().'/tilawa-book-not-installed');

    try {
        $this->get(route('library.hisnul-muslim.pdf'))->assertNotFound();
    } finally {
        app()->useStoragePath($originalStorage);
    }
});

test('the library offers separate English and Kiswahili readers with matching PDFs', function () {
    $this->get(route('library'))
        ->assertOk()->assertSee('English')->assertSee('Kiswahili')
        ->assertSeeHtml(route('library.hisnul-muslim.reader'))
        ->assertSeeHtml(route('library.hisnul-muslim.swahili.reader'));

    $this->get(route('library.hisnul-muslim.swahili.reader'))
        ->assertOk()->assertSee('Kiswahili')->assertSeeHtml('max="224"')
        ->assertSeeHtml(route('library.hisnul-muslim.swahili.pdf'))
        ->assertSee('Zoom in')->assertSee('Fullscreen')->assertSee('Close reader');

    $this->get(route('library.hisnul-muslim.reader'))->assertSeeHtml('max="193"');
});

test('the Kiswahili endpoint preserves the supplied PDF and supports partial downloads', function () {
    $response = $this->get(route('library.hisnul-muslim.swahili.pdf'));
    $response->assertOk()->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'inline; filename="hisnul-muslim-swahili.pdf"');
    expect(hash_file('sha256', $response->baseResponse->getFile()->getPathname()))
        ->toBe('d597fd92efe04195f5b2a441c949268f6d4b95513da367935d5a52e6b7269763');
    $this->withHeader('Range', 'bytes=0-99')->get(route('library.hisnul-muslim.swahili.pdf'))
        ->assertStatus(206)->assertHeader('Content-Length', '100');
});
