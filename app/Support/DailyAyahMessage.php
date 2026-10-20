<?php

namespace App\Support;

/**
 * Builds the notification text from the user's content choices. Mirrors
 * compose() in public/pwa/daily-ayah-core.js so a push looks the same as a
 * notification the browser builds itself. Verse text is passed through
 * untouched.
 */
class DailyAyahMessage
{
    /**
     * @param  array<string, mixed>|null  $ayah  AyahOfTheDay::forDate() result, or null when unavailable
     * @param  array{arabic: bool, translation: bool, reference: bool, simple: bool}  $prefs
     * @return array{title: string, body: string, url: string, lang: string}
     */
    public static function compose(?array $ayah, array $prefs): array
    {
        $title = 'Ayah of the Day';

        if ($ayah === null) {
            return ['title' => $title, 'body' => "Take a moment to read today's ayah.", 'url' => '/daily-ayah', 'lang' => 'en'];
        }

        $url = $ayah['url'];
        $showsDetail = $prefs['arabic'] || $prefs['translation'] || $prefs['reference'];

        if ($prefs['simple'] || ! $showsDetail) {
            return ['title' => $title, 'body' => 'Your daily ayah is ready. Take a moment to read it.', 'url' => $url, 'lang' => 'en'];
        }

        $lines = [];
        if ($prefs['arabic']) {
            $lines[] = $ayah['arabic'];
        }
        if ($prefs['translation']) {
            $lines[] = $prefs['arabic'] ? $ayah['translation'] : "“{$ayah['translation']}”";
        }
        if ($prefs['reference']) {
            $lines[] = "— {$ayah['reference']}";
        }

        return [
            'title' => $title,
            'body' => implode("\n", $lines),
            'url' => $url,
            'lang' => $prefs['arabic'] && ! $prefs['translation'] ? 'ar' : ($ayah['language'] ?? 'en'),
        ];
    }
}
