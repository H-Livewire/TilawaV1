<div>
    <div class="rounded-b-[2.5rem] bg-tilawa-teal px-6 pb-8 pt-6 md:px-12">
        <div class="mx-auto flex max-w-3xl items-center gap-3">
            <a href="{{ route('home') }}" wire:navigate aria-label="Back to Qur'an"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/15 text-white transition hover:bg-white/25">
                <x-ui.icon name="arrow-left" class="h-[18px] w-[18px]" />
            </a>
            <div>
                <div class="text-lg font-bold text-white">Ayah of the Day</div>
                <div class="text-xs font-semibold text-white/75">{{ now()->format('l, j F Y') }}</div>
            </div>
        </div>
    </div>

    <div class="px-6 py-8 md:px-12">
        <div class="mx-auto flex max-w-3xl flex-col gap-6">
            @if ($this->ayah)
                @php($ayah = $this->ayah)
                <article class="rounded-3xl border border-tilawa-line bg-tilawa-surface px-6 py-8 text-center shadow-sm md:px-10">
                    <div class="mx-auto flex items-center justify-center gap-3">
                        <x-ui.number-badge :number="$ayah['ayah']" class="h-10 w-10" textClass="text-xs" />
                        <div class="text-left">
                            <div class="text-sm font-bold text-tilawa-ink">{{ $ayah['surahName'] }}</div>
                            <div class="text-xs font-semibold text-tilawa-sub">Surah {{ $ayah['surah'] }} &middot; Ayah {{ $ayah['ayah'] }}</div>
                        </div>
                    </div>
                    <p lang="ar" dir="rtl" class="font-arabic mt-8 text-3xl leading-[2.4] text-tilawa-ink md:text-4xl md:leading-[2.4]">{{ $ayah['arabic'] }}</p>
                    <div class="mx-auto mt-6 h-px w-16 bg-tilawa-gold/40"></div>
                    <p class="mt-6 text-base leading-8 text-tilawa-ink md:text-lg">{{ $ayah['translation'] }}</p>
                    <p class="mt-3 text-xs font-semibold text-tilawa-sub">{{ $ayah['translator'] }}</p>
                    <a href="{{ route('surah.show', ['number' => $ayah['surah'], 'ayah' => $ayah['ayah']]) }}" wire:navigate
                        class="mt-8 inline-flex items-center gap-3 rounded-full bg-tilawa-amber px-6 py-3 text-sm font-bold text-[#12313a] transition hover:brightness-105">
                        Read in context <x-ui.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </article>
            @else
                <div class="rounded-3xl border border-tilawa-line bg-tilawa-surface px-6 py-10 text-center">
                    <p class="text-sm font-semibold text-tilawa-ink">Today's ayah can't be loaded right now.</p>
                    <p class="mt-2 text-sm text-tilawa-sub">Please check your connection and try again in a moment.</p>
                </div>
            @endif

            <x-daily-reminder />
        </div>
    </div>
</div>
