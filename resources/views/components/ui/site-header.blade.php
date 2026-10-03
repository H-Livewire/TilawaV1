<header class="relative z-30 border-b border-tilawa-line bg-tilawa-surface" x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-2 px-5 py-5 sm:px-8 lg:px-10">
        <a href="{{ route('landing') }}" wire:navigate aria-label="Tilawa home" class="flex shrink-0 items-center gap-2.5 text-tilawa-ink">
            <x-ui.logo />
        </a>
        <nav aria-label="Main navigation" class="hidden items-center gap-5 text-sm font-semibold text-tilawa-sub lg:flex">
            <a href="{{ route('home') }}" wire:navigate @if (request()->routeIs('home')) aria-current="page" @endif class="transition hover:text-tilawa-teal-dark">Browse Qur'an</a>

            {{-- Islamic Library Dropdown / Mega-Menu --}}
            <div class="relative" x-data="{ libraryOpen: false }" @mouseleave="libraryOpen = false">
                <button type="button" @click="libraryOpen = !libraryOpen" @mouseenter="libraryOpen = true"
                    :aria-expanded="libraryOpen" aria-haspopup="true"
                    class="inline-flex items-center gap-1.5 transition hover:text-tilawa-teal-dark focus:outline-none py-1"
                    :class="libraryOpen ? 'text-tilawa-teal-dark font-bold' : ''">
                    <span>Islamic Library</span>
                    <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="libraryOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9l6 6 6-6"/></svg>
                </button>

                {{-- Mega-Menu Panel --}}
                <div x-show="libraryOpen" x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                    @click.outside="libraryOpen = false"
                    class="absolute -left-20 top-full mt-2 w-[580px] rounded-2xl border border-tilawa-line bg-tilawa-surface p-5 shadow-2xl z-50">

                    {{-- Top Bar & Header --}}
                    <div class="flex items-center justify-between border-b border-tilawa-line pb-3">
                        <div>
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-tilawa-gold">Maktaba ya Kiislamu</span>
                            <div class="text-sm font-bold text-tilawa-ink">Islamic Library &amp; Books</div>
                        </div>
                        <a href="{{ route('library.index') }}" wire:navigate @click="libraryOpen = false"
                            class="text-xs font-bold text-tilawa-teal-dark hover:underline flex items-center gap-1">
                            <span>Vitabu Vyote (All Books)</span>
                            <span>&rarr;</span>
                        </a>
                    </div>

                    {{-- 2-Column Grid: Categories & Featured Book --}}
                    <div class="mt-4 grid grid-cols-12 gap-4">
                        {{-- Left Categories (7 cols) --}}
                        <div class="col-span-7 grid grid-cols-2 gap-2 text-xs">
                            <a href="{{ route('home') }}" wire:navigate @click="libraryOpen = false"
                                class="group rounded-xl p-2.5 hover:bg-tilawa-paper transition border border-transparent hover:border-tilawa-line">
                                <div class="font-bold text-tilawa-ink group-hover:text-tilawa-teal-dark flex items-center justify-between">
                                    <span>Quran</span>
                                    <span class="font-arabic text-[11px] text-tilawa-gold">القرآن</span>
                                </div>
                                <div class="text-[11px] text-tilawa-sub mt-0.5 truncate">114 Surahs, Juz, Pages</div>
                            </a>

                            <a href="{{ route('library.book', 'hisn-al-muslim') }}" wire:navigate @click="libraryOpen = false"
                                class="group rounded-xl p-2.5 bg-tilawa-teal/10 hover:bg-tilawa-teal/20 transition border border-tilawa-teal/30">
                                <div class="font-bold text-tilawa-teal-dark flex items-center justify-between">
                                    <span>Dua &amp; Adhkar</span>
                                    <span class="font-arabic text-[11px] text-tilawa-teal-dark">الأذكار</span>
                                </div>
                                <div class="text-[11px] text-tilawa-teal-dark font-medium mt-0.5 truncate">Hisn al-Muslim ★</div>
                            </a>

                            <a href="{{ route('library.index') }}" wire:navigate @click="libraryOpen = false"
                                class="group rounded-xl p-2.5 hover:bg-tilawa-paper transition border border-transparent hover:border-tilawa-line">
                                <div class="font-bold text-tilawa-ink group-hover:text-tilawa-teal-dark flex items-center justify-between">
                                    <span>Hadith</span>
                                    <span class="font-arabic text-[11px] text-tilawa-sub">الحديث</span>
                                </div>
                                <div class="text-[11px] text-tilawa-sub mt-0.5 truncate">40 An-Nawawi, Riyad</div>
                            </a>

                            <a href="{{ route('library.index') }}" wire:navigate @click="libraryOpen = false"
                                class="group rounded-xl p-2.5 hover:bg-tilawa-paper transition border border-transparent hover:border-tilawa-line">
                                <div class="font-bold text-tilawa-ink group-hover:text-tilawa-teal-dark flex items-center justify-between">
                                    <span>Aqeedah</span>
                                    <span class="font-arabic text-[11px] text-tilawa-sub">العقيدة</span>
                                </div>
                                <div class="text-[11px] text-tilawa-sub mt-0.5 truncate">Tawheed &amp; Iman</div>
                            </a>

                            <a href="{{ route('library.index') }}" wire:navigate @click="libraryOpen = false"
                                class="group rounded-xl p-2.5 hover:bg-tilawa-paper transition border border-transparent hover:border-tilawa-line">
                                <div class="font-bold text-tilawa-ink group-hover:text-tilawa-teal-dark flex items-center justify-between">
                                    <span>Fiqh</span>
                                    <span class="font-arabic text-[11px] text-tilawa-sub">الفقه</span>
                                </div>
                                <div class="text-[11px] text-tilawa-sub mt-0.5 truncate">Swalah &amp; Ibadah</div>
                            </a>

                            <a href="{{ route('library.index') }}" wire:navigate @click="libraryOpen = false"
                                class="group rounded-xl p-2.5 hover:bg-tilawa-paper transition border border-transparent hover:border-tilawa-line">
                                <div class="font-bold text-tilawa-ink group-hover:text-tilawa-teal-dark flex items-center justify-between">
                                    <span>Seerah</span>
                                    <span class="font-arabic text-[11px] text-tilawa-sub">السيرة</span>
                                </div>
                                <div class="text-[11px] text-tilawa-sub mt-0.5 truncate">Maisha ya Mtume</div>
                            </a>

                            <a href="{{ route('library.index') }}" wire:navigate @click="libraryOpen = false"
                                class="group rounded-xl p-2.5 hover:bg-tilawa-paper transition border border-transparent hover:border-tilawa-line">
                                <div class="font-bold text-tilawa-ink group-hover:text-tilawa-teal-dark flex items-center justify-between">
                                    <span>Tafsir</span>
                                    <span class="font-arabic text-[11px] text-tilawa-sub">التفسير</span>
                                </div>
                                <div class="text-[11px] text-tilawa-sub mt-0.5 truncate">Maana ya Qur'an</div>
                            </a>

                            <a href="{{ route('library.index') }}" wire:navigate @click="libraryOpen = false"
                                class="group rounded-xl p-2.5 hover:bg-tilawa-paper transition border border-transparent hover:border-tilawa-line">
                                <div class="font-bold text-tilawa-ink group-hover:text-tilawa-teal-dark flex items-center justify-between">
                                    <span>Other Books</span>
                                    <span class="font-arabic text-[11px] text-tilawa-sub">كتب أخرى</span>
                                </div>
                                <div class="text-[11px] text-tilawa-sub mt-0.5 truncate">Adab &amp; Tazkiyah</div>
                            </a>
                        </div>

                        {{-- Right Spotlight: Hisn al-Muslim (5 cols) --}}
                        <div class="col-span-5 rounded-xl border border-tilawa-gold/40 bg-gradient-to-br from-[#188691]/10 via-tilawa-surface to-tilawa-paper p-3.5 flex flex-col justify-between">
                            <div>
                                <div class="inline-flex items-center gap-1 rounded-full bg-tilawa-amber/30 px-2 py-0.5 text-[10px] font-bold text-[#7A5B14] dark:text-tilawa-amber">
                                    ★ Kitabu Kinachosomwa Sasa
                                </div>
                                <h4 class="font-bold text-sm text-tilawa-ink mt-2">Hisn al-Muslim</h4>
                                <div class="font-arabic text-sm text-tilawa-gold">حِصْنُ الْمُسْلِمِ</div>
                                <p class="text-[11px] text-tilawa-sub mt-1 leading-snug">
                                    Dua na Adhkar za kila siku zenye uzoefu wa kitabu halisi chenye sauti na kurasa.
                                </p>
                            </div>

                            <a href="{{ route('library.book', 'hisn-al-muslim') }}" wire:navigate @click="libraryOpen = false"
                                class="mt-3 flex items-center justify-center gap-1.5 rounded-lg bg-tilawa-teal-dark px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-tilawa-teal transition">
                                <span>Fungua Kitabu (Read)</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('ruqyah') }}" wire:navigate @if (request()->routeIs('ruqyah')) aria-current="page" @endif class="transition hover:text-tilawa-teal-dark">Ruqyah read</a>
            <a href="{{ route('landing') }}#about" class="transition hover:text-tilawa-teal-dark">About Tilawa</a>
            @auth
                <a href="{{ route('bookmarks') }}" wire:navigate class="transition hover:text-tilawa-teal-dark">Bookmarks</a>
            @endauth
        </nav>
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="rounded-full bg-tilawa-teal-dark"><x-ui.theme-selector /></div>
            @auth
                <form method="POST" action="{{ route('logout') }}" class="hidden lg:block">@csrf<button type="submit" class="text-xs font-semibold text-tilawa-sub hover:text-tilawa-ink">Sign out</button></form>
                <a href="{{ route('profile') }}" wire:navigate class="whitespace-nowrap rounded-full bg-tilawa-amber px-3 py-2.5 text-xs font-bold text-[#12313a] sm:text-sm">My account</a>
            @else
                <button type="button" @click="$dispatch('open-login')" aria-haspopup="dialog" aria-controls="login-drawer" class="whitespace-nowrap rounded-full bg-tilawa-amber px-3 py-2.5 text-xs font-bold text-[#12313a] transition hover:brightness-105 sm:px-5 sm:text-sm">Get started</button>
            @endauth
            <button type="button" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" aria-controls="mobile-navigation" aria-label="Toggle navigation" class="flex h-8 w-8 items-center justify-center rounded-lg text-tilawa-ink lg:hidden">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
        </div>
    </div>
    <nav id="mobile-navigation" aria-label="Mobile navigation" x-show="menuOpen" x-cloak class="flex flex-col gap-4 border-t border-tilawa-line px-5 py-5 text-sm font-semibold text-tilawa-ink lg:hidden">
        <a href="{{ route('home') }}" wire:navigate>Browse Qur'an</a>

        {{-- Mobile Islamic Library Accordion --}}
        <div x-data="{ libraryExpanded: false }" class="border-y border-tilawa-line/60 py-2">
            <button type="button" @click="libraryExpanded = !libraryExpanded" class="flex w-full items-center justify-between text-left py-1 text-sm font-semibold text-tilawa-ink">
                <span class="flex items-center gap-2">
                    <x-ui.icon name="book" class="h-4 w-4 text-tilawa-teal-dark" />
                    <span>Islamic Library</span>
                </span>
                <svg class="h-4 w-4 transition-transform duration-200" :class="libraryExpanded ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div x-show="libraryExpanded" x-cloak class="mt-3 flex flex-col gap-2.5 pl-3 border-l-2 border-tilawa-teal">
                <a href="{{ route('library.index') }}" wire:navigate class="text-xs font-bold text-tilawa-teal-dark flex items-center justify-between">
                    <span>&bull; Vitabu Vyote (All Books)</span>
                    <span>&rarr;</span>
                </a>
                <a href="{{ route('library.book', 'hisn-al-muslim') }}" wire:navigate class="text-xs font-bold text-tilawa-gold flex items-center justify-between">
                    <span>&bull; Hisn al-Muslim (Ngome ya Muislamu)</span>
                    <span class="rounded bg-tilawa-gold/20 px-1.5 py-0.5 text-[10px]">Soma</span>
                </a>
                <a href="{{ route('home') }}" wire:navigate class="text-xs text-tilawa-sub">&bull; Quran</a>
                <a href="{{ route('library.index') }}" wire:navigate class="text-xs text-tilawa-sub">&bull; Hadith</a>
                <a href="{{ route('library.index') }}" wire:navigate class="text-xs text-tilawa-sub">&bull; Aqeedah</a>
                <a href="{{ route('library.index') }}" wire:navigate class="text-xs text-tilawa-sub">&bull; Fiqh</a>
                <a href="{{ route('library.index') }}" wire:navigate class="text-xs text-tilawa-sub">&bull; Seerah</a>
                <a href="{{ route('library.index') }}" wire:navigate class="text-xs text-tilawa-sub">&bull; Tafsir</a>
            </div>
        </div>

        <a href="{{ route('ruqyah') }}" wire:navigate>Ruqyah read</a>
        <a href="{{ route('landing') }}#about" @click="menuOpen = false">About Tilawa</a>
        @auth
            <a href="{{ route('bookmarks') }}" wire:navigate>Bookmarks</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sign out</button></form>
        @endauth
    </nav>
</header>
