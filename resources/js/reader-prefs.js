// Per-device reading preferences for the ayah view (sizes and what to show).
// Quran state (language, mode, tajweed) stays in Reading.php; this is only presentation.
const KEY = 'tilawa.reader';
const ARABIC = ['1.25rem', '1.5rem', '1.875rem', '2.25rem', '2.75rem'];
const TRANSLATION = ['0.8125rem', '0.9375rem', '1.0625rem', '1.25rem', '1.5rem'];

document.addEventListener('alpine:init', () => {
    let saved = {};
    try { saved = JSON.parse(localStorage.getItem(KEY) ?? '{}') || {}; } catch { /* defaults apply */ }
    const level = (value, fallback) => (Number.isInteger(value) && value >= 0 && value <= 4 ? value : fallback);

    window.Alpine.store('reader', {
        arabicLevel: level(saved.arabicLevel, 1),
        translationLevel: level(saved.translationLevel, 1),
        translation: saved.translation !== false,
        transliteration: saved.transliteration !== false,
        get arabicSize() { return ARABIC[this.arabicLevel]; },
        get translationSize() { return TRANSLATION[this.translationLevel]; },
        save() {
            try {
                localStorage.setItem(KEY, JSON.stringify({
                    arabicLevel: this.arabicLevel, translationLevel: this.translationLevel,
                    translation: this.translation, transliteration: this.transliteration,
                }));
            } catch { /* ignore */ }
        },
    });
});
