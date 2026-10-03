<?php

namespace App\Services;

class LibraryService
{
    /**
     * The 8 primary Islamic Library categories.
     *
     * @return array<string, array{name: string, arabic: string, description: string, icon: string, slug: string}>
     */
    public function categories(): array
    {
        return [
            'quran' => [
                'name' => 'Quran',
                'arabic' => 'القرآن الكريم',
                'description' => 'Read and reflect on all 114 Surahs, 30 Juz, and printed pages with word analysis and Tajweed.',
                'icon' => 'book',
                'slug' => 'quran',
                'count' => 114,
            ],
            'dua' => [
                'name' => 'Dua & Adhkar',
                'arabic' => 'الأدعية والأذكار',
                'description' => 'Authentic daily supplications, morning/evening remembrances, and invocations from the Sunnah.',
                'icon' => 'bookmark',
                'slug' => 'dua',
                'count' => 1,
            ],
            'hadith' => [
                'name' => 'Hadith',
                'arabic' => 'الحديث النبوي',
                'description' => 'Prophetic traditions including 40 Hadith An-Nawawi and Riyad as-Salihin.',
                'icon' => 'list',
                'slug' => 'hadith',
                'count' => 2,
            ],
            'aqeedah' => [
                'name' => 'Aqeedah',
                'arabic' => 'العقيدة الإسلامية',
                'description' => 'Foundations of Islamic creed, Tawheed (monotheism), and sound belief.',
                'icon' => 'check-circle',
                'slug' => 'aqeedah',
                'count' => 2,
            ],
            'fiqh' => [
                'name' => 'Fiqh',
                'arabic' => 'الفقه الإسلامي',
                'description' => 'Islamic jurisprudence, rules of purification, prayer, fasting, and transactions.',
                'icon' => 'grid',
                'slug' => 'fiqh',
                'count' => 2,
            ],
            'seerah' => [
                'name' => 'Seerah',
                'arabic' => 'السيرة النبوية',
                'description' => 'The inspiring life and biography of the Prophet Muhammad (ﷺ).',
                'icon' => 'user',
                'slug' => 'seerah',
                'count' => 1,
            ],
            'tafsir' => [
                'name' => 'Tafsir',
                'arabic' => 'التفسير وعلوم القرآن',
                'description' => 'Authoritative exegesis, context of revelation, and meanings of the Noble Quran.',
                'icon' => 'globe',
                'slug' => 'tafsir',
                'count' => 2,
            ],
            'other' => [
                'name' => 'Other Islamic Books',
                'arabic' => 'كتب إسلامية عامة',
                'description' => 'Spiritual purification (Tazkiyah), manners (Adab), and family guidance.',
                'icon' => 'book',
                'slug' => 'other',
                'count' => 2,
            ],
        ];
    }

