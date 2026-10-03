<?php

use App\Livewire\Library\Index;
use App\Livewire\Library\Reader;
use Livewire\Livewire;

test('guests can browse the Islamic Library index page', function () {
    $response = $this->get(route('library.index'));

    $response->assertOk()
        ->assertSee('Islamic Library')
        ->assertSee('Maktaba ya Kiislamu')
        ->assertSee('Hisn al-Muslim (Ngome ya Muislamu)')
        ->assertSee('Dua & Adhkar')
        ->assertSee('Hadith')
        ->assertSee('Aqeedah');
});

test('library catalog can be filtered by category', function () {
    $component = Livewire::test(Index::class)
        ->call('setCategory', 'dua-adhkar')
        ->assertSee('Hisn al-Muslim');

    $duaSlugs = collect($component->get('books'))->pluck('slug')->all();
    expect($duaSlugs)->toContain('hisn-al-muslim');

    $component->call('setCategory', 'hadith')
        ->assertSee('An-Nawawi');

    $hadithSlugs = collect($component->get('books'))->pluck('slug')->all();
    expect($hadithSlugs)->not->toContain('hisn-al-muslim')
        ->toContain('arbaeen-nawawi');

    $component->call('setCategory', 'all');
    $allSlugs = collect($component->get('books'))->pluck('slug')->all();
    expect($allSlugs)->toContain('hisn-al-muslim')
        ->toContain('arbaeen-nawawi');
});

test('library catalog can be searched', function () {
    Livewire::test(Index::class)
        ->set('search', 'Ngome')
        ->assertSee('Hisn al-Muslim')
        ->set('search', 'NonExistentTitleXYZ')
        ->assertSee('Hakuna kitabu kilichopatikana');
});

test('guests can access the Hisn al-Muslim physical book reader', function () {
    $response = $this->get(route('library.book', 'hisn-al-muslim'));

    $response->assertOk()
        ->assertSee('Hisn al-Muslim')
        ->assertSee('Ngome ya Muislamu')
        ->assertSee('Al-Qahtani');
});

test('book reader supports page navigation and chapter jumping', function () {
    Livewire::test(Reader::class, ['slug' => 'hisn-al-muslim'])
        ->assertSet('page', 1)
        ->call('nextPage')
        ->assertSet('page', 2)
        ->call('previousPage')
        ->assertSet('page', 1)
        ->call('goToChapter', 2)
        ->assertSet('viewMode', 'spread')
        ->call('setViewMode', 'single')
        ->assertSet('viewMode', 'single');
});

test('book reader in-book search finds matching adhkar in Swahili and Arabic', function () {
    $component = Livewire::test(Reader::class, ['slug' => 'hisn-al-muslim'])
        ->set('search', 'amka');

    $results = $component->get('searchResults');
    expect($results)->not->toBeEmpty();
});

test('guests can view the print-optimized edition', function () {
    $response = $this->get(route('library.book.print', 'hisn-al-muslim'));

    $response->assertOk()
        ->assertSee('Hisn al-Muslim')
        ->assertSee('Chapisha / Hifadhi kama PDF')
        ->assertSee('Orodha ya Yaliyomo');
});

test('accessing a non-existent book returns 404', function () {
    $this->get('/library/unknown-book-slug')->assertNotFound();
});

test('site header includes Islamic Library dropdown navigation', function () {
    $response = $this->get(route('landing'));

    $response->assertOk()
        ->assertSee('Islamic Library')
        ->assertSee(route('library.index'))
        ->assertSee(route('library.book', 'hisn-al-muslim'));
});
