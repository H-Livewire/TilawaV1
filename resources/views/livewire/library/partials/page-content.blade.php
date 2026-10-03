@if ($p['type'] === 'title')
    {{-- INNER TITLE PAGE --}}
    <div class="text-center py-8">
        <div class="font-arabic text-base text-tilawa-gold font-bold mb-4">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
        <h2 class="font-arabic text-4xl font-extrabold text-tilawa-ink mb-2">{{ $p['arabicTitle'] }}</h2>
        <div class="text-xl font-extrabold text-tilawa-ink tracking-wider">{{ $p['title'] }}</div>
        <div class="text-sm font-semibold text-tilawa-teal-dark mt-1">{{ $p['swahiliTitle'] }}</div>

        <div class="my-8 mx-auto w-24 border-b border-tilawa-gold/40"></div>

        <div class="text-xs text-tilawa-sub uppercase tracking-widest font-semibold">Mwandishi / Mkusanyaji</div>
        <div class="text-sm font-bold text-tilawa-ink mt-1">{{ $p['author'] }}</div>

        <div class="mt-8 text-xs text-tilawa-sub leading-relaxed max-w-sm mx-auto">
            {{ $p['description'] }}
        </div>

        <div class="mt-8 inline-block rounded-lg bg-tilawa-paper px-4 py-2 border border-tilawa-line text-xs font-semibold text-tilawa-sub">
            {{ $p['edition'] }}
        </div>
    </div>

@elseif ($p['type'] === 'foreword')
    {{-- FOREWORD / UTANGULIZI --}}
    <div class="py-2">
        <div class="text-center mb-6">
            <span class="text-xs font-bold uppercase tracking-wider text-tilawa-gold">Utangulizi</span>
            <h2 class="text-xl font-bold text-tilawa-ink mt-1">Neno la Utangulizi</h2>
        </div>

        @if (!empty($p['foreword']['arabic']))
            <div class="font-arabic text-xl leading-[2.2] text-tilawa-teal-dark text-right mb-5 p-4 rounded-xl bg-tilawa-paper/60 border border-tilawa-line/60">
                {{ $p['foreword']['arabic'] }}
            </div>
        @endif

        <div class="space-y-4 text-xs sm:text-sm text-tilawa-ink leading-relaxed">
            <p>{{ $p['foreword']['swahili'] ?? '' }}</p>
            @if (!empty($p['foreword']['english']))
                <p class="text-tilawa-sub italic border-t border-tilawa-line/40 pt-3 text-xs">{{ $p['foreword']['english'] }}</p>
            @endif
        </div>
    </div>

