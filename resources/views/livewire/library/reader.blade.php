<div x-data="{
        soundMuted: false,
        playPageSound() {
            if (this.soundMuted) return;
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                if (ctx.state === 'suspended') { ctx.resume(); }

                const bufferSize = ctx.sampleRate * 0.12; // 120ms
                const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
                const data = buffer.getChannelData(0);
                for (let i = 0; i < bufferSize; i++) {
                    data[i] = (Math.random() * 2 - 1) * Math.exp(-i / (bufferSize * 0.35));
                }
                const noise = ctx.createBufferSource();
                noise.buffer = buffer;

                const filter = ctx.createBiquadFilter();
                filter.type = 'bandpass';
                filter.frequency.setValueAtTime(1400, ctx.currentTime);
                filter.Q.setValueAtTime(1.5, ctx.currentTime);

                const gain = ctx.createGain();
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.12);

                noise.connect(filter);
                filter.connect(gain);
                gain.connect(ctx.destination);
                noise.start();
            } catch (e) {}
        }
    }"
    x-init="
        try {
            soundMuted = localStorage.getItem('tilawa-book-sound-muted') === 'true';
        } catch (e) {}
        $watch('soundMuted', val => {
            try { localStorage.setItem('tilawa-book-sound-muted', String(val)); } catch (e) {}
        });
    "
    @keydown.arrow-right.window="$wire.nextPage(); playPageSound();"
    @keydown.arrow-left.window="$wire.previousPage(); playPageSound();"
    @keydown.escape.window="$wire.set('tocOpen', false); $wire.set('searchOpen', false)"
    class="min-h-screen bg-[#EDE7D9] dark:bg-[#0c1417] flex flex-col justify-between selection:bg-tilawa-amber selection:text-tilawa-ink">

    {{-- Top App Bar --}}
    <header class="sticky top-0 z-40 border-b border-tilawa-line/80 bg-tilawa-surface/95 backdrop-blur-md px-4 py-3 sm:px-8">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
            {{-- Left: Back to library & book title --}}
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('library.index') }}" wire:navigate aria-label="Rudi maktabani"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-tilawa-line/60 text-tilawa-ink transition hover:bg-tilawa-teal hover:text-white dark:bg-white/10 dark:text-white">
                    <x-ui.icon name="arrow-left" class="h-4 w-4" />
                </a>

                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="truncate text-sm font-bold text-tilawa-ink sm:text-base">{{ $this->book['title'] }}</span>
                        <span class="hidden rounded-md bg-tilawa-teal/15 px-2 py-0.5 text-[10px] font-bold text-tilawa-teal-dark dark:text-tilawa-teal-light md:inline-block">Kitabu Halisi</span>
                    </div>
                    <div class="truncate text-xs text-tilawa-sub hidden sm:block">
                        {{ $this->book['swahiliTitle'] }} &middot; {{ $this->book['author'] }}
                    </div>
                </div>
            </div>

            {{-- Center: Page & Chapter Info --}}
            <div class="hidden lg:flex items-center gap-2">
                <button type="button" wire:click="$set('tocOpen', true)"
                    class="flex items-center gap-1.5 rounded-full border border-tilawa-line bg-tilawa-paper px-3 py-1 text-xs font-semibold text-tilawa-ink hover:border-tilawa-teal transition">
                    <x-ui.icon name="list" class="h-3.5 w-3.5 text-tilawa-teal-dark" />
                    <span>Yaliyomo (Chapters)</span>
                </button>
                <span class="text-xs font-bold text-tilawa-sub">
                    Ukurasa {{ $page }} ya {{ $this->totalPages() }}
                </span>
            </div>

            {{-- Right Controls --}}
            <div class="flex items-center gap-1.5 sm:gap-2">
                {{-- Search in book button --}}
                <button type="button" wire:click="$set('searchOpen', true)" title="Tafuta katika kitabu"
                    class="flex h-9 w-9 items-center justify-center rounded-xl border border-tilawa-line bg-tilawa-surface text-tilawa-sub hover:text-tilawa-teal-dark hover:border-tilawa-teal transition">
                    <x-ui.icon name="search" class="h-4 w-4" />
                </button>

                {{-- Table of Contents (Mobile & Tablet) --}}
                <button type="button" wire:click="$set('tocOpen', true)" title="Orodha ya Yaliyomo"
                    class="lg:hidden flex h-9 w-9 items-center justify-center rounded-xl border border-tilawa-line bg-tilawa-surface text-tilawa-sub hover:text-tilawa-teal-dark hover:border-tilawa-teal transition">
                    <x-ui.icon name="list" class="h-4 w-4" />
                </button>

                {{-- Spread / Single Toggle (Desktop) --}}
                <button type="button" wire:click="setViewMode('{{ $viewMode === 'spread' ? 'single' : 'spread' }}')" title="{{ $viewMode === 'spread' ? 'Badilisha kuwa Ukurasa Mmoja' : 'Badilisha kuwa Kurasa Mbili' }}"
                    class="hidden md:flex items-center gap-1 rounded-xl border border-tilawa-line bg-tilawa-surface px-2.5 py-1.5 text-xs font-semibold text-tilawa-ink hover:border-tilawa-teal transition">
                    <x-ui.icon name="grid" class="h-3.5 w-3.5 text-tilawa-sub" />
                    <span class="text-[11px]">{{ $viewMode === 'spread' ? 'Kurasa 2' : 'Ukurasa 1' }}</span>
                </button>

                {{-- Sound toggle --}}
                <button type="button" @click="soundMuted = !soundMuted" :title="soundMuted ? 'Washa sauti ya kitabu' : 'Zima sauti ya kitabu'"
                    class="flex h-9 w-9 items-center justify-center rounded-xl border border-tilawa-line bg-tilawa-surface text-tilawa-sub hover:text-tilawa-ink transition">
                    <template x-if="!soundMuted">
                        <svg class="h-4 w-4 text-tilawa-teal-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                        </svg>
                    </template>
                    <template x-if="soundMuted">
                        <svg class="h-4 w-4 text-tilawa-sub/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                            <line x1="23" y1="9" x2="17" y2="15"></line>
                            <line x1="17" y1="9" x2="23" y2="15"></line>
                        </svg>
                    </template>
                </button>

                {{-- Print / PDF button --}}
                <a href="{{ route('library.book.print', $slug) }}" target="_blank" title="Chapisha au Hifadhi kama PDF"
                    class="flex h-9 items-center gap-1.5 rounded-xl border border-tilawa-line bg-tilawa-surface px-2.5 py-1.5 text-xs font-semibold text-tilawa-ink hover:border-tilawa-teal transition">
                    <svg class="h-4 w-4 text-tilawa-sub" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <span class="hidden sm:inline">Print / PDF</span>
                </a>

                {{-- Theme Switcher --}}
                <div class="rounded-full bg-tilawa-teal-dark"><x-ui.theme-selector /></div>
            </div>
        </div>
    </header>

    {{-- Main Book Stage --}}
    <main class="relative flex-1 flex items-center justify-center p-3 sm:p-6 md:p-8 lg:p-10 overflow-hidden">
        {{-- Side Navigation Buttons (Desktop) --}}
        <button type="button" wire:click="previousPage" @click="playPageSound()" @disabled($page === 1)
            aria-label="Ukurasa uliotangulia"
            class="hidden md:flex absolute left-4 z-20 h-12 w-12 items-center justify-center rounded-full bg-tilawa-surface/90 text-tilawa-ink shadow-lg backdrop-blur-sm border border-tilawa-line transition hover:scale-105 hover:bg-tilawa-teal hover:text-white disabled:opacity-25 disabled:pointer-events-none">
            <x-ui.icon name="arrow-left" class="h-6 w-6" />
        </button>

        <button type="button" wire:click="nextPage" @click="playPageSound()" @disabled($page >= $this->totalPages())
            aria-label="Ukurasa unaofuata"
            class="hidden md:flex absolute right-4 z-20 h-12 w-12 items-center justify-center rounded-full bg-tilawa-surface/90 text-tilawa-ink shadow-lg backdrop-blur-sm border border-tilawa-line transition hover:scale-105 hover:bg-tilawa-teal hover:text-white disabled:opacity-25 disabled:pointer-events-none">
            <x-ui.icon name="arrow-right" class="h-6 w-6" />
        </button>

        {{-- Book Container with 3D Perspective & Realistic Shadows --}}
        <div class="w-full max-w-5xl transition-all duration-300">
            @if ($page === 1)
                {{-- PAGE 1: PHYSICAL HARDCOVER FRONT --}}
                <div class="mx-auto max-w-md md:max-w-lg aspect-[1/1.42] rounded-r-2xl rounded-l-md shadow-2xl transition-transform duration-500 hover:scale-[1.01] relative overflow-hidden flex flex-col justify-between p-6 sm:p-10 border-4 border-[#C89B3C]/80"
                    style="background: radial-gradient(circle at 30% 30%, #1e5257 0%, #0f3236 70%, #081d20 100%);">

                    {{-- Left Book Spine Realistic Crease --}}
                    <div class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-black/40 via-black/10 to-transparent pointer-events-none"></div>
                    <div class="absolute left-7 top-0 bottom-0 w-[2px] bg-[#C89B3C]/30 pointer-events-none"></div>

                    {{-- Golden Islamic Geometric Ornaments --}}
                    <div class="absolute inset-3 border-2 border-[#C89B3C]/50 rounded-r-xl rounded-l-sm pointer-events-none"></div>
                    <div class="absolute inset-5 border border-[#C89B3C]/30 rounded-r-lg pointer-events-none"></div>

                    {{-- Top Islamic Header --}}
                    <div class="text-center pt-4 relative z-10">
                        <div class="font-arabic text-sm text-[#E6C687] tracking-widest font-bold">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
                        <div class="mt-1 text-[11px] uppercase tracking-[0.25em] text-[#C89B3C] font-extrabold">Al-Qur'an & Sunnah</div>
                    </div>

                    {{-- Center Medallion with Gilded Foil Calligraphy --}}
                    <div class="relative z-10 text-center my-auto py-6">
                        <div class="mx-auto w-24 h-24 mb-4 rounded-full border-2 border-[#C89B3C] flex items-center justify-center bg-[#0d2a2d] shadow-inner">
                            <span class="font-arabic text-3xl text-[#E6C687] font-bold">حصن</span>
                        </div>
                        <h1 class="font-arabic text-4xl sm:text-5xl font-extrabold text-[#F7DE9A] tracking-wide leading-tight drop-shadow-md">
                            حِصْنُ الْمُسْلِمِ
                        </h1>
                        <div class="mt-3 text-lg sm:text-xl font-extrabold text-white tracking-wider">
                            HISN AL-MUSLIM
                        </div>
                        <div class="mt-1 text-xs sm:text-sm font-semibold text-[#E6C687]/90 tracking-wide">
                            Ngome ya Muislamu
                        </div>
                        <div class="mt-3 mx-auto max-w-xs text-[11px] sm:text-xs text-white/75 leading-relaxed">
                            {{ $this->book['subtitle'] }}
                        </div>
                    </div>

                    {{-- Bottom Author & Opening CTA --}}
                    <div class="relative z-10 text-center pb-2">
                        <div class="text-[11px] text-[#C89B3C] uppercase tracking-wider font-semibold">Mkusanyaji:</div>
                        <div class="text-xs sm:text-sm font-bold text-white tracking-wide">
                            {{ $this->book['author'] }}
                        </div>
                        <div class="font-arabic text-xs text-[#E6C687] mt-0.5">
                            {{ $this->book['authorArabic'] }}
                        </div>

                        {{-- Open Book Button --}}
                        <div class="mt-6">
                            <button type="button" wire:click="nextPage" @click="playPageSound()"
                                class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#D9AB5A] to-[#B8892F] px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-[#081d20] shadow-lg transition hover:scale-105 hover:brightness-110">
                                <span>Fungua Kitabu (Open Book)</span>
                                <x-ui.icon name="arrow-right" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            @else
                {{-- OPEN PHYSICAL BOOK: SPREAD OR SINGLE VIEW --}}
                <div class="bg-[#FCF8EE] dark:bg-[#152227] text-tilawa-ink rounded-2xl shadow-2xl border border-[#D5CBB5] dark:border-[#22353c] overflow-hidden flex flex-col md:flex-row relative">

                    {{-- Center Book Spine Effect (Spread mode) --}}
                    @if ($viewMode === 'spread' && $this->companionPageData)
                        <div class="hidden md:block absolute left-1/2 top-0 bottom-0 w-10 -ml-5 bg-gradient-to-r from-black/10 via-black/25 to-black/10 z-10 pointer-events-none shadow-inner"></div>
                        <div class="hidden md:block absolute left-1/2 top-0 bottom-0 w-[1px] bg-black/30 z-10 pointer-events-none"></div>
                    @endif

                    {{-- LEFT PAGE (or Sole Page in single mode) --}}
                    <div class="flex-1 p-6 sm:p-10 md:p-12 min-h-[560px] md:min-h-[640px] flex flex-col justify-between relative bg-gradient-to-r from-stone-100/40 via-transparent to-stone-200/20 dark:from-black/20 dark:to-transparent">
                        <div>
                            {{-- Running Page Header --}}
                            <div class="flex items-center justify-between border-b border-tilawa-line pb-3 text-xs text-tilawa-sub font-semibold">
                                <span>{{ $this->book['title'] }}</span>
                                <span class="font-arabic text-sm text-tilawa-gold">{{ $this->currentPageData['header'] ?? $this->book['arabicTitle'] }}</span>
                            </div>

                            {{-- Render Page Content --}}
                            <div class="mt-6">
                                @include('livewire.library.partials.page-content', ['p' => $this->currentPageData])
                            </div>
                        </div>

                        {{-- Page Number Footer --}}
                        <div class="mt-8 pt-3 border-t border-tilawa-line/60 flex items-center justify-between text-xs text-tilawa-sub">
                            <span>Ukurasa {{ $this->currentPageData['pageNumber'] ?? $page }}</span>
                            @if ($this->currentPageData['type'] === 'content')
                                <span class="text-[11px] font-medium text-tilawa-gold">Hisn al-Muslim</span>
                            @endif
                        </div>
                    </div>

                    {{-- RIGHT PAGE (Dual-Spread Mode) --}}
                    @if ($viewMode === 'spread' && $this->companionPageData)
                        <div class="hidden md:flex flex-1 p-6 sm:p-10 md:p-12 min-h-[640px] flex-col justify-between relative bg-gradient-to-l from-stone-100/40 via-transparent to-stone-200/20 dark:from-black/20 dark:to-transparent border-t md:border-t-0 md:border-l border-tilawa-line/60">
                            <div>
                                {{-- Running Page Header --}}
                                <div class="flex items-center justify-between border-b border-tilawa-line pb-3 text-xs text-tilawa-sub font-semibold">
                                    <span class="font-arabic text-sm text-tilawa-gold">{{ $this->companionPageData['header'] ?? $this->book['arabicTitle'] }}</span>
                                    <span>{{ $this->book['swahiliTitle'] }}</span>
                                </div>

                                {{-- Render Companion Page Content --}}
                                <div class="mt-6">
                                    @include('livewire.library.partials.page-content', ['p' => $this->companionPageData])
                                </div>
                            </div>

                            {{-- Page Number Footer --}}
                            <div class="mt-8 pt-3 border-t border-tilawa-line/60 flex items-center justify-between text-xs text-tilawa-sub">
                                <span class="text-[11px] font-medium text-tilawa-gold">Hisn al-Muslim</span>
                                <span>Ukurasa {{ $this->companionPageData['pageNumber'] }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </main>

    {{-- Bottom Reading Controls --}}
    <footer class="sticky bottom-0 z-40 border-t border-tilawa-line bg-tilawa-surface/95 backdrop-blur-md px-4 py-3 sm:px-8">
        <div class="mx-auto flex max-w-4xl items-center justify-between gap-4">
            {{-- Prev Page button --}}
            <button type="button" wire:click="previousPage" @click="playPageSound()" @disabled($page === 1)
                class="flex items-center gap-1.5 rounded-xl border border-tilawa-line bg-tilawa-surface px-4 py-2 text-xs font-bold text-tilawa-ink shadow-sm transition hover:border-tilawa-teal disabled:opacity-30 disabled:pointer-events-none">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                <span>Nyuma</span>
            </button>

            {{-- Slider scrubber / indicator --}}
            <div class="flex-1 max-w-xs sm:max-w-sm flex items-center gap-3">
                <input type="range" min="1" max="{{ $this->totalPages() }}" wire:model.live="page" @change="playPageSound()"
                    class="w-full accent-tilawa-teal-dark cursor-pointer h-1.5 rounded-lg bg-tilawa-line">
                <span class="text-xs font-bold text-tilawa-ink whitespace-nowrap">
                    {{ $page }} / {{ $this->totalPages() }}
                </span>
            </div>

            {{-- Next Page button --}}
            <button type="button" wire:click="nextPage" @click="playPageSound()" @disabled($page >= $this->totalPages())
                class="flex items-center gap-1.5 rounded-xl bg-tilawa-teal-dark px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-tilawa-teal disabled:opacity-30 disabled:pointer-events-none">
                <span>Mbele</span>
                <x-ui.icon name="arrow-right" class="h-4 w-4" />
            </button>
        </div>
    </footer>

    {{-- TABLE OF CONTENTS SLIDE-OVER DRAWER --}}
    <div x-cloak x-show="$wire.tocOpen" class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="toc-heading" role="dialog" aria-modal="true">
        <div x-show="$wire.tocOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @click="$wire.set('tocOpen', false)" class="fixed inset-0 bg-black/40 backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div x-show="$wire.tocOpen" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                class="w-screen max-w-md bg-tilawa-surface border-l border-tilawa-line shadow-2xl flex flex-col">

                {{-- TOC Header --}}
                <div class="p-6 border-b border-tilawa-line flex items-center justify-between bg-tilawa-paper">
                    <div>
                        <h2 id="toc-heading" class="text-lg font-bold text-tilawa-ink">Orodha ya Yaliyomo</h2>
                        <p class="text-xs text-tilawa-sub mt-0.5">Surah na Milango ya Hisn al-Muslim</p>
                    </div>
                    <button type="button" wire:click="$set('tocOpen', false)"
                        class="rounded-full p-2 text-tilawa-sub hover:bg-tilawa-line hover:text-tilawa-ink">
                        <x-ui.icon name="close" class="h-5 w-5" />
                    </button>
                </div>

                {{-- TOC List --}}
                <div class="flex-1 overflow-y-auto p-4 divide-y divide-tilawa-line/60">
                    {{-- Cover jump --}}
                    <button type="button" wire:click="goToPage(1); playPageSound();"
                        class="w-full py-3 px-3 rounded-xl flex items-center justify-between text-left hover:bg-tilawa-line/40 transition">
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-tilawa-teal/15 text-xs font-bold text-tilawa-teal-dark">1</span>
                            <span class="text-sm font-bold text-tilawa-ink">Jalada Kuu (Cover)</span>
                        </div>
                        <span class="text-xs text-tilawa-sub">Ukurasa 1</span>
                    </button>

                    @foreach ($this->chaptersWithPages as $ch)
                        <button type="button" wire:click="goToChapter({{ $ch['id'] }}); playPageSound();"
                            class="w-full py-3.5 px-3 rounded-xl flex items-center justify-between text-left hover:bg-tilawa-line/40 transition">
                            <div class="flex items-start gap-3 min-w-0 pr-2">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-tilawa-line text-xs font-bold text-tilawa-sub">
                                    {{ $ch['number'] }}
                                </span>
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-tilawa-ink truncate">{{ $ch['title'] }}</div>
                                    <div class="font-arabic text-xs text-tilawa-gold mt-0.5 truncate">{{ $ch['arabicTitle'] }}</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-tilawa-sub shrink-0">Ukurasa {{ $ch['pageNumber'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- SEARCH IN BOOK MODAL / DRAWER --}}
    <div x-cloak x-show="$wire.searchOpen" class="fixed inset-0 z-50 overflow-hidden" role="dialog" aria-modal="true">
        <div x-show="$wire.searchOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @click="$wire.set('searchOpen', false)" class="fixed inset-0 bg-black/40 backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div x-show="$wire.searchOpen" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                class="w-screen max-w-lg bg-tilawa-surface border-l border-tilawa-line shadow-2xl flex flex-col">

                {{-- Search Header --}}
                <div class="p-6 border-b border-tilawa-line bg-tilawa-paper">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-tilawa-ink">Tafuta Katika Kitabu</h2>
                        <button type="button" wire:click="$set('searchOpen', false)"
                            class="rounded-full p-2 text-tilawa-sub hover:bg-tilawa-line hover:text-tilawa-ink">
                            <x-ui.icon name="close" class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="flex items-center gap-2 rounded-xl border border-tilawa-line bg-tilawa-surface px-3 py-2.5 focus-within:ring-2 focus-within:ring-tilawa-teal-dark">
                        <x-ui.icon name="search" class="h-4 w-4 text-tilawa-sub" />
                        <input type="text" wire:model.live.debounce.250ms="search" placeholder="Andika neno la Kiswahili, Kiarabu, au Kiingereza..."
                            class="w-full border-none bg-transparent p-0 text-sm text-tilawa-ink placeholder:text-tilawa-sub focus:outline-none focus:ring-0">
                    </div>
                </div>

                {{-- Search Results --}}
                <div class="flex-1 overflow-y-auto p-4">
                    @if (trim($search) === '')
                        <div class="py-12 text-center text-xs text-tilawa-sub">
                            Andika neno lolote (mfano: "asubuhi", "usingizi", "wudhu", "istighfar") ili kutafuta dua husika.
                        </div>
                    @elseif (count($this->searchResults) === 0)
                        <div class="py-12 text-center text-xs text-tilawa-sub">
                            Hakuna dua iliyopatikana kwa neno "{{ $search }}".
                        </div>
                    @else
                        <div class="flex flex-col gap-3">
                            <div class="text-xs font-bold text-tilawa-sub px-1">Matokeo ({{ count($this->searchResults) }})</div>
                            @foreach ($this->searchResults as $res)
                                <div wire:click="goToChapter({{ $res['chapterId'] }}); playPageSound();"
                                    class="cursor-pointer rounded-xl border border-tilawa-line bg-tilawa-surface p-4 hover:border-tilawa-teal hover:shadow-sm transition">
                                    <div class="flex items-center justify-between text-xs font-bold text-tilawa-gold mb-1">
                                        <span>Mlango wa {{ $res['chapterNumber'] }}: {{ $res['chapterTitle'] }}</span>
                                        <span class="text-[11px] text-tilawa-sub">Fungua &rarr;</span>
                                    </div>
                                    <div class="font-arabic text-sm text-tilawa-ink line-clamp-1 mb-1">{{ $res['item']['arabic'] }}</div>
                                    <p class="text-xs text-tilawa-sub line-clamp-2 leading-relaxed">{{ $res['item']['swahili'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