    /**
     * Curated catalog of Islamic books available or planned.
     *
     * @return array<int, array{
     *     id: string,
     *     slug: string,
     *     title: string,
     *     arabicTitle: string,
     *     swahiliTitle: string,
     *     author: string,
     *     category: string,
     *     categorySlug: string,
     *     description: string,
     *     status: 'available'|'coming_soon',
     *     themeColor: string,
     *     pagesCount?: int
     * }>
     */
    public function catalog(): array
    {
        return [
            [
                'id' => 'hisn-al-muslim',
                'slug' => 'hisn-al-muslim',
                'title' => 'Hisn al-Muslim',
                'arabicTitle' => 'حِصْنُ الْمُسْلِمِ',
                'swahiliTitle' => 'Ngome ya Muislamu',
                'author' => 'Sheikh Sa\'id bin Ali bin Wahf Al-Qahtani',
                'category' => 'Dua & Adhkar',
                'categorySlug' => 'dua-adhkar',
                'description' => 'Dua na Adhkar sahihi za kila siku kutoka katika Qur\'ani na Sunnah na tafsiri ya Kiswahili.',
                'status' => 'available',
                'themeColor' => '#188691',
                'pagesCount' => 21,
            ],
            [
                'id' => 'arbaeen-nawawi',
                'slug' => 'arbaeen-nawawi',
                'title' => 'Al-Arba\'in An-Nawawiyyah',
                'arabicTitle' => 'الأربعون النووية',
                'swahiliTitle' => 'Hadithi 40 za An-Nawawi',
                'author' => 'Imam Yahya bin Sharaf An-Nawawi',
                'category' => 'Hadith',
                'categorySlug' => 'hadith',
                'description' => 'Hadithi 40 teule zenye misingi mikubwa ya sheria na maadili ya Uislamu.',
                'status' => 'coming_soon',
                'themeColor' => '#2A5FA0',
                'pagesCount' => 45,
            ],
            [
                'id' => 'kitab-at-tawheed',
                'slug' => 'kitab-at-tawheed',
                'title' => 'Kitab at-Tawheed',
                'arabicTitle' => 'كِتَابُ التَّوْحِيدِ',
                'swahiliTitle' => 'Kitabu cha Tawheed',
                'author' => 'Sheikh Muhammad bin Abd al-Wahhab',
                'category' => 'Aqeedah',
                'categorySlug' => 'aqeedah',
                'description' => 'Ufafanuzi wa haki ya Mwenyezi Mungu juu ya waja na misingi ya kumpwekesha.',
                'status' => 'coming_soon',
                'themeColor' => '#7A5B14',
                'pagesCount' => 67,
            ],
            [
                'id' => 'ar-raheeq-al-makhtum',
                'slug' => 'ar-raheeq-al-makhtum',
                'title' => 'Ar-Raheeq Al-Makhtum',
                'arabicTitle' => 'الرَّحِيقُ الْمَخْتُومُ',
                'swahiliTitle' => 'Kinywaji Kilichofungwa (Sira)',
                'author' => 'Safiur Rahman Mubarakpuri',
                'category' => 'Seerah',
                'categorySlug' => 'seerah',
                'description' => 'Historia iliyoshinda tuzo ya maisha ya Mtume Muhammad (ﷺ) kuanzia kuzaliwa hadi kuondoka kwake.',
                'status' => 'coming_soon',
                'themeColor' => '#5B3FBF',
                'pagesCount' => 120,
            ],
            [
                'id' => 'tafsir-as-sadi',
                'slug' => 'tafsir-as-sadi',
                'title' => 'Taysir al-Karim ar-Rahman',
                'arabicTitle' => 'تيسير الكريم الرحمن في تفسير كلام المنان',
                'swahiliTitle' => 'Tafsir as-Sa\'di',
                'author' => 'Sheikh Abdur-Rahman bin Nasir as-Sa\'di',
                'category' => 'Tafsir',
                'categorySlug' => 'tafsir',
                'description' => 'Tafsir iliyo wazi, nyepesi kueleweka na inayozingatia mafunzo ya kiroho na kimaadili ya aya za Qur\'an.',
                'status' => 'coming_soon',
                'themeColor' => '#15803D',
                'pagesCount' => 180,
            ],
            [
                'id' => 'riyad-as-salihin',
                'slug' => 'riyad-as-salihin',
                'title' => 'Riyad as-Salihin',
                'arabicTitle' => 'رِيَاضُ الصَّالِحِينَ',
                'swahiliTitle' => 'Bustani ya Waja Wema',
                'author' => 'Imam An-Nawawi',
                'category' => 'Hadith',
                'categorySlug' => 'hadith',
                'description' => 'Mkusanyiko mpana wa Hadithi sahihi zinazohusu tabia njema, ibada, na malezi ya kiroho.',
                'status' => 'coming_soon',
                'themeColor' => '#0F766E',
                'pagesCount' => 250,
            ],
        ];
    }

    /**
     * Load full book data with structure, chapters, and generated pages.
     */
    public function getBook(string $slug): ?array
    {
        if ($slug !== 'hisn-al-muslim') {
            return null;
        }

        $dataFile = resource_path('data/library/hisn-al-muslim.php');

        if (! file_exists($dataFile)) {
            return null;
        }

        $book = require $dataFile;

        $book['pages'] = $this->generatePhysicalPages($book);

        return $book;
    }

