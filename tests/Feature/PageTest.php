<?php

use App\Livewire\Page;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function fakePrintedPage(int $number): void
{
    $ayahs = [
        ['number' => 7, 'numberInSurah' => 7, 'text' => 'نص الفاتحة', 'page' => $number, 'surah' => ['number' => 1, 'name' => 'الفاتحة', 'englishName' => 'Al-Fatihah']],
        ['number' => 8, 'numberInSurah' => 1, 'text' => 'الم', 'page' => $number, 'surah' => ['number' => 2, 'name' => 'البقرة', 'englishName' => 'Al-Baqarah']],
    ];
    Http::fake([
        "api.alquran.cloud/v1/page/{$number}/quran-uthmani" => Http::response(['data' => ['number' => $number, 'ayahs' => $ayahs]]),
        "api.alquran.cloud/v1/page/{$number}/en.transliteration" => Http::response(['data' => ['number' => $number, 'ayahs' => [['number' => 7, 'text' => 'First'], ['number' => 8, 'text' => 'Second']]]]),
        "api.alquran.cloud/v1/page/{$number}/en.sahih" => Http::response(['data' => ['number' => $number, 'ayahs' => [['number' => 8, 'text' => 'Second meaning'], ['number' => 7, 'text' => 'First meaning']]]]),
        "api.quran.com/api/v4/verses/by_page/{$number}*" => Http::response(['verses' => []]),
    ]);
}

test('printed pages are public and require valid page numbers', function () {
    fakePrintedPage(1);
    $this->get(route('page.show', 1))->assertOk();
    $this->actingAs(User::factory()->create())->get(route('page.show', 605))->assertNotFound();
    $this->get(route('page.show', 0))->assertNotFound();
});

test('a printed page includes both surahs and supports bookmarks', function () {
    fakePrintedPage(2);
    $user = User::factory()->create();
    Livewire::actingAs($user)->test(Page::class, ['number' => 2])
        ->assertSee('Page 2')->assertSee('Al-Fatihah')->assertSee('Al-Baqarah')
        ->assertSee('First meaning')->assertSee('Second meaning')
        ->call('toggleBookmark', 2, 1);
    expect($user->bookmarks()->first()->surah_number)->toBe(2);
    expect($user->fresh()->last_read_ayah)->toBe(7);
});

test('the final printed page has no next-page link', function () {
    fakePrintedPage(604);
    Livewire::actingAs(User::factory()->create())->test(Page::class, ['number' => 604])
        ->assertSee('Page 604')->assertDontSee('Next page')->assertSee(route('page.show', 603));
});