@elseif ($p['type'] === 'toc')
    {{-- TABLE OF CONTENTS PAGE --}}
    <div class="py-2">
        <div class="text-center mb-5">
            <span class="text-xs font-bold uppercase tracking-wider text-tilawa-gold">Faharasa</span>
            <h2 class="text-xl font-bold text-tilawa-ink mt-1">Orodha ya Yaliyomo</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
            @foreach ($p['toc'] as $item)
                <button type="button" wire:click="goToChapter({{ $item['id'] }}); playPageSound();"
                    class="p-2.5 rounded-lg border border-tilawa-line/70 hover:border-tilawa-teal hover:bg-tilawa-paper text-left transition flex items-center justify-between gap-2">
                    <span class="font-medium text-tilawa-ink truncate">
                        <span class="font-bold text-tilawa-teal-dark mr-1">{{ $item['number'] }}.</span>
                        {{ $item['title'] }}
                    </span>
                    <span class="font-arabic text-tilawa-sub shrink-0">{{ $item['arabicTitle'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

@elseif ($p['type'] === 'content' && isset($p['chapter']))
    {{-- CHAPTER / DUAS CONTENT PAGE --}}
    <div class="space-y-6">
        {{-- Chapter Title Bar (if not continuation) --}}
        @if (!($p['chapter']['isContinuation'] ?? false))
            <div class="text-center border-b border-tilawa-gold/30 pb-4">
                <span class="inline-block rounded-full bg-tilawa-gold/15 px-3 py-0.5 text-[11px] font-bold text-[#7A5B14] dark:text-tilawa-amber uppercase tracking-wider">
                    Mlango wa {{ $p['chapter']['number'] }}
                </span>
                <h3 class="text-lg sm:text-xl font-bold text-tilawa-ink mt-2">
                    {{ $p['chapter']['title'] }}
                </h3>
                <div class="font-arabic text-xl font-bold text-tilawa-teal-dark mt-1">
                    {{ $p['chapter']['arabicTitle'] }}
                </div>
                <div class="text-xs text-tilawa-sub mt-0.5 italic">
                    {{ $p['chapter']['englishTitle'] }}
                </div>
            </div>
        @else
            <div class="flex items-center justify-between text-xs text-tilawa-sub border-b border-tilawa-line pb-2 mb-4">
                <span>{{ $p['chapter']['title'] }} (Muendelezo)</span>
                <span class="font-arabic text-tilawa-gold">{{ $p['chapter']['arabicTitle'] }}</span>
            </div>
        @endif

        {{-- Duas in this Chapter --}}
        <div class="space-y-6">
            @foreach ($p['chapter']['items'] as $dua)
                <div class="rounded-xl border border-tilawa-line/80 bg-white/70 dark:bg-white/5 p-4 sm:p-5 shadow-xs relative">
                    {{-- Top Item Bar --}}
                    <div class="flex items-center justify-between mb-3 text-xs">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-tilawa-teal/15 text-xs font-bold text-tilawa-teal-dark">
                            {{ $dua['id'] }}
                        </span>
                        @if ($dua['repeats'] > 1)
                            <span class="rounded-full bg-tilawa-amber/40 px-2.5 py-0.5 text-[11px] font-bold text-[#7A5B14] dark:text-tilawa-amber">
                                Soma mara {{ $dua['repeats'] }}
                            </span>
                        @else
                            <span class="text-[11px] text-tilawa-sub">Soma mara 1</span>
                        @endif
                    </div>

                    {{-- Arabic Text (Amiri Font, vocalized, right-to-left) --}}
                    <div class="font-arabic text-xl sm:text-2xl leading-[2.3] text-tilawa-ink text-right mb-4 font-normal" dir="rtl">
                        {{ $dua['arabic'] }}
                    </div>

                    {{-- Transliteration --}}
                    @if (!empty($dua['transliteration']))
                        <div class="text-xs text-tilawa-teal-dark dark:text-tilawa-teal-light font-medium italic mb-2.5">
                            {{ $dua['transliteration'] }}
                        </div>
                    @endif

                    {{-- Swahili Translation --}}
                    <div class="text-xs sm:text-sm text-tilawa-ink leading-relaxed font-normal mb-2">
                        <span class="font-bold text-tilawa-sub mr-1">Tafsiri:</span>
                        {{ $dua['swahili'] }}
                    </div>

                    {{-- English Translation --}}
                    @if (!empty($dua['english']))
                        <div class="text-[11px] sm:text-xs text-tilawa-sub leading-relaxed border-t border-tilawa-line/40 pt-2 mb-2">
                            {{ $dua['english'] }}
                        </div>
                    @endif

                    {{-- Hadith Citation / Reference --}}
                    @if (!empty($dua['reference']))
                        <div class="text-[11px] text-[#7A5B14] dark:text-tilawa-gold font-medium mt-2 flex items-center gap-1.5">
                            <span class="inline-block h-1 w-1 rounded-full bg-tilawa-gold"></span>
                            <span>{{ $dua['reference'] }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

@elseif ($p['type'] === 'backcover')
    {{-- BACK COVER / CLOSING --}}
    <div class="text-center py-12 flex flex-col justify-between h-full">
        <div>
            <div class="font-arabic text-2xl text-tilawa-gold font-bold mb-4">وَآخِرُ دَعْوَانَا أَنِ الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ</div>
            <h2 class="text-xl font-bold text-tilawa-ink mb-2">Tamati ya Kitabu</h2>
            <p class="text-xs text-tilawa-sub max-w-sm mx-auto leading-relaxed">
                Tunamhimidi Mwenyezi Mungu Mtukufu kwa kutujaalia kumaliza kusoma kitabu hiki chenye baraka tele. Mwenyezi Mungu Atukubalie dua na dhikr zetu.
            </p>
        </div>

        <div class="my-8">
            <div class="mx-auto w-16 h-16 rounded-full border border-tilawa-gold/50 flex items-center justify-center bg-tilawa-paper">
                <span class="font-arabic text-xl text-tilawa-teal-dark font-bold">تم</span>
            </div>
        </div>

        <div class="text-xs text-tilawa-sub">
            Tilawa — Maktaba ya Kiislamu &copy; {{ now()->year }}
        </div>
    </div>
@endif
