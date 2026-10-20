<?php

use App\Services\AyahOfTheDay;
use App\Services\QuranService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

function fakeAyahEditions(): void
{
    Http::fake(function ($request) {
        if (! preg_match('#/surah/(\d+)/editions#', $request->url(), $m)) {
            return Http::response([], 404);
        }
        $surah = (int) $m[1];
        $count = AyahOfTheDay::AYAH_COUNTS[$surah - 1];
        $ayahs = fn (string $prefix) => collect(range(1, $count))->map(fn ($n) => ['numberInSurah' => $n, 'text' => "{$prefix} {$surah}:{$n}"])->all();

        return Http::response(['code' => 200, 'data' => [
            [
                'number' => $surah, 'name' => 'سورة', 'englishName' => "Surah {$surah}", 'englishNameTranslation' => 'T', 'revelationType' => 'Meccan', 'numberOfAyahs' => $count,
                'ayahs' => collect(range(1, $count))->map(fn ($n) => ['number' => $n, 'numberInSurah' => $n, 'text' => "arabic {$surah}:{$n}", 'juz' => 1, 'page' => 1])->all(),
            ],
            ['ayahs' => $ayahs('translit')],
            ['ayahs' => $ayahs('translation')],
        ]]);
    });
}

test('the ayah counts add up to the whole Qur\'an', function () {
    expect(AyahOfTheDay::AYAH_COUNTS)->toHaveCount(114)
        ->and(array_sum(AyahOfTheDay::AYAH_COUNTS))->toBe(AyahOfTheDay::TOTAL_AYAHS);
});

test('every ayah is visited exactly once before any repeats', function () {
    $service = app(AyahOfTheDay::class);
    $start = Carbon::parse('2026-01-01');

    $seen = collect(range(0, AyahOfTheDay::TOTAL_AYAHS - 1))
        ->map(fn (int $day) => $service->indexFor($start->copy()->addDays($day)));

    expect($seen->unique())->toHaveCount(AyahOfTheDay::TOTAL_AYAHS);
});

test('the same day always gives the same ayah and consecutive days differ', function () {
    $service = app(AyahOfTheDay::class);

    expect($service->indexFor('2026-10-20'))->toBe($service->indexFor('2026-10-20'))
        ->and($service->indexFor('2026-10-20'))->not->toBe($service->indexFor('2026-10-21'));
});

test('indexes map to the right surah and ayah', function () {
    $service = app(AyahOfTheDay::class);

    expect($service->locate(0))->toBe([1, 1])
        ->and($service->locate(6))->toBe([1, 7])
        ->and($service->locate(7))->toBe([2, 1])
        ->and($service->locate(AyahOfTheDay::TOTAL_AYAHS - 1))->toBe([114, 6]);
});

test('forDate returns the verified text, translation, reference and a deep link', function () {
    fakeAyahEditions();
    $service = app(AyahOfTheDay::class);
    [$surah, $ayah] = $service->locate($service->indexFor('2026-10-20'));

    $result = $service->forDate('2026-10-20');

    expect($result)->toMatchArray([
        'date' => '2026-10-20',
        'surah' => $surah,
        'ayah' => $ayah,
        'arabic' => "arabic {$surah}:{$ayah}",
        'translation' => "translation {$surah}:{$ayah}",
        'reference' => "Surah {$surah} {$surah}:{$ayah}",
        'url' => "/surah/{$surah}?ayah={$ayah}",
    ]);
});

test('the JSON endpoint serves the ayah for the date the device sends', function () {
    fakeAyahEditions();

    $this->getJson(route('daily-ayah.today', ['date' => '2026-10-20', 'lang' => 'en']))
        ->assertOk()
        ->assertJsonStructure(['date', 'surah', 'ayah', 'surahName', 'arabic', 'translation', 'reference', 'url'])
        ->assertJsonPath('date', '2026-10-20');
});

test('the JSON endpoint rejects bad dates and languages', function () {
    $this->getJson(route('daily-ayah.today', ['date' => 'tomorrow']))->assertStatus(422);
    $this->getJson(route('daily-ayah.today', ['date' => '2026-10-20', 'lang' => 'xx']))->assertStatus(422);
});

test('the JSON endpoint reports 503 when the Qur\'an API is down', function () {
    Http::fake(fn () => Http::response('down', 500));

    $this->getJson(route('daily-ayah.today', ['date' => '2026-10-20']))->assertStatus(503);
});

test('the daily ayah page shows the verse and the reminder settings', function () {
    fakeAyahEditions();

    $this->get(route('daily-ayah'))
        ->assertOk()
        ->assertSee('Ayah of the Day')
        ->assertSee('Ayah of the Day reminder')
        ->assertSee('Allow')
        ->assertSee('Read in context');
});

test('the daily ayah page still renders when the Qur\'an API is down', function () {
    Http::fake(fn () => Http::response('down', 500));

    $this->get(route('daily-ayah'))->assertOk()->assertSee("Today's ayah can't be loaded", false);
});
