<?php

use App\Livewire\Home;
use App\Livewire\Juz;
use App\Livewire\Reading;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function fakeGuestReading(): void
{
    $meta = ['number' => 1, 'name' => 'الفاتحة', 'englishName' => 'Al-Fatihah', 'englishNameTranslation' => 'The Opening', 'revelationType' => 'Meccan', 'numberOfAyahs' => 1];
    $ayah = ['number' => 1, 'numberInSurah' => 1, 'text' => 'بسم الله', 'juz' => 1, 'page' => 1, 'surah' => $meta];
    Http::fake([
        'api.alquran.cloud/v1/surah' => Http::response(['data' => [$meta]]),
        'api.alquran.cloud/v1/surah/1/editions/*' => Http::response(['data' => [
            [...$meta, 'ayahs' => [$ayah]],
            ['ayahs' => [['numberInSurah' => 1, 'text' => 'Bismillah']]],
            ['ayahs' => [['numberInSurah' => 1, 'text' => 'In the name of Allah.']]],
        ]]),
        'api.alquran.cloud/v1/juz/1/*' => Http::response(['data' => ['number' => 1, 'ayahs' => [$ayah]]]),
        'api.alquran.cloud/v1/page/1/*' => Http::response(['data' => ['number' => 1, 'ayahs' => [$ayah]]]),
        'api.quran.com/api/v4/verses/*' => Http::response(['verses' => []]),
        'api.quran.com/api/v4/quran/verses/*' => Http::response(['verses' => []]),
    ]);
}

test('a guest can open the full list and every type of reader without an account', function (string $route, array $parameters) {
    fakeGuestReading();
    $this->get(route($route, $parameters))->assertOk()->assertSee('Al-Fatihah');
    $this->assertGuest();
    expect(User::count())->toBe(0);
})->with([
    ['home', []], ['surah.show', [1]], ['juz.show', [1]], ['page.show', [1]],
]);

test('guest reading preferences and progress persist in the session', function () {
    fakeGuestReading();
    Livewire::test(Reading::class, ['number' => 1])->call('setTranslationLanguage', 'sw')->call('toggleTajweed');
    Livewire::test(Juz::class, ['number' => 1])->assertSet('translationLanguage', 'sw')->assertSet('tajweedEnabled', true);
    Livewire::test(Home::class)->assertSee('Continue reading')->assertSee('Ayah 1');
    expect(session('reading.progress'))->toBe(['surah' => 1, 'ayah' => 1]);
    expect(User::count())->toBe(0);
});

test('guest bookmarks prompt sign-in without creating records', function () {
    fakeGuestReading();
    Livewire::test(Reading::class, ['number' => 1])->call('toggleBookmark', 1)->assertRedirect(route('login'));
    expect(session('url.intended'))->toBe(route('surah.show', ['number' => 1, 'ayah' => 1]));
    expect(Bookmark::count())->toBe(0);
    expect(User::count())->toBe(0);
});

test('guest access does not expose private account pages', function () {
    $this->get(route('bookmarks'))->assertRedirect(route('login'));
    $this->get(route('profile'))->assertRedirect(route('login'));
});