    /**
     * Generates a sequence of physical book pages from chapters.
     * Page 1: Hardcover Front
     * Page 2: Inside Title Page
     * Page 3: Utangulizi & Muhtasari (Foreword)
     * Page 4: Orodha ya Yaliyomo (Table of Contents)
     * Page 5+: Chapters with Duas (1-2 chapters per page)
     * Final Page: Back Inside Cover / Closing
     *
     * @return array<int, array{
     *     pageNumber: int,
     *     type: 'cover'|'title'|'foreword'|'toc'|'content'|'backcover',
     *     header?: string,
     *     chapters?: array<int, array>
     * }>
     */
    protected function generatePhysicalPages(array $book): array
    {
        $pages = [];
        $p = 1;

        // Page 1: Cover
        $pages[] = [
            'pageNumber' => $p++,
            'type' => 'cover',
            'title' => $book['title'],
            'arabicTitle' => $book['arabicTitle'],
            'swahiliTitle' => $book['swahiliTitle'],
            'subtitle' => $book['subtitle'],
            'author' => $book['author'],
            'authorArabic' => $book['authorArabic'],
        ];

        // Page 2: Inside Title & Imprint
        $pages[] = [
            'pageNumber' => $p++,
            'type' => 'title',
            'title' => $book['title'],
            'arabicTitle' => $book['arabicTitle'],
            'swahiliTitle' => $book['swahiliTitle'],
            'author' => $book['author'],
            'edition' => $book['edition'] ?? 'Toleo Rasmi',
            'description' => $book['description'] ?? '',
        ];

        // Page 3: Utangulizi (Foreword)
        $pages[] = [
            'pageNumber' => $p++,
            'type' => 'foreword',
            'header' => 'Utangulizi',
            'foreword' => $book['foreword'] ?? null,
        ];

        // Page 4: Table of Contents
        $tocList = array_map(function ($c) {
            return [
                'id' => $c['id'],
                'number' => $c['number'],
                'title' => $c['title'],
                'arabicTitle' => $c['arabicTitle'],
            ];
        }, $book['chapters'] ?? []);

        $pages[] = [
            'pageNumber' => $p++,
            'type' => 'toc',
            'header' => 'Orodha ya Yaliyomo',
            'toc' => $tocList,
        ];

        // Pages 5+: Content Pages
        // Each page contains 1 chapter (or 2 if short)
        $chapters = $book['chapters'] ?? [];

        foreach ($chapters as $chapter) {
            $chapterPages = [];

            // If a chapter has many items (like Asubuhi na Jioni), split across pages
            $itemChunks = array_chunk($chapter['items'], 3);

            foreach ($itemChunks as $index => $items) {
                $pages[] = [
                    'pageNumber' => $p++,
                    'type' => 'content',
                    'header' => $chapter['title'],
                    'chapter' => [
                        'id' => $chapter['id'],
                        'number' => $chapter['number'],
                        'title' => $chapter['title'],
                        'englishTitle' => $chapter['englishTitle'],
                        'arabicTitle' => $chapter['arabicTitle'],
                        'items' => $items,
                        'isContinuation' => $index > 0,
                    ],
                ];
            }
        }

        // Final page: Back Cover
        $pages[] = [
            'pageNumber' => $p,
            'type' => 'backcover',
            'header' => 'Hitimisho',
            'title' => $book['title'],
            'arabicTitle' => $book['arabicTitle'],
        ];

        return $pages;
    }

    /**
     * Search within a book's chapters and duas.
     *
     * @return array<int, array{chapterId: int, chapterTitle: string, item: array, matchType: string}>
     */
    public function searchBook(string $slug, string $query): array
    {
        $book = $this->getBook($slug);

        if (! $book || trim($query) === '') {
            return [];
        }

        $needle = mb_strtolower(trim($query));
        $matches = [];

        foreach ($book['chapters'] as $chapter) {
            foreach ($chapter['items'] as $item) {
                $matched = false;
                $matchType = 'text';

                if (str_contains(mb_strtolower($chapter['title']), $needle)) {
                    $matched = true;
                    $matchType = 'chapter';
                } elseif (str_contains(mb_strtolower($item['swahili']), $needle)) {
                    $matched = true;
                    $matchType = 'swahili';
                } elseif (str_contains(mb_strtolower($item['english']), $needle)) {
                    $matched = true;
                    $matchType = 'english';
                } elseif (str_contains(mb_strtolower($item['transliteration']), $needle)) {
                    $matched = true;
                    $matchType = 'transliteration';
                } elseif (str_contains($item['arabic'], trim($query))) {
                    $matched = true;
                    $matchType = 'arabic';
                }

                if ($matched) {
                    $matches[] = [
                        'chapterId' => $chapter['id'],
                        'chapterNumber' => $chapter['number'],
                        'chapterTitle' => $chapter['title'],
                        'item' => $item,
                        'matchType' => $matchType,
                    ];
                }
            }
        }

        return $matches;
    }
}
