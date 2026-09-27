<?php

namespace App\Services;

use App\Exceptions\QuranApiException;
use App\Support\TajweedRules;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches Tajweed-highlighted Arabic text from Quran.com's public content
 * API (a separate provider from the alquran.cloud QuranService above) and
 * turns its `<tajweed class=...>` markup into safe, CSS-class-based HTML
 * spans (never inline colors, so highlighting keeps working — and keeps
 * its contrast — in both Light and Night Mode).
 *
 * This powers an optional, off-by-default layer — every caller is expected
 * to treat a failure here as "no highlighting available" and fall back to
 * plain Arabic text, never as a reason to break the reading page itself.
 */
class TajweedService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.tajweed.base_url');
    }

    /**
     * Tajweed-annotated ayahs for a whole surah, keyed by ayah number.
     *
     * Each entry is ['html' => whole-ayah HTML, 'words' => per-word HTML
     * fragments in reading order] so callers can render either the full
     * ayah or tap-target individual words while keeping Tajweed coloring.
     *
     * Only the plain per-ayah HTML is cached (see cachedHtml()) — the
     * per-word split is cheap to recompute on every call, and NOT caching
     * it keeps long surahs (Al-Baqarah's 286 ayahs, worst case) well clear
     * of MySQL's max_allowed_packet when the app's cache store is the
     * database driver.
     *
     * @return array<int, array{html: string, words: array<int, string>}>
     */
    public function getSurahTajweed(int $surahNumber): array
    {
        $htmlByAyah = $this->cachedHtml(
            "tajweed.surah.v4.{$surahNumber}",
            fn (): array => $this->fetchAndSanitize(['chapter_number' => $surahNumber], keyBy: 'ayah')
        );

        return $this->withWords($htmlByAyah);
    }

    /**
     * Tajweed-annotated ayahs for a whole juz, keyed by "surah:ayah".
     *
     * @return array<string, array{html: string, words: array<int, string>}>
     */
    public function getJuzTajweed(int $juzNumber): array
    {
        $htmlByKey = $this->cachedHtml(
            "tajweed.juz.v4.{$juzNumber}",
            fn (): array => $this->fetchAndSanitize(['juz_number' => $juzNumber], keyBy: 'verse_key')
        );

        return $this->withWords($htmlByKey);
    }

    public function getPageTajweed(int $number): array
    {
        return $this->withWords($this->cachedHtml(
            "tajweed.page.v4.{$number}",
            fn (): array => $this->fetchAndSanitize(['page_number' => $number], keyBy: 'verse_key')
        ));
    }

    /**
     * Cache wrapper for the (plain-HTML-only) payload. Deliberately routed
     * through the 'file' cache store rather than the app's default —
     * a whole surah's sanitized HTML can run to several hundred KB, which
     * is exactly the kind of blob a database cache row (and its
     * max_allowed_packet ceiling) isn't a good home for. File storage has
     * no such limit and this data is disposable/regenerable, so it's a
     * safe, narrow exception to the app's default cache store.
     */
    protected function cachedHtml(string $key, \Closure $resolve): array
    {
        return Cache::store('file')->rememberForever("tilawa-tajweed:{$key}", $resolve);
    }

    /**
     * @param  array<string, int>  $filter  A single Quran.com filter param, e.g. ['chapter_number' => 2].
     * @param  'ayah'|'verse_key'  $keyBy
     * @return array<int|string, string>
     */
    protected function fetchAndSanitize(array $filter, string $keyBy): array
    {
        $verses = $this->fetchVerses($filter);

        $result = [];

        foreach ($verses as $verse) {
            if ($keyBy === 'ayah') {
                [, $ayahNumber] = explode(':', $verse['verse_key']);
                $key = (int) $ayahNumber;
            } else {
                $key = $verse['verse_key'];
            }

            $result[$key] = $this->sanitize($verse['text_uthmani_tajweed'] ?? '');
        }

        return $result;
    }

    /**
     * @param  array<int|string, string>  $htmlByKey
     * @return array<int|string, array{html: string, words: array<int, string>}>
     */
    protected function withWords(array $htmlByKey): array
    {
        $result = [];

        foreach ($htmlByKey as $key => $html) {
            $result[$key] = [
                'html' => $html,
                'words' => $this->splitIntoWords($html),
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, int>  $filter  A single Quran.com filter param, e.g. ['chapter_number' => 2].
     * @return array<int, array{verse_key: string, text_uthmani_tajweed: string}>
     */
    protected function fetchVerses(array $filter): array
    {
        try {
            $response = Http::timeout(15)->get("{$this->baseUrl}/quran/verses/uthmani_tajweed", $filter);
        } catch (ConnectionException $e) {
            throw $this->tajweedUnavailable($filter, $e);
        }

        if ($response->failed()) {
            throw $this->tajweedUnavailable($filter, null, $response->status());
        }

        $verses = $response->json('verses');

        if (! is_array($verses)) {
            throw $this->tajweedUnavailable($filter, null, $response->status());
        }

        return $verses;
    }

    protected function tajweedUnavailable(array $filter, ?\Throwable $previous = null, ?int $status = null): QuranApiException
    {
        Log::warning('Tajweed API unavailable; falling back to plain Arabic text.', [
            'filter' => $filter,
            'status' => $status,
            'exception' => $previous?->getMessage(),
        ]);

        return new QuranApiException('Unable to fetch Tajweed-annotated text.', 0, $previous);
    }

    /**
     * Convert Quran.com's `<tajweed class=x>...</tajweed>` markup into safe
     * `<span class="tajweed-mark tajweed-group-{group}">` tags, dropping the
     * trailing ayah-number marker (we render our own number badge) and any
     * unrecognized tag. Colors live entirely in CSS (see app.css) so they
     * can respond to Night Mode — never baked in as inline styles.
     */
    protected function sanitize(string $html): string
    {
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);

        try {
            $document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $render = function (\DOMNode $node) use (&$render): string {
            if ($node instanceof \DOMText) {
                return htmlspecialchars($node->textContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }

            if (! $node instanceof \DOMElement) {
                return '';
            }

            $tag = strtolower($node->tagName);

            if (in_array($tag, ['script', 'style', 'iframe', 'object'], true)
                || ($tag === 'span' && $node->getAttribute('class') === 'end')) {
                return '';
            }

            $inner = '';
            foreach ($node->childNodes as $child) {
                $inner .= $render($child);
            }

            $rule = $node->getAttribute('class');
            if ($tag === 'tajweed' && isset(TajweedRules::RULES[$rule])) {
                return '<span class="tajweed-mark tajweed-group-'.TajweedRules::RULES[$rule]['group'].'" data-tajweed-rule="'.$rule.'">'.$inner.'</span>';
            }

            return $inner;
        };

        $body = $document->getElementsByTagName('body')->item(0);

        return $body ? $render($body) : '';
    }

    /**
     * Split a sanitized ayah's HTML into one fragment per word, on
     * whitespace, while keeping any `tajweed-mark` span that crosses a word
     * boundary well-formed by closing and reopening it in each fragment.
     *
     * @return array<int, string>
     */
    protected function splitIntoWords(string $html): array
    {
        $tokens = preg_split(
            '/(<span[^>]*>|<\/span>|\s+)/u',
            $html,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        if ($tokens === false) {
            return array_values(array_filter(preg_split('/\s+/u', $html) ?: []));
        }

        $words = [];
        $buffer = '';
        $openSpanTag = null;

        foreach ($tokens as $token) {
            if (preg_match('/^\s+$/u', $token) === 1) {
                if ($openSpanTag !== null) {
                    $buffer .= '</span>';
                }
                if (trim($buffer) !== '') {
                    $words[] = $buffer;
                }
                $buffer = $openSpanTag ?? '';

                continue;
            }

            if (preg_match('/^<span[^>]*>$/u', $token) === 1) {
                $openSpanTag = $token;
                $buffer .= $token;

                continue;
            }

            if ($token === '</span>') {
                $openSpanTag = null;
                $buffer .= $token;

                continue;
            }

            $buffer .= $token;
        }

        if (trim($buffer) !== '') {
            if ($openSpanTag !== null) {
                $buffer .= '</span>';
            }
            $words[] = $buffer;
        }

        return $words;
    }
}
