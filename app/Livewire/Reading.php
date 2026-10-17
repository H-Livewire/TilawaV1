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
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Reading extends Component
{
    #[Locked]
    public int $number;

    /** @var 'ayat'|'mushaf' */
    #[Locked]
    public string $mode = 'ayat';

    /** Ayat-mode pagination: long surahs (e.g. Al-Baqarah, 286 ayahs) are shown in chunks. */
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

    #[Locked]
    public int $physicalPage = 1;

    #[Locked]
    public int $focusedAyah = 1;

    public function mount(int $number): void
    {
        abort_unless($number >= 1 && $number <= 114, 404);

        $this->number = $number;

        $preferredLanguage = app(ReadingState::class)->language();

        if (array_key_exists($preferredLanguage, app(QuranService::class)->supportedTranslationLanguages())) {
            $this->translationLanguage = $preferredLanguage;
        }

        $this->tajweedEnabled = app(ReadingState::class)->tajweedEnabled();

        try {
            // Priming these here warms QuranService's cache for the rest of
            // this request/lifecycle, so render() and every computed prop
            // below reuse the result instead of hitting the API again.
            $this->surahList;
            $target = request()->query('ayah', 1);
            abort_unless(is_scalar($target) && filter_var($target, FILTER_VALIDATE_INT) !== false, 404);
            $target = (int) $target;
            $ayah = collect($this->surah['ayahs'])->firstWhere('number', $target);
            abort_unless($ayah, 404);
            $this->page = (int) ceil($target / $this->perPage);
            $this->focusedAyah = $target;
            $this->physicalPage = $ayah['page'];
            if (request()->query('view', session('reading.mode', 'ayat')) === 'mushaf') {
                $this->mode = 'mushaf';
                session(['reading.mode' => 'mushaf']);
                $requestedPage = request()->query('mp', $this->physicalPage);
                abort_unless(is_scalar($requestedPage) && filter_var($requestedPage, FILTER_VALIDATE_INT) !== false && $requestedPage >= 1 && $requestedPage <= 604, 404);
                if (! $this->openMushafPage((int) $requestedPage, false, $this->number)) {
                    $this->apiUnavailable = true;
                }
            } else {
                $this->recordLastRead($target);
            }
        } catch (QuranApiException) {
            $this->apiUnavailable = true;
        }
    }

    public function setMode(string $mode): void
    {
        $mode = in_array($mode, ['ayat', 'mushaf'], true) ? $mode : 'ayat';
        if ($this->mode === $mode) {
            return;
        }
        if ($mode === 'mushaf') {
            $anchor = collect($this->pagedAyahs)->firstWhere('number', $this->focusedAyah) ?? $this->pagedAyahs[0];
            $this->physicalPage = $anchor['page'];
        }
        $this->mode = $mode;
        session(['reading.mode' => $mode]);
        $this->syncReaderUrl();
    }

    /** Load complete physical pages, including adjoining surahs. Never filter by selected surah. */
    #[Renderless]
    public function loadMushafPages(array $numbers): array
    {
        abort_unless(count($numbers) <= 8, 422);
        $pages = [];
        foreach (array_unique($numbers) as $number) {
            abort_unless(is_int($number) && $number >= 1 && $number <= 604, 422);
            try {
                $page = app(QuranService::class)->getPage($number, $this->translationLanguage);
            } catch (QuranApiException) {
                return [];
            }
            $words = $tajweed = [];
            try {
                $words = app(WordService::class)->getPageWords($number);
            } catch (QuranApiException) {
                // Optional annotations must not prevent reading the original text.
            }
            if ($this->tajweedEnabled) {
                try {
                    $tajweed = app(TajweedService::class)->getPageTajweed($number);
                } catch (QuranApiException) {
                    // Fall back to the uncoloured Uthmani text.
                }
            }
            $pages[$number] = view('components.mushaf-page', compact('page', 'words', 'tajweed'))->render();
        }

        return $pages;
    }

    public function openMushafPage(int $number, bool $updateUrl = true, ?int $preferredSurah = null): bool
    {
        abort_unless($number >= 1 && $number <= 604, 422);
        try {
            $data = app(QuranService::class)->getPage($number, $this->translationLanguage);
        } catch (QuranApiException) {
            return false;
        }
        $ayah = collect($data['ayahs'])->firstWhere('surahNumber', $preferredSurah) ?? $data['ayahs'][0];
        $this->physicalPage = $number;
        $this->focusedAyah = $ayah['number'];
        $this->number = $ayah['surahNumber'];
        unset($this->surah, $this->pagedAyahs, $this->wordsByAyah, $this->tajweedAyahs);
        $this->page = (int) ceil($ayah['number'] / $this->perPage);
        $this->recordLastRead($ayah['number']);
        if ($updateUrl) {
            $this->syncReaderUrl();
        }

        return true;
    }

    private function syncReaderUrl(): void
    {
        $parameters = ['number' => $this->number];
        if ($this->mode === 'mushaf') {
            $parameters += ['view' => 'mushaf', 'mp' => $this->physicalPage];
        } else {
            $parameters['ayah'] = $this->focusedAyah;
        }
        $this->dispatch('reader-location', url: route('surah.show', $parameters));
    }

    /**
     * Switch the ayah translation's language and remember the choice on the
     * user's account, so it carries over to every surah and every device.
     */
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

    public function nextPage(): void
    {
        if ($this->page < $this->totalPages()) {
            $this->page++;
            unset($this->pagedAyahs);
            $this->focusedAyah = $this->pagedAyahs[0]['number'] ?? 1;
            $this->recordLastRead($this->focusedAyah);
            $this->syncReaderUrl();
        }
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
            unset($this->pagedAyahs);
            $this->focusedAyah = $this->pagedAyahs[0]['number'] ?? 1;
            $this->recordLastRead($this->focusedAyah);
            $this->syncReaderUrl();
        }
    }

    public function nextMushafPage(): void
    {
        if ($this->physicalPage < 604) {
            $this->openMushafPage($this->physicalPage + 1);
        }
    }

    public function previousMushafPage(): void
    {
        if ($this->physicalPage > 1) {
            $this->openMushafPage($this->physicalPage - 1);
        }
    }

    /**
     * Toggle a bookmark on a single ayah for the current user.
     */
    public function toggleBookmark(int $ayahNumber): void
    {
        if (! Auth::check()) {
            session(['url.intended' => route('surah.show', ['number' => $this->number, 'ayah' => $ayahNumber])]);
            session()->flash('status', 'Sign in to save bookmarks. You can keep reading without an account.');
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $ayah = collect($this->surah['ayahs'])->firstWhere('number', $ayahNumber);

        if (! $ayah) {
            return;
        }

        $query = Auth::user()->bookmarks()
            ->where('surah_number', $this->number)
            ->where('ayah_number', $ayahNumber);

        if ($query->exists()) {
            $query->delete();

            return;
        }

        Auth::user()->bookmarks()->create([
            'surah_number' => $this->number,
            'surah_name' => $this->surah['englishName'],
            'ayah_number' => $ayahNumber,
            'ayah_text' => $ayah['arabic'],
        ]);
    }

    /**
     * Remember where the user left off, so the Home page can offer to resume.
     */
    protected function recordLastRead(int $ayahNumber): void
    {
        app(ReadingState::class)->recordProgress($this->number, $ayahNumber);
    }

    public function goToSurah(int $number): void
    {
        abort_unless($number >= 1 && $number <= 114, 404);

        $this->redirectRoute('surah.show', array_filter(['number' => $number, 'view' => $this->mode === 'mushaf' ? 'mushaf' : null]), navigate: true);
    }

    #[Computed]
    public function surah(): array
    {
        return app(QuranService::class)->getSurah($this->number, $this->translationLanguage);
    }

    #[Computed]
    public function surahList(): array
    {
        return app(QuranService::class)->getSurahList();
    }

    /** @return array<string, string> language code => label, for the translation-language picker. */
    #[Computed]
    public function translationLanguages(): array
    {
        return app(QuranService::class)->supportedTranslationLanguages();
    }

    /**
     * Tajweed-highlighted HTML per ayah number, or an empty array when the
     * layer is off or its API is unavailable — callers must fall back to
     * plain Arabic text in that case, never break the page over it.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function tajweedAyahs(): array
    {
        if (! $this->tajweedEnabled) {
            return [];
        }

        try {
            return app(TajweedService::class)->getSurahTajweed($this->number);
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
     * Word-by-word text/translation/transliteration per ayah number, for
     * the tap-a-word drawer. An empty array (API hiccup) simply means the
     * ayah renders as plain, non-word-tappable text — never breaks the page.
     *
     * @return array<int, array<int, array{position: int, arabic: string, translation: ?string, transliteration: ?string}>>
     */
    #[Computed]
    public function wordsByAyah(): array
    {
        try {
            return app(WordService::class)->getSurahWords($this->number);
        } catch (QuranApiException) {
            return [];
        }
    }

    /** @return array<string, string> language code => translator/tafsir credit, for the translation-language picker. */
    #[Computed]
    public function translatorCredits(): array
    {
        return app(QuranService::class)->translatorCredits();
    }

    /** Ayah numbers the current user has bookmarked within this surah. */
    #[Computed]
    public function bookmarkedAyahNumbers(): array
    {
        if (! Auth::check()) {
            return [];
        }

        return Auth::user()->bookmarks()
            ->where('surah_number', $this->number)
            ->pluck('ayah_number')
            ->all();
    }

    #[Computed]
    public function pagedAyahs(): array
    {
        return array_slice($this->surah['ayahs'], ($this->page - 1) * $this->perPage, $this->perPage);
    }

    public function totalPages(): int
    {
        return (int) max(1, ceil(count($this->surah['ayahs']) / $this->perPage));
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
                $this->surah;
                $this->surahList;
            } catch (QuranApiException) {
                $this->apiUnavailable = true;
            }
        }

        if ($this->apiUnavailable) {
            return view('livewire.quran-unavailable', ['backRoute' => 'home'])
                ->layout('layouts.app', ['title' => 'Tilawa — Unavailable']);
        }

        return view('livewire.reading', [
            'previousNumber' => $this->number > 1 ? $this->number - 1 : null,
            'nextNumber' => $this->number < 114 ? $this->number + 1 : null,
        ])->layout('layouts.app', [
            'title' => "Tilawa — {$this->surah['englishName']}",
        ]);
    }
}
