<div>
    <x-ui.site-header />
    <main>
    <section class="border-b border-tilawa-line bg-tilawa-paper py-8 sm:py-10">
        <div class="mx-auto max-w-6xl px-5 text-center sm:px-8 lg:px-10">
            <h1 class="text-3xl font-extrabold tracking-tight text-tilawa-ink md:text-4xl">The Qur'an, within reach.</h1>
            <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-tilawa-sub">Explore all 114 surahs, browse by juz, or find a printed page. Open a surah to begin reading.</p>

            <div class="relative mx-auto mt-6 flex max-w-2xl flex-col items-center gap-4">
            @if ($tab === 'surah')
                <div class="flex w-full items-center gap-2.5 rounded-2xl border border-tilawa-line bg-tilawa-surface px-4.5 py-3.5 shadow-lg focus-within:border-tilawa-teal-dark focus-within:ring-2 focus-within:ring-tilawa-teal-dark dark:focus-within:border-tilawa-teal-light dark:focus-within:ring-tilawa-teal-light">
                    <x-ui.icon name="search" class="h-[18px] w-[18px] shrink-0 text-tilawa-sub/70" />
                    <input aria-label="Search surahs" type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Search a surah in the Qur'an..."
                        class="min-w-0 grow bg-transparent border-none p-0 text-sm text-tilawa-ink placeholder:text-tilawa-sub focus:outline-none focus:ring-0">
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-center gap-2">
                <span class="text-xs font-semibold text-tilawa-sub">Popular:</span>
                @foreach ($this->popularSurahDetails as $surah)
                    <a href="{{ route('surah.show', $surah['number']) }}" wire:navigate
                        class="rounded-full border border-tilawa-line bg-tilawa-surface px-3.5 py-1.5 text-xs font-semibold text-tilawa-ink transition hover:border-tilawa-teal">
                        {{ $surah['englishName'] }}
                    </a>
                @endforeach
            </div>
            </div>
        </div>
    </section>

    {{-- Ayah of the Day + Continue reading: centred, one under the other --}}
    <div class="mx-auto flex max-w-2xl flex-col gap-4 px-5 pt-6 sm:px-8">
    {{-- Ayah of the Day --}}
    @if ($this->dailyAyah)
        <div>
            <a href="{{ route('surah.show', ['number' => $this->dailyAyah['surah'], 'ayah' => $this->dailyAyah['ayah']]) }}" wire:navigate
                class="block rounded-2xl border border-tilawa-line bg-tilawa-surface px-5 py-4 shadow-sm transition hover:border-tilawa-teal hover:shadow-md">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-xs font-bold uppercase tracking-wide text-tilawa-gold">Ayah of the Day</div>
                    <div class="text-xs font-semibold text-tilawa-sub">{{ $this->dailyAyah['reference'] }}</div>
                </div>
                <p lang="ar" dir="rtl" class="font-arabic mt-3 line-clamp-2 text-xl leading-[2.2] text-tilawa-ink">{{ $this->dailyAyah['arabic'] }}</p>
                <p class="mt-2 line-clamp-2 text-sm leading-6 text-tilawa-sub">{{ $this->dailyAyah['translation'] }}</p>
            </a>
            @auth
                <div class="mt-3"><x-daily-reminder compact /></div>
            @endauth
        </div>
    @endif

    {{-- Continue reading --}}
    @if ($this->lastRead)
        <div>
            <a href="{{ route('surah.show', ['number' => $this->lastRead['surah']['number'], 'ayah' => $this->lastRead['ayah']]) }}" wire:navigate
                class="flex items-center gap-4 rounded-2xl border border-tilawa-line bg-tilawa-surface px-5 py-4 shadow-sm transition hover:border-tilawa-teal hover:shadow-md">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-tilawa-teal-light text-tilawa-teal-dark">
                    <x-ui.icon name="book" class="h-5 w-5" />
                </div>
                <div class="min-w-0 grow">
                    <div class="text-xs font-bold uppercase tracking-wide text-tilawa-gold">Continue reading</div>
                    <div class="truncate text-[15px] font-bold text-tilawa-ink">
                        {{ $this->lastRead['surah']['englishName'] }}
                        <span class="font-medium text-tilawa-sub">&middot; Ayah {{ $this->lastRead['ayah'] }}</span>
                    </div>
                </div>
                <x-ui.icon name="arrow-right" class="h-[18px] w-[18px] shrink-0 text-tilawa-sub" />
            </a>
        </div>
    @endif
    </div>

    {{-- Tabs --}}
    <div class="mx-auto max-w-6xl px-5 sm:px-8 lg:px-10 flex flex-wrap items-center gap-3 pt-6">
        <button wire:click="setTab('surah')"
            class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold shadow-sm transition {{ $tab === 'surah' ? 'bg-tilawa-amber text-[#7A5B14]' : 'bg-tilawa-surface text-tilawa-sub' }}">
            <x-ui.icon name="book" class="h-[17px] w-[17px]" /> Surah
        </button>
        <button wire:click="setTab('juz')"
            class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold shadow-sm transition {{ $tab === 'juz' ? 'bg-tilawa-amber text-[#7A5B14]' : 'bg-tilawa-surface text-tilawa-sub' }}">
            <x-ui.icon name="grid" class="h-[17px] w-[17px]" /> Juz
        </button>
        <button wire:click="setTab('page')"
            class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold shadow-sm transition {{ $tab === 'page' ? 'bg-tilawa-amber text-[#7A5B14]' : 'bg-tilawa-surface text-tilawa-sub' }}">
            <x-ui.icon name="list" class="h-[17px] w-[17px]" /> Page
        </button>

        @if ($tab === 'surah')
            <div class="grow"></div>
            <button wire:click="toggleSort"
                class="flex items-center gap-2 rounded-xl bg-tilawa-surface px-5 py-2.5 text-sm font-bold text-tilawa-sub shadow-sm transition hover:text-tilawa-ink">
                <x-ui.icon name="sort" class="h-[17px] w-[17px]" />
                {{ $sort === 'asc' ? '1 → 114' : '114 → 1' }}
            </button>
        @endif
    </div>

    {{-- Content --}}
    <div class="mx-auto max-w-6xl px-5 sm:px-8 lg:px-10 py-6 md:py-8">
        @if ($tab === 'surah')
            @if (count($this->surahs) === 0)
                <p class="py-16 text-center text-sm text-tilawa-sub">No surahs match "{{ $search }}".</p>
            @else
                <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($this->surahs as $surah)
                        <a href="{{ route('surah.show', $surah['number']) }}" wire:navigate
                            class="flex items-center gap-3.5 rounded-2xl border border-tilawa-line bg-tilawa-surface px-4 py-3.5 transition hover:border-tilawa-teal hover:shadow-sm">
                            <x-ui.number-badge :number="$surah['number']" class="h-11 w-11" textClass="text-sm" />
                            <div class="grow min-w-0">
                                <div class="truncate text-[15px] font-bold text-tilawa-ink">
                                    {{ $surah['englishName'] }}
                                    <span class="font-medium text-tilawa-sub text-[13px]">({{ $surah['englishNameTranslation'] }})</span>
                                </div>
                                <div class="mt-1 flex items-center gap-1.5">
                                    @if ($surah['revelationType'] === 'Meccan')
                                        <span class="rounded-full bg-tilawa-mint px-2.5 py-0.5 text-[11px] font-bold text-tilawa-purple">Meccan</span>
                                    @else
                                        <span class="rounded-full bg-[#C9E4FF] px-2.5 py-0.5 text-[11px] font-bold text-[#2A5FA0]">Medinan</span>
                                    @endif
                                    <span class="text-xs text-tilawa-sub">{{ $surah['numberOfAyahs'] }} verses</span>
                                </div>
                            </div>
                            <div class="font-arabic shrink-0 text-xl font-bold text-tilawa-ink">{{ $surah['name'] }}</div>
                        </a>
                    @endforeach
                </div>
            @endif
        @elseif ($tab === 'juz')
            <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8">
                @foreach ($juzNumbers as $number)
                    <a href="{{ route('juz.show', $number) }}" wire:navigate
                        class="rounded-xl border border-tilawa-line bg-tilawa-surface px-3 py-4 text-center transition hover:border-tilawa-teal hover:shadow-sm">
                        <div class="text-xs font-semibold text-tilawa-sub">Juz</div>
                        <div class="text-lg font-bold text-tilawa-ink">{{ $number }}</div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="grid grid-cols-4 gap-2.5 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10">
                @foreach ($pageNumbers as $number)
                    <a href="{{ route('page.show', $number) }}" wire:navigate class="rounded-lg border border-tilawa-line bg-tilawa-surface py-2.5 text-center text-sm font-semibold text-tilawa-ink transition hover:border-tilawa-teal">
                        {{ $number }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    </main>
    <x-ui.site-footer />
</div>
