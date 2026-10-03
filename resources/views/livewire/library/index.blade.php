<div>
    <x-ui.site-header />

    <main>
        {{-- Hero Section --}}
        <section class="border-b border-tilawa-line bg-tilawa-paper px-6 py-12 md:px-12">
            <div class="mx-auto max-w-6xl">
                <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-tilawa-teal/30 bg-tilawa-teal/10 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-tilawa-teal-dark dark:text-tilawa-teal-light">
                            <x-ui.icon name="book" class="h-3.5 w-3.5" />
                            Maktaba ya Kiislamu
                        </div>
                        <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-tilawa-ink md:text-4xl">
                            Islamic Library
                        </h1>
                        <p class="mt-2.5 max-w-2xl text-sm leading-relaxed text-tilawa-sub">
                            Mkusanyiko wa vitabu bora vya Kiislamu, kuanzia Qur'ani Tukufu, Adhkar na Dua (Hisn al-Muslim), Hadithi, Aqeedah, Fiqh, na Sira. Soma moja kwa moja kwenye kivinjari chako kwa muundo wa kitabu halisi.
                        </p>
                    </div>

                    {{-- Quick Action: Hisn al-Muslim spotlight --}}
                    <a href="{{ route('library.book', 'hisn-al-muslim') }}" wire:navigate
                        class="group flex items-center gap-4 rounded-2xl border border-tilawa-gold/30 bg-tilawa-surface p-4 shadow-sm transition hover:border-tilawa-gold hover:shadow-md">
                        <div class="flex h-12 w-10 shrink-0 items-center justify-center rounded-lg bg-[#188691] text-xs font-bold text-white shadow-inner">
                            <span class="font-arabic text-sm">حصن</span>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-tilawa-gold">Kitabu Kinachosomwa Zaidi</span>
                            <div class="text-sm font-bold text-tilawa-ink group-hover:text-tilawa-teal-dark">Hisn al-Muslim</div>
                            <div class="text-xs text-tilawa-sub">Fungua Kitabu &rarr;</div>
                        </div>
                    </a>
                </div>

                {{-- Search Bar --}}
                <div class="mt-8 max-w-2xl">
                    <div class="flex items-center gap-3 rounded-2xl border border-tilawa-line bg-tilawa-surface px-4 py-3 shadow-md focus-within:border-tilawa-teal-dark focus-within:ring-2 focus-within:ring-tilawa-teal-dark dark:focus-within:border-tilawa-teal-light">
                        <x-ui.icon name="search" class="h-5 w-5 shrink-0 text-tilawa-sub" />
                        <input aria-label="Tafuta vitabu" type="text" wire:model.live.debounce.250ms="search"
                            placeholder="Tafuta kitabu, mwandishi, au maudhui..."
                            class="min-w-0 grow border-none bg-transparent p-0 text-sm text-tilawa-ink placeholder:text-tilawa-sub focus:outline-none focus:ring-0">
                        @if ($search !== '')
                            <button type="button" wire:click="$set('search', '')" class="text-xs text-tilawa-sub hover:text-tilawa-ink">
                                Futa
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Categories Pill Bar --}}
                <div class="mt-6 flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="setCategory('all')"
                        class="rounded-xl px-4 py-2 text-xs font-bold transition shadow-sm {{ $activeCategory === 'all' ? 'bg-tilawa-teal-dark text-white' : 'border border-tilawa-line bg-tilawa-surface text-tilawa-sub hover:border-tilawa-teal' }}">
                        Vitabu Vyote
                    </button>
                    @foreach ($this->categories as $key => $cat)
                        <button type="button" wire:click="setCategory('{{ $key }}')"
                            class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold transition shadow-sm {{ $activeCategory === $key ? 'bg-tilawa-teal-dark text-white' : 'border border-tilawa-line bg-tilawa-surface text-tilawa-sub hover:border-tilawa-teal' }}">
                            <x-ui.icon :name="$cat['icon']" class="h-3.5 w-3.5" />
                            {{ $cat['name'] }}
                        </button>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Catalog Section --}}
        <section class="mx-auto max-w-6xl px-6 py-10 md:px-12">
            {{-- Header info --}}
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-lg font-bold text-tilawa-ink">
                    @if ($activeCategory === 'all')
                        Vitabu Vyote Maktabani ({{ count($this->books) }})
                    @else
                        {{ $this->categories[$activeCategory]['name'] ?? 'Vitabu' }} ({{ count($this->books) }})
                    @endif
                </h2>
                <span class="text-xs text-tilawa-sub">Uzoefu wa kusoma kama kitabu halisi</span>
            </div>

            @if (count($this->books) === 0)
                <div class="rounded-2xl border border-tilawa-line bg-tilawa-surface p-12 text-center">
                    <x-ui.icon name="book" class="mx-auto h-12 w-12 text-tilawa-sub/40" />
                    <p class="mt-4 text-sm font-semibold text-tilawa-sub">Hakuna kitabu kilichopatikana kwa utafutaji huo.</p>
                    <button type="button" wire:click="$set('search', ''); $set('activeCategory', 'all');" class="mt-3 text-xs font-bold text-tilawa-teal-dark hover:underline">
                        Onyesha vitabu vyote
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($this->books as $book)
                        <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-tilawa-line bg-tilawa-surface p-6 shadow-sm transition-all hover:-translate-y-1 hover:border-tilawa-teal hover:shadow-lg">
                            {{-- Top Banner --}}
                            <div>
                                <div class="flex items-start justify-between gap-3">
                                    <span class="inline-flex rounded-full bg-tilawa-mint/30 px-3 py-1 text-[11px] font-bold text-tilawa-teal-dark dark:bg-tilawa-teal/20 dark:text-tilawa-teal-light">
                                        {{ $book['category'] }}
                                    </span>
                                    @if ($book['status'] === 'available')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Inasomeka Sasa
                                        </span>
                                    @else
                                        <span class="rounded-full bg-tilawa-line px-2.5 py-0.5 text-[11px] font-bold text-tilawa-sub dark:bg-white/10 dark:text-white/60">
                                            Inakuja Karibuni
                                        </span>
                                    @endif
                                </div>

                                {{-- Book Identity --}}
                                <div class="mt-5">
                                    <h3 class="text-xl font-bold tracking-tight text-tilawa-ink group-hover:text-tilawa-teal-dark">
                                        {{ $book['title'] }}
                                    </h3>
                                    <div class="font-arabic mt-1 text-lg font-semibold text-tilawa-gold">
                                        {{ $book['arabicTitle'] }}
                                    </div>
                                    <div class="mt-0.5 text-xs font-medium text-tilawa-sub">
                                        {{ $book['swahiliTitle'] }}
                                    </div>
                                </div>

                                {{-- Description --}}
                                <p class="mt-3.5 text-xs leading-relaxed text-tilawa-sub">
                                    {{ $book['description'] }}
                                </p>
                            </div>

                            {{-- Bottom Meta & Action --}}
                            <div class="mt-6 border-t border-tilawa-line pt-4">
                                <div class="flex items-center justify-between text-xs text-tilawa-sub">
                                    <span class="truncate max-w-[170px]" title="{{ $book['author'] }}">{{ $book['author'] }}</span>
                                    @if (isset($book['pagesCount']))
                                        <span>{{ $book['pagesCount'] }} kurasa</span>
                                    @endif
                                </div>

                                <div class="mt-4">
                                    @if ($book['status'] === 'available')
                                        <a href="{{ route('library.book', $book['slug']) }}" wire:navigate
                                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-tilawa-teal-dark py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-tilawa-teal">
                                            <x-ui.icon name="book" class="h-4 w-4" />
                                            Fungua Kitabu (Read Book)
                                        </a>
                                    @else
                                        <button type="button" disabled
                                            class="w-full cursor-not-allowed rounded-xl border border-tilawa-line bg-tilawa-line/40 py-2.5 text-center text-xs font-bold text-tilawa-sub/60">
                                            Toleo Linaandaliwa
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </main>

    <x-ui.site-footer />
</div>
