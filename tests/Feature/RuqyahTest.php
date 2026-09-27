<?php

use Illuminate\Support\Facades\Http;

test('guests can open the Ruqyah collection and its selected reading links', function () {
    Http::fake(['api.alquran.cloud/v1/surah' => Http::response(['data' => [
        ['number' => 1, 'name' => 'الفاتحة', 'englishName' => 'Al-Fatihah', 'englishNameTranslation' => 'The Opening', 'numberOfAyahs' => 7, 'revelationType' => 'Meccan'],
    ]])]);

    $this->get(route('ruqyah'))->assertOk()
        ->assertSee('الفاتحة')->assertSee('Ayat al-Kursi')->assertSee('An-Nas')
        ->assertSeeHtml(route('surah.show', ['number' => 2, 'ayah' => 255]))
        ->assertSeeHtml(route('surah.show', ['number' => 2, 'ayah' => 285]))
        ->assertSeeHtml(route('surah.show', ['number' => 114, 'ayah' => 1]));
    $this->assertGuest();
});

test('Ruqyah reading links remain available during an API outage', function () {
    Http::fake(['api.alquran.cloud/v1/surah' => Http::response([], 503)]);
    $this->get(route('ruqyah'))->assertOk()->assertSee('temporarily unavailable')
        ->assertSee('Al-Fatihah')->assertSee('Al-Ikhlas');
});

test('the homepage connects readers to Ruqyah with the supplied image', function () {
    $this->get(route('landing'))->assertOk()->assertSee('Ruqyah read')
        ->assertSeeHtml(route('ruqyah'))->assertSee('ruqraimage/', false)
        ->assertDontSee('Your next quiet moment starts here.');
});
