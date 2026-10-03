<?php

namespace App\Livewire;

use App\Exceptions\QuranApiException;
use App\Services\QuranService;
use App\Services\ReadingState;
use App\Services\TajweedService;
use App\Services\WordService;
use App\Support\TajweedRules;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Juz extends Component
{
    protected string $section = 'juz';

    protected int $maxNumber = 30;

    #[Locked]
    public int $number;

    #[Locked]
    public int $page = 1;

    #[Locked]
    public int $perPage = 10;

    #[Locked]
    public bool $apiUnavailable = false;

    /** @var 'en'|'ar'|'sw' */
    #[Locked]
    public string $translationLanguage = 'en';

    /** Off by default — the reading page stays plain unless the user opts in. */
    #[Locked]
    public bool $tajweedEnabled = false;

    public function mount(int $number): void
    {
        abort_unless($number >= 1 && $number <= $this->maxNumber, 404);

        $this->number = $number;

        $preferredLanguage = app(ReadingState::class)->language();

        if (array_key_exists($preferredLanguage, app(QuranService::class)->supportedTranslationLanguages())) {
            $this->translationLanguage = $preferredLanguage;
        }

        $this->tajweedEnabled = app(ReadingState::class)->tajweedEnabled();

        try {
            $firstAyah = $this->pagedAyahs[0] ?? null;

            // Re-opening a juz/page the reader is already partway through
            // must not rewind their saved progress to its first ayah.
            if ($firstAyah && ! $this->hasProgressInThisSection()) {
                $this->recordLastRead($firstAyah['surahNumber'], $firstAyah['number']);
            }
        } catch (QuranApiException) {
            $this->apiUnavailable = true;
        }
    }

    public function nextPage(): void
    {
        if ($this->page < $this->totalPages()) {
            $this->page++;
            $this->recordCurrentPageAsLastRead();
        }
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
            $this->recordCurrentPageAsLastRead();
        }
    }

    protected function hasProgressInThisSection(): bool
    {
        $progress = app(ReadingState::class)->progress();

        if (! $progress) {
            return false;
        }

        return collect($this->juz['ayahs'])->contains(
            fn (array $ayah): bool => $ayah['surahNumber'] === (int) $progress['surah'] && $ayah['number'] === (int) $progress['ayah']
        );
    }

    protected function recordCurrentPageAsLastRead(): void
    {
        $firstAyah = $this->pagedAyahs[0] ?? null;

        if ($firstAyah) {
            $this->recordLastRead($firstAyah['surahNumber'], $firstAyah['number']);
        }
    }

    /**
     * Remember where the user left off, so the Home page can offer to resume
     * — same last-read fields Reading uses, so it doesn't matter whether
     * someone reads surah-by-surah or juz-by-juz.
     */
    protected function recordLastRead(int $surahNumber, int $ayahNumber): void
    {
        app(ReadingState::class)->recordProgress($surahNumber, $ayahNumber);
    }

    /**
     * Toggle a bookmark on a single ayah for the current user. Bookmarks are
     * stored by surah + ayah number, so this works the same regardless of
     * whether the ayah was reached via a surah page or a juz page.
     */
    public function toggleBookmark(int $surahNumber, int $ayahNumber): void
    {
        if (! Auth::check()) {
            session(['url.intended' => route('surah.show', ['number' => $surahNumber, 'ayah' => $ayahNumber])]);
            session()->flash('status', 'Sign in to save bookmarks. You can keep reading without an account.');
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $ayah = collect($this->juz['ayahs'])
            ->firstWhere(fn (array $a) => $a['surahNumber'] === $surahNumber && $a['number'] === $ayahNumber);

        if (! $ayah) {
            return;
        }

        // Delete-then-createOrFirst instead of exists()-then-create(): a rapid
        // double tap (two requests racing) can no longer trip the unique
        // (user, surah, ayah) index and surface a 500 error.
        $removed = Auth::user()->bookmarks()
            ->where('surah_number', $surahNumber)
            ->where('ayah_number', $ayahNumber)
            ->delete();

        if ($removed > 0) {
            return;
        }

        Auth::user()->bookmarks()->createOrFirst([
            'surah_number' => $surahNumber,
            'ayah_number' => $ayahNumber,
        ], [
            'surah_name' => $ayah['surahName'],
            'ayah_text' => $ayah['arabic'],
        ]);
    }

    public function setTranslationLanguage(string $language): void
    {
        if (! array_key_exists($language, app(QuranService::class)->supportedTranslationLanguages())) {
            return;
        }

        $this->translationLanguage = $language;

        app(ReadingState::class)->setLanguage($language);
    }

    public function toggleTajweed(): void
    {
        $this->tajweedEnabled = ! $this->tajweedEnabled;

        app(ReadingState::class)->setTajweed($this->tajweedEnabled);
    }

    public function goToJuz(int $number): void
    {
        abort_unless($number >= 1 && $number <= $this->maxNumber, 404);

        $this->redirectRoute($this->section.'.show', ['number' => $number], navigate: true);
    }

    #[Computed]
    public function juz(): array
    {
        return $this->section === 'page'
            ? app(QuranService::class)->getPage($this->number, $this->translationLanguage)
            : app(QuranService::class)->getJuz($this->number, $this->translationLanguage);
    }

    #[Computed]
    public function translationLanguages(): array
    {
        return app(QuranService::class)->supportedTranslationLanguages();
    }

    #[Computed]
    public function translatorCredits(): array
    {
        return app(QuranService::class)->translatorCredits();
    }

    /**
     * Tajweed-highlighted HTML keyed by "surah:ayah", or an empty array
     * when the layer is off or its API is unavailable — callers must fall
     * back to plain Arabic text in that case, never break the page over it.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function tajweedAyahs(): array
    {
        if (! $this->tajweedEnabled) {
            return [];
        }

        try {
            return $this->section === 'page'
                ? app(TajweedService::class)->getPageTajweed($this->number)
                : app(TajweedService::class)->getJuzTajweed($this->number);
        } catch (QuranApiException) {
            return [];
        }
    }

    /** @return array<string, array{name: string, arabicName: string, group: string, description: string, color: string}> */
    #[Computed]
    public function tajweedRules(): array
    {
        return TajweedRules::all();
    }

    /**
     * Word-by-word text/translation/transliteration keyed by "surah:ayah",
     * for the tap-a-word drawer. Empty means the layer degrades to plain,
     * non-word-tappable text — never breaks the page.
     *
     * @return array<string, array<int, array{position: int, arabic: string, translation: ?string, transliteration: ?string}>>
     */
    #[Computed]
    public function wordsByAyah(): array
    {
        try {
            return $this->section === 'page'
                ? app(WordService::class)->getPageWords($this->number)
                : app(WordService::class)->getJuzWords($this->number);
        } catch (QuranApiException) {
            return [];
        }
    }

    /** "{surahNumber}:{ayahNumber}" keys for every ayah in this juz the user has bookmarked. */
    #[Computed]
    public function bookmarkedPairs(): array
    {
        if (! Auth::check()) {
            return [];
        }

        $surahNumbers = collect($this->juz['ayahs'])->pluck('surahNumber')->unique()->values();

        return Auth::user()->bookmarks()
            ->whereIn('surah_number', $surahNumbers)
            ->get(['surah_number', 'ayah_number'])
            ->map(fn ($bookmark) => "{$bookmark->surah_number}:{$bookmark->ayah_number}")
            ->all();
    }

    #[Computed]
    public function pagedAyahs(): array
    {
        return array_slice($this->juz['ayahs'], ($this->page - 1) * $this->perPage, $this->perPage);
    }

    public function totalPages(): int
    {
        return (int) max(1, ceil(count($this->juz['ayahs']) / $this->perPage));
    }

    public function exception(\Throwable $e, \Closure $stopPropagation): void
    {
        if ($e instanceof QuranApiException) {
            $this->apiUnavailable = true;
            $stopPropagation();
        }
    }

    public function render()
    {
        if (! $this->apiUnavailable) {
            try {
                $this->juz;
            } catch (QuranApiException) {
                $this->apiUnavailable = true;
            }
        }

        if ($this->apiUnavailable) {
            return view('livewire.quran-unavailable', ['backRoute' => 'home'])
                ->layout('layouts.app', ['title' => 'Tilawa — Unavailable']);
        }

        return view('livewire.juz', [
            'section' => $this->section,
            'maxNumber' => $this->maxNumber,
            'previousNumber' => $this->number > 1 ? $this->number - 1 : null,
            'nextNumber' => $this->number < $this->maxNumber ? $this->number + 1 : null,
        ])->layout('layouts.app', [
            'title' => 'Tilawa — '.ucfirst($this->section).' '.$this->number,
        ]);
    }
}
