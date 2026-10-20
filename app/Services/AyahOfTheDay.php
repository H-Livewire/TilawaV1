<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Picks the Ayah of the Day.
 *
 * The verse text and translation always come from QuranService (the same
 * verified editions the reader uses) — nothing here generates or edits
 * Qur'anic text, it only chooses *which* ayah to show.
 *
 * Rotation: all 6,236 ayahs are visited exactly once per cycle in a fixed,
 * scattered order (a stride coprime with the total), so an ayah never repeats
 * until every other ayah has had its day, and neighbouring days rarely land
 * in the same surah.
 */
class AyahOfTheDay
{
    public const TOTAL_AYAHS = 6236;

    /** Coprime with 6236 (= 2² × 1559), which guarantees a full, repeat-free cycle. */
    private const STRIDE = 2749;

    private const OFFSET = 1117;

    /** Ayahs in each of the 114 surahs (fixed; sums to 6,236). */
    public const AYAH_COUNTS = [
        7, 286, 200, 176, 120, 165, 206, 75, 129, 109, 123, 111, 43, 52, 99, 128, 111, 110, 98, 135,
        112, 78, 118, 64, 77, 227, 93, 88, 69, 60, 34, 30, 73, 54, 45, 83, 182, 88, 75, 85,
        54, 53, 89, 59, 37, 35, 38, 29, 18, 45, 60, 49, 62, 55, 78, 96, 29, 22, 24, 13,
        14, 11, 11, 18, 12, 12, 30, 52, 52, 44, 28, 28, 20, 56, 40, 31, 50, 40, 46, 42,
        29, 19, 36, 25, 22, 17, 19, 26, 30, 20, 15, 21, 11, 8, 8, 19, 5, 8, 8, 11,
        11, 8, 3, 9, 5, 4, 7, 3, 6, 3, 5, 4, 5, 6,
    ];

    public function __construct(private QuranService $quran) {}

    /** 0-based position in the 6,236-ayah sequence for a calendar day. */
    public function indexFor(CarbonInterface|string $date): int
    {
        $day = Carbon::parse($date)->startOfDay();
        $days = (int) floor(Carbon::create(1970, 1, 1, 0, 0, 0, 'UTC')->diffInDays(
            Carbon::create($day->year, $day->month, $day->day, 0, 0, 0, 'UTC')
        ));

        return ($days * self::STRIDE + self::OFFSET) % self::TOTAL_AYAHS;
    }

    /**
     * Resolve a 0-based position to [surah, ayah].
     *
     * @return array{0: int, 1: int}
     */
    public function locate(int $index): array
    {
        foreach (self::AYAH_COUNTS as $position => $count) {
            if ($index < $count) {
                return [$position + 1, $index + 1];
            }
            $index -= $count;
        }

        throw new \OutOfRangeException('Ayah index is outside the Qur\'an.');
    }

    /**
     * @return array{
     *     date: string, surah: int, ayah: int, surahName: string, surahArabicName: string,
     *     arabic: string, translation: string, translator: string, language: string,
     *     reference: string, url: string,
     * }
     */
    public function forDate(CarbonInterface|string|null $date = null, string $language = 'en'): array
    {
        $date = $date === null ? Carbon::today() : Carbon::parse($date);
        [$surahNumber, $ayahNumber] = $this->locate($this->indexFor($date));

        $surah = $this->quran->getSurah($surahNumber, $language);
        $ayah = collect($surah['ayahs'])->firstWhere('number', $ayahNumber);
        $language = array_key_exists($language, QuranService::LANGUAGE_LABELS) ? $language : 'en';

        return [
            'date' => $date->toDateString(),
            'surah' => $surahNumber,
            'ayah' => $ayahNumber,
            'surahName' => $surah['englishName'],
            'surahArabicName' => $surah['name'],
            'arabic' => $ayah['arabic'],
            'translation' => $ayah['translation'],
            'translator' => QuranService::TRANSLATOR_CREDITS[$language] ?? '',
            'language' => $language,
            'reference' => "{$surah['englishName']} {$surahNumber}:{$ayahNumber}",
            'url' => route('surah.show', ['number' => $surahNumber, 'ayah' => $ayahNumber], false),
        ];
    }
}
