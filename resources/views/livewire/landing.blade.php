<div class="bg-tilawa-surface">
    <x-ui.site-header />
    <main>
        <section class="relative overflow-hidden rounded-b-[2rem] bg-tilawa-paper sm:rounded-b-[4rem]">
            <div class="mx-auto grid max-w-6xl items-center gap-10 px-6 py-12 sm:px-8 sm:py-16 lg:grid-cols-[1.15fr_1fr] lg:gap-16 lg:px-10 lg:py-20">
                <div>
                    <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-tilawa-teal-dark dark:text-tilawa-teal-light"><span class="h-1.5 w-1.5 rounded-full bg-tilawa-teal-dark"></span>Your daily companion for the Qur'an</p>
                    <h1 class="mt-6 text-[2.7rem] font-extrabold leading-[1.15] tracking-tight text-tilawa-ink sm:text-6xl lg:text-[4.1rem]">Make room for<br>the <span class="text-tilawa-teal-dark dark:text-tilawa-teal-light">Qur'an.</span></h1>
                    <p class="mt-6 max-w-md text-base leading-8 text-tilawa-sub">A few ayahs. A quiet moment. Read and reflect with clear Arabic, translations, and gentle guidance, wherever you are in your journey.</p>
                    <div class="mt-8 flex flex-wrap items-center gap-5">
                        <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-3 rounded-full bg-tilawa-teal-dark px-6 py-3.5 text-sm font-bold text-white shadow-sm transition hover:brightness-110 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-tilawa-teal-dark dark:focus-visible:outline-tilawa-teal-light">Browse all surahs <x-ui.icon name="arrow-right" class="h-4 w-4" /></a>
                        <a href="{{ route('surah.show', 1) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-bold text-tilawa-ink underline decoration-tilawa-gold/50 underline-offset-8 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-tilawa-teal-dark dark:focus-visible:outline-tilawa-teal-light">Read Al-Fatihah</a>
                    </div>
                    <p class="mt-5 text-xs leading-6 text-tilawa-sub">No account needed. Just open a surah and begin.</p>
                    <div class="mt-10 flex gap-8 border-t border-tilawa-gold/20 pt-6 text-tilawa-ink">
                        <div><span class="text-2xl font-extrabold">114</span><span class="mt-1 block text-xs text-tilawa-sub">Surahs to explore</span></div>
                        <div><span class="text-2xl font-extrabold">3</span><span class="mt-1 block text-xs text-tilawa-sub">Translation options</span></div>
                        <div><span class="whitespace-nowrap text-xl sm:text-2xl font-extrabold">Your pace</span><span class="mt-1 block text-xs text-tilawa-sub">Every single day</span></div>
                    </div>
                </div>
                <div class="relative  lg:ml-auto">
                    
                    <div class="relative overflow-hidden ">
                        <img src="{{ asset('images/ChatGPT Image Sep 25, 2026, 06_31_28 PM.png') }}" alt="An open Qur'an on a wooden stand in warm light" >
                        <div class="absolute inset-x-0 bottom-0  to-transparent px-8 pb-16 pt-20">
                           {{-- " <p class="text-sm font-semibold text-white">A moment of stillness, just for you.</p>
                            <p class="mt-1 text-xs text-white/70">Read. Reflect. Return.</p>" --}}
                        </div>
                    </div>
                    {{-- <div class="absolute -bottom-5 left-5 flex items-center gap-3 rounded-2xl border border-tilawa-line bg-tilawa-surface px-5 py-3.5 shadow-md">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-tilawa-teal-light/35 text-tilawa-teal-dark"><x-ui.icon name="book" class="h-4 w-4" /></span>
                        <div><p class="text-xs font-bold text-tilawa-ink">Always within reach</p><p class="mt-0.5 text-[11px] text-tilawa-sub">Free to read, on any device</p></div>
                    </div> --}}
                </div>
            </div>
        </section>

        <section id="surahs" class="mx-auto max-w-6xl px-6 py-16 sm:px-8 lg:px-10 lg:py-20">
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-tilawa-teal-dark dark:text-tilawa-teal-light">Begin with a surah</p><h2 class="mt-3 text-3xl font-extrabold tracking-tight text-tilawa-ink">Where will you begin today?</h2><p class="mt-3 text-sm leading-7 text-tilawa-sub">A few familiar places to start. Every surah is one tap away.</p></div>
                <a href="{{ route('home') }}" wire:navigate class="inline-flex shrink-0 items-center gap-2 text-sm font-bold text-tilawa-teal-dark dark:text-tilawa-teal-light">View all 114 surahs <x-ui.icon name="arrow-right" class="h-4 w-4" /></a>
            </div>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featuredSurahs as $surah)
                    <a href="{{ route('surah.show', $surah['number']) }}" wire:navigate wire:key="featured-{{ $surah['number'] }}" class="group flex items-center gap-4 rounded-2xl border border-tilawa-line bg-tilawa-surface p-5 transition hover:border-tilawa-teal hover:bg-tilawa-teal-light/10">
                        <x-ui.number-badge :number="$surah['number']" class="h-11 w-11" textClass="text-sm" />
                        <div class="min-w-0 grow"><h3 class="text-sm font-bold text-tilawa-ink">{{ $surah['englishName'] }}</h3><p class="mt-1 text-xs text-tilawa-sub">{{ $surah['translation'] }} · {{ $surah['ayahs'] }} ayahs</p></div>
                        <span lang="ar" dir="rtl" class="font-arabic shrink-0 text-xl text-tilawa-teal-dark dark:text-tilawa-teal-light">{{ $surah['name'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <section id="about" class="border-y border-tilawa-line bg-tilawa-paper/50">
            <div class="mx-auto grid max-w-6xl gap-10 px-6 py-16 sm:px-8 lg:grid-cols-[1fr_1.5fr] lg:gap-20 lg:px-10">
                <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-tilawa-gold">Made for your daily reading</p><h2 class="mt-4 text-3xl font-extrabold leading-snug tracking-tight text-tilawa-ink">Simple to begin.<br>Easy to come back to.</h2><p class="mt-4 text-sm leading-7 text-tilawa-sub">A thoughtful reading space, with the tools you need and room to focus.</p></div>
                <div class="grid gap-x-8 gap-y-9 sm:grid-cols-2">
                    @foreach ([
                        ['icon' => 'book', 'title' => 'Read your way', 'body' => 'Explore by surah, juz, or page. Switch between ayah cards and a continuous Mushaf view.'],
                        ['icon' => 'globe', 'title' => 'Understand as you read', 'body' => 'English and Swahili translations, Arabic tafsir, and word-by-word meanings.'],
                        ['icon' => 'sun', 'title' => 'Find your quiet', 'body' => 'Comfortable type, light and night themes, and optional Tajweed colors.'],
                        ['icon' => 'bookmark', 'title' => 'Keep your place', 'body' => 'Read freely as a guest. Choose an account when you want bookmarks and progress across devices.'],
                    ] as $feature)
                        <div><span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-tilawa-teal-light/30 text-tilawa-teal-dark dark:text-tilawa-teal-light"><x-ui.icon :name="$feature['icon']" class="h-5 w-5" /></span><h3 class="mt-3 text-sm font-bold text-tilawa-ink">{{ $feature['title'] }}</h3><p class="mt-2 text-sm leading-7 text-tilawa-sub">{{ $feature['body'] }}</p></div>
                    @endforeach
                </div>
            </div>
        </section>
        <section class="mx-auto max-w-6xl px-6 py-14 sm:px-8 lg:px-10">
            <div class="grid items-center gap-5 overflow-hidden rounded-3xl md:grid-cols-2">
                <img src="{{ asset('ruqraimage/ChatGPT Image Sep 25, 2026, 05_19_18 PM.png') }}" alt="An open Qur'an on a wooden stand" width="1397" height="1124" loading="lazy" class="h-auto w-full object-contain p-5">
                <div class="px-7 py-9 sm:px-10">
                    <p class="text-xs font-bold uppercase tracking-widest text-tilawa-ink">Ruqyah reading</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-tilawa-ink">Seek comfort in the words of Allah.</h2>
                    <p class="mt-4 text-sm leading-7 text-tilawa-ink">Explore Qur'anic passages for Ruqyah: recitation and supplication seeking Allah's protection and healing. Read Al-Fatihah, Ayat al-Kursi, and a collection of surahs and ayahs at your own pace.</p>
                    <a href="{{ route('ruqyah') }}" wire:navigate class="mt-6 inline-flex items-center gap-3 rounded-full bg-tilawa-amber px-6 py-3.5 text-sm font-bold text-[#12313a] transition hover:brightness-105">Start reading <x-ui.icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
            </div>
        </section>
    </main>
    <x-ui.site-footer />
</div>
