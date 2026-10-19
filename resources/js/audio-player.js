// Recitation player. Plays Quran.com's gapless chapter recording (Mishari
// Rashid al-`Afasy) and uses its per-ayah / per-word timings to highlight the
// ayah and word being recited. Ayah cards listen for "audio-state"; word
// spans are matched through their data-audio-word="surah:ayah:index" attribute.
const API = 'https://api.quran.com/api/v4/chapter_recitations';
const WORD_CLASS = 'audio-word-active';
const RECITERS = [
    { id: 'abdulbaset-mujawwad', name: 'AbdulBaset AbdulSamad', style: 'Mujawwad', qdc: null },
    { id: 'abdulbaset-murattal', name: 'AbdulBaset AbdulSamad', style: 'Murattal', qdc: null },
    { id: 'abdullah-ali-jabir', name: 'Abdullah Ali Jabir - beta', style: 'Murattal', qdc: null },
    { id: 'abdur-rahman-as-sudais', name: 'Abdur-Rahman as-Sudais', style: 'Murattal', qdc: null },
    { id: 'abu-bakr-al-shatri', name: 'Abu Bakr al-Shatri', style: 'Murattal', qdc: null },
    { id: 'ahmed-al-ajmy', name: 'Ahmed ibn Ali al-Ajmy - beta', style: 'Murattal', qdc: null },
    { id: 'bandar-baleela', name: 'Bandar Baleela - beta', style: 'Murattal', qdc: null },
    { id: 'hani-ar-rifai', name: 'Hani ar-Rifai', style: 'Murattal', qdc: null },
    { id: 'khalifah-al-tunaiji', name: 'Khalifah Al Tunaiji', style: 'Murattal', qdc: null },
    { id: 'maher-al-muaiqly', name: 'Maher al-Muaiqly - beta', style: 'Murattal', qdc: null },
    { id: 'mahmoud-khalil-al-husary', name: 'Mahmoud Khalil Al-Husary', style: 'Murattal', qdc: null },
    { id: 'mishari-al-afasy', name: 'Mishari Rashid al-`Afasy', style: 'Murattal', qdc: 7 },
    { id: 'mohamed-siddiq-al-minshawi', name: 'Mohamed Siddiq al-Minshawi', style: 'Murattal', qdc: null },
    { id: 'saad-al-ghamdi', name: 'Saad al-Ghamdi', style: 'Murattal', qdc: null },
];
const STORE = 'tilawa.audio';
const timingCache = new Map();

async function fetchChapter(qdc, surah) {
    const key = `${qdc}:${surah}`;
    if (timingCache.has(key)) return timingCache.get(key);
    const response = await fetch(`${API}/${qdc}/${surah}?segments=true`);
    if (!response.ok) throw new Error('Audio unavailable');
    const file = (await response.json()).audio_file;
    if (!file?.audio_url || !Array.isArray(file.timestamps)) throw new Error('Audio unavailable');
    const verses = {};
    for (const item of file.timestamps) {
        verses[Number(item.verse_key.split(':')[1])] = { from: item.timestamp_from, to: item.timestamp_to, segments: item.segments ?? [] };
    }
    const chapter = { key, url: file.audio_url, verses };
    timingCache.set(key, chapter);
    return chapter;
}

