<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class ReadingState
{
    public function language(): string
    {
        return Auth::user()?->translation_language ?? session('reading.language', 'en');
    }

    public function tajweedEnabled(): bool
    {
        return (bool) (Auth::user()?->tajweed_enabled ?? session('reading.tajweed', false));
    }

    public function setLanguage(string $language): void
    {
        if (Auth::check()) {
            Auth::user()->forceFill(['translation_language' => $language])->save();
        } else {
            session(['reading.language' => $language]);
        }
    }

    public function setTajweed(bool $enabled): void
    {
        if (Auth::check()) {
            Auth::user()->forceFill(['tajweed_enabled' => $enabled])->save();
        } else {
            session(['reading.tajweed' => $enabled]);
        }
    }

    public function recordProgress(int $surah, int $ayah): void
    {
        if (Auth::check()) {
            Auth::user()->forceFill([
                'last_read_surah' => $surah,
                'last_read_ayah' => $ayah,
                'last_read_at' => now(),
            ])->save();
        } else {
            session(['reading.progress' => ['surah' => $surah, 'ayah' => $ayah]]);
        }
    }

    public function progress(): ?array
    {
        $user = Auth::user();

        if ($user) {
            return $user->last_read_surah
                ? ['surah' => $user->last_read_surah, 'ayah' => $user->last_read_ayah]
                : null;
        }

        return session('reading.progress');
    }
}