function audioPlayer({ surah, total, name }) {
    // Kept outside the reactive state: Alpine proxies would break media methods.
    let media = null;
    let chapter = null;
    let frame = 0;
    let activeWord = null;
    let paging = false;

    return {
        open: false,
        playing: false,
        loading: false,
        error: null,
        muted: false,
        menu: null, // null | 'main' | 'repeat' | 'experience' | 'speed' | 'reciter'
        surah,
        total,
        name,
        ayah: 1,
        repeatLeft: 0,
        reciter: 'mishari-al-afasy',
        speed: '1x',
        repeat: 'off',
        experience: 'continuous',
        reciters: RECITERS,
        speeds: ['0.75x', '1x', '1.25x', '1.5x', '2x'],
        repeats: [{ value: 'off', label: 'No repeat' }, { value: '2', label: 'Repeat 2 times' }, { value: '3', label: 'Repeat 3 times' }, { value: '5', label: 'Repeat 5 times' }, { value: 'inf', label: 'Repeat forever' }],
        experiences: [{ value: 'continuous', label: 'Continuous', hint: 'Keep playing the next ayah' }, { value: 'single', label: 'Single ayah', hint: 'Stop after each ayah' }],

        init() {
            try {
                const saved = JSON.parse(localStorage.getItem(STORE) ?? '{}');
                for (const key of ['speed', 'repeat', 'experience']) if (saved[key]) this[key] = saved[key];
                if (RECITERS.some(r => r.id === saved.reciter && r.qdc)) this.reciter = saved.reciter;
            } catch { /* storage unavailable: defaults apply */ }
            window.addEventListener('pagehide', () => this.teardown(), { once: true });
        },
        save() {
            try {
                localStorage.setItem(STORE, JSON.stringify({ reciter: this.reciter, speed: this.speed, repeat: this.repeat, experience: this.experience }));
            } catch { /* ignore */ }
        },
        emit() {
            window.dispatchEvent(new CustomEvent('audio-state', { detail: { surah: this.surah, ayah: this.open ? this.ayah : null, playing: this.playing } }));
        },
        get statusText() {
            if (this.error) return this.error;
            if (this.loading) return 'Loading…';
            return `${this.name} ${this.surah}:${this.ayah}`;
        },
        get repeatCount() {
            return this.repeat === 'inf' ? Infinity : this.repeat === 'off' ? 1 : Number(this.repeat);
        },

        // --- loading -------------------------------------------------------
        async load() {
            const reciter = RECITERS.find(r => r.id === this.reciter);
            const key = `${reciter.qdc}:${this.surah}`;
            if (chapter?.key === key && media) return;
            this.loading = true;
            try {
                chapter = await fetchChapter(reciter.qdc, this.surah);
            } catch (e) {
                chapter = null;
                throw e;
            }
            this.release();
            media = new Audio();
            media.preload = 'auto';
            media.src = chapter.url;
            media.muted = this.muted;
            media.playbackRate = this.rate();
            media.addEventListener('playing', () => { this.loading = false; this.error = null; });
            media.addEventListener('waiting', () => { this.loading = true; });
            media.addEventListener('canplay', () => { this.loading = false; });
            media.addEventListener('ended', () => this.ended());
            media.addEventListener('error', () => this.fail('Could not play this recitation'));
        },
        release() {
            if (!media) return;
            media.pause();
            media.removeAttribute('src');
            media.load();
            media = null;
        },
        fail(message) {
            this.error = message;
            this.loading = false;
            this.playing = false;
            cancelAnimationFrame(frame);
            this.emit();
        },
        rate() {
            return parseFloat(this.speed) || 1;
        },

        // --- transport -----------------------------------------------------
        async play(detail) {
            const resume = media && chapter && this.surah === detail.surah && this.ayah === detail.ayah && !media.ended
                && this.within(detail.ayah, media.currentTime * 1000);
            this.surah = detail.surah;
            this.name = detail.name;
            this.ayah = detail.ayah;
            this.open = true;
            this.menu = null;
            this.error = null;
            this.emit();
            try {
                await this.load();
            } catch {
                this.fail('Recitation is unavailable right now');
                return;
            }
            await this.start(this.ayah, !resume);
        },
        within(ayah, ms) {
            const verse = chapter?.verses[ayah];
            return !!verse && ms >= verse.from - 50 && ms <= verse.to + 50;
        },
        async start(ayah, seek = true, reset = true) {
            const verse = chapter?.verses[ayah];
            if (!verse || !media) return this.fail('Recitation is unavailable right now');
            if (seek) {
                media.currentTime = verse.from / 1000;
                if (reset) this.repeatLeft = this.repeatCount - 1;
            }
            media.playbackRate = this.rate();
            media.muted = this.muted;
            try {
                await media.play();
            } catch (e) {
                if (e?.name !== 'AbortError') this.fail('Press play to start the recitation');
                return;
            }
            this.playing = true;
            this.emit();
            this.scrollToAyah();
            cancelAnimationFrame(frame);
            const loop = () => { this.tick(); if (this.playing) frame = requestAnimationFrame(loop); };
            frame = requestAnimationFrame(loop);
        },
        toggle() {
            if (!media) return this.play({ surah: this.surah, ayah: this.ayah, name: this.name });
            if (this.playing) {
                media.pause();
                this.playing = false;
                cancelAnimationFrame(frame);
                this.emit();
            } else {
                this.start(this.ayah, !this.within(this.ayah, media.currentTime * 1000));
            }
        },
        step(direction) {
            const next = this.ayah + direction;
            if (next < 1 || next > this.total) return;
            this.ayah = next;
            this.repeatLeft = this.repeatCount - 1;
            const verse = chapter?.verses[next];
            if (verse && media) media.currentTime = verse.from / 1000;
            this.clearWord();
            this.emit();
            this.scrollToAyah();
        },
        close() {
            this.teardown();
            this.open = false;
            this.menu = null;
            this.emit();
        },
        destroy() {
            this.teardown();
        },
        teardown() {
            cancelAnimationFrame(frame);
            this.playing = false;
            this.clearWord();
            this.release();
            chapter = null;
        },
        ended() {
            // Last ayah of the chapter finished.
            if (this.repeatLeft > 0 && chapter?.verses[this.ayah]) {
                this.repeatLeft--;
                this.start(this.ayah, true, false);
                return;
            }
            this.playing = false;
            cancelAnimationFrame(frame);
            this.clearWord();
            this.emit();
        },
        toggleMute() {
            this.muted = !this.muted;
            if (media) media.muted = this.muted;
        },
        download() {
            this.menu = null;
            if (chapter?.url) window.open(chapter.url, '_blank', 'noopener');
        },
        choose(key, value) {
            this[key] = value;
            this.save();
            if (key === 'speed' && media) media.playbackRate = this.rate();
            if (key === 'repeat') this.repeatLeft = this.repeatCount - 1;
            this.menu = 'main';
        },

        // --- following the recitation -------------------------------------
        tick() {
            if (!media || !chapter) return;
            const t = media.currentTime * 1000;
            const verses = chapter.verses;
            let n = this.ayah;
            while (verses[n + 1] && t >= verses[n + 1].from) n++;
            while (verses[n - 1] && verses[n] && t < verses[n].from - 120) n--;
            if (n !== this.ayah) {
                if (this.repeatLeft > 0 && n > this.ayah) {
                    this.repeatLeft--;
                    media.currentTime = verses[this.ayah].from / 1000;
                    return;
                }
                const previous = this.ayah;
                this.ayah = n;
                this.repeatLeft = this.repeatCount - 1;
                this.clearWord();
                if (this.experience === 'single' && n > previous) {
                    media.pause();
                    media.currentTime = verses[n].from / 1000;
                    this.playing = false;
                    cancelAnimationFrame(frame);
                }
                this.emit();
                this.scrollToAyah();
                return;
            }
            const segment = verses[n].segments.find(s => t >= s[1] && t < s[2]);
            if (segment) this.highlight(n, segment[0] - 1);
        },
        highlight(ayah, index) {
            const key = `${this.surah}:${ayah}:${index}`;
            if (activeWord?.key === key && activeWord.el.isConnected && activeWord.el.classList.contains(WORD_CLASS)) return;
            this.clearWord();
            const el = document.querySelector(`[data-audio-word="${key}"]`);
            if (!el) return;
            el.classList.add(WORD_CLASS);
            activeWord = { key, el };
        },
        clearWord() {
            activeWord?.el.classList.remove(WORD_CLASS);
            activeWord = null;
        },
        scrollToAyah() {
            const reveal = () => document.getElementById(`ayah-${this.ayah}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (document.getElementById(`ayah-${this.ayah}`)) return reveal();
            if (paging || !this.$wire?.showAyah) return;
            // The ayah sits on another page of this surah: turn to it first.
            paging = true;
            this.$wire.showAyah(this.ayah).then(() => requestAnimationFrame(() => { this.emit(); reveal(); })).finally(() => { paging = false; });
        },
    };
}

document.addEventListener('alpine:init', () => window.Alpine.data('audioPlayer', audioPlayer));
