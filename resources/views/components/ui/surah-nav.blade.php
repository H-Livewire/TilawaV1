@props(['surahs', 'current', 'ayahCount', 'ayah' => 1, 'mode' => 'ayat', 'page' => 1, 'label'])

@php
    // First ayah of each juz as [surah, ayah].
    $juzStarts = [[1, 1], [2, 142], [2, 253], [3, 93], [4, 24], [4, 148], [5, 82], [6, 111], [7, 88], [8, 41], [9, 93], [11, 6], [12, 53], [15, 1], [17, 1], [18, 75], [21, 1], [23, 1], [25, 21], [27, 56], [29, 46], [33, 31], [36, 28], [39, 32], [41, 47], [46, 1], [51, 31], [58, 1], [67, 1], [78, 1]];
    $byNumber = collect($surahs)->keyBy('number');
    $currentJuz = 1;
    foreach ($juzStarts as $i => [$s, $a]) {
        if ($current > $s || ($current === $s && $ayah >= $a)) {
            $currentJuz = $i + 1;
        }
    }
    $row = 'flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition hover:bg-tilawa-line dark:hover:bg-white/10';
    $active = 'bg-tilawa-teal-light/40 font-bold text-tilawa-teal-dark dark:bg-tilawa-teal/20 dark:text-tilawa-teal-light';
    $idle = 'text-tilawa-ink dark:text-[#f3ead9]';
    $cell = 'flex h-11 items-center justify-center rounded-xl text-sm font-semibold transition hover:bg-tilawa-line dark:hover:bg-white/10';
@endphp

<div class="relative"
    x-data="{
        open: false, tab: 'surah', query: '',
        placeholders: { surah: 'Search surah', verse: 'Search verse', juz: 'Search juz', page: 'Search page' },
        show(text) { return this.query === '' || text.toLowerCase().includes(this.query.toLowerCase()) },
        setTab(tab) { this.tab = tab; this.query = ''; this.$nextTick(() => this.reveal()) },
        reveal() {
            const box = this.$refs['list_' + this.tab];
            const el = box && box.querySelector('[data-current]');
            if (el) box.scrollTop = Math.max(0, el.offsetTop - box.clientHeight / 2 + el.offsetHeight / 2);
        },
        close() { this.open = false; this.query = '' },
        pickVerse(n) {
            if (this.$wire.mode === 'mushaf') {
                this.close();
                window.Livewire.navigate('{{ url('/surah/'.$current) }}?ayah=' + n + '&view=mushaf');
                return;
            }
            this.$wire.showAyah(n).then(() => {
                this.close();
                requestAnimationFrame(() => document.getElementById('ayah-' + n)?.scrollIntoView({ behavior: 'smooth', block: 'center' }));
            });
        },
        pickPage(n) {
            const ready = this.$wire.mode === 'mushaf' ? Promise.resolve() : this.$wire.setMode('mushaf');
            this.close();
            ready.then(() => this.$wire.openMushafPage(n));
        },
    }"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    x-init="$watch('open', value => { if (value) $nextTick(() => setTimeout(() => reveal(), 60)) })"
    @keydown.escape.window="close()">
    <button type="button" @click="open = true"
        class="flex items-center gap-2.5 rounded-xl border border-tilawa-line bg-tilawa-surface py-2.5 pl-4 pr-3.5 text-sm font-bold text-tilawa-ink shadow-sm transition hover:border-tilawa-teal dark:border-white/10 dark:bg-[#1c2b30] dark:text-[#f3ead9] dark:hover:border-tilawa-teal">
        <span class="truncate">{{ $label }}</span>
        <x-ui.icon name="chevron-down" class="h-[14px] w-[14px] shrink-0 text-tilawa-sub" />
    </button>

    {{-- Backdrop --}}
    <div x-show="open" x-cloak x-transition.opacity.duration.300ms @click="close()"
        class="fixed inset-0 z-40 bg-tilawa-ink/40 dark:bg-black/60"></div>

    {{-- Panel, slides in from the left --}}
    <aside x-show="open" x-cloak role="dialog" aria-label="Navigate the Qur'an"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-250" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 flex w-full max-w-sm flex-col bg-tilawa-surface shadow-2xl will-change-transform dark:bg-[#1c2b30]">
        <div class="flex items-center gap-2 px-4 pb-3 pt-4">
            <div class="flex grow rounded-xl bg-tilawa-paper/70 p-1 dark:bg-white/5" role="tablist">
                @foreach (['surah' => 'Surah', 'verse' => 'Verse', 'juz' => 'Juz', 'page' => 'Page'] as $key => $title)
                    <button type="button" role="tab" @click="setTab('{{ $key }}')" x-bind:aria-selected="tab === '{{ $key }}'"
                        x-bind:class="tab === '{{ $key }}' ? 'bg-tilawa-surface text-tilawa-ink shadow-sm dark:bg-white/10 dark:text-[#f3ead9]' : 'text-tilawa-sub hover:text-tilawa-ink dark:text-[#a9b9bc]'"
                        class="grow rounded-lg px-3 py-2 text-sm font-semibold transition">{{ $title }}</button>
                @endforeach
            </div>
            <button type="button" @click="close()" aria-label="Close"
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-tilawa-sub transition hover:bg-tilawa-line hover:text-tilawa-ink dark:text-[#a9b9bc] dark:hover:bg-white/10">
                <x-ui.icon name="close" class="h-4 w-4" />
            </button>
        </div>

        <div class="px-4 pb-3">
            <div class="relative">
                <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-tilawa-sub/60" />
                <input type="text" x-model="query" x-bind:placeholder="placeholders[tab]" x-bind:inputmode="tab === 'surah' ? 'text' : 'numeric'"
                    class="w-full rounded-lg border-none bg-tilawa-paper/70 py-2.5 pl-9 pr-3 text-sm text-tilawa-ink placeholder:text-tilawa-sub focus:outline-none focus:ring-2 focus:ring-tilawa-teal-dark dark:bg-white/5 dark:text-[#f3ead9] dark:placeholder:text-[#a9b9bc] dark:focus:ring-tilawa-teal-light">
            </div>
        </div>

        {{-- Surah --}}
        <div x-show="tab === 'surah'" x-transition.opacity.duration.150ms x-ref="list_surah" class="relative grow overflow-y-auto px-3 pb-4">
            @foreach ($surahs as $item)
                <button type="button" wire:click="goToSurah({{ $item['number'] }})" @click="close()"
                    @if ($item['number'] === $current) data-current @endif
                    x-show="show(@js(\Illuminate\Support\Str::lower($item['englishName'].' '.$item['number'])))"
                    class="{{ $row }} {{ $item['number'] === $current ? $active : $idle }}">
                    <span class="w-7 shrink-0 text-center text-xs font-bold {{ $item['number'] === $current ? '' : 'text-tilawa-sub dark:text-[#a9b9bc]' }}">{{ $item['number'] }}</span>
                    <span class="grow truncate">{{ $item['englishName'] }}</span>
                    <span class="font-arabic shrink-0 text-base text-tilawa-sub">{{ $item['name'] }}</span>
                </button>
            @endforeach
        </div>

        {{-- Verse (of the surah being read) --}}
        <div x-show="tab === 'verse'" x-cloak x-transition.opacity.duration.150ms x-ref="list_verse" class="relative grow overflow-y-auto px-4 pb-4">
            <p class="pb-2 text-xs font-semibold text-tilawa-sub dark:text-[#a9b9bc]">{{ $byNumber[$current]['englishName'] ?? '' }} &middot; {{ $ayahCount }} verses</p>
            <div class="grid grid-cols-5 gap-1.5">
                <template x-for="n in {{ $ayahCount }}" :key="'verse-' + n">
                    <button type="button" x-show="show(String(n))" x-text="n" @click="pickVerse(n)"
                        x-bind:data-current="n === $wire.focusedAyah ? '' : null"
                        x-bind:class="n === $wire.focusedAyah ? '{{ $active }}' : '{{ $idle }}'"
                        class="{{ $cell }}"></button>
                </template>
            </div>
        </div>

        {{-- Juz --}}
        <div x-show="tab === 'juz'" x-cloak x-transition.opacity.duration.150ms x-ref="list_juz" class="relative grow overflow-y-auto px-3 pb-4">
            @foreach ($juzStarts as $i => [$s, $a])
                @php $n = $i + 1; @endphp
                <a href="{{ route('juz.show', $n) }}" wire:navigate @click="close()"
                    @if ($n === $currentJuz) data-current @endif
                    x-show="show(@js('juz '.$n.' '.\Illuminate\Support\Str::lower($byNumber[$s]['englishName'] ?? '')))"
                    class="{{ $row }} {{ $n === $currentJuz ? $active : $idle }}">
                    <span class="w-7 shrink-0 text-center text-xs font-bold {{ $n === $currentJuz ? '' : 'text-tilawa-sub dark:text-[#a9b9bc]' }}">{{ $n }}</span>
                    <span class="grow">Juz {{ $n }}</span>
                    <span class="shrink-0 text-xs text-tilawa-sub dark:text-[#a9b9bc]">{{ $byNumber[$s]['englishName'] ?? '' }} {{ $s }}:{{ $a }}</span>
                </a>
            @endforeach
        </div>

        {{-- Page (opens the Mushaf at that page) --}}
        <div x-show="tab === 'page'" x-cloak x-transition.opacity.duration.150ms x-ref="list_page" class="relative grow overflow-y-auto px-4 pb-4">
            <div class="grid grid-cols-5 gap-1.5">
                <template x-for="n in 604" :key="'page-' + n">
                    <button type="button" x-show="show(String(n))" x-text="n" @click="pickPage(n)"
                        x-bind:data-current="$wire.mode === 'mushaf' && n === $wire.physicalPage ? '' : null"
                        x-bind:class="$wire.mode === 'mushaf' && n === $wire.physicalPage ? '{{ $active }}' : '{{ $idle }}'"
                        class="{{ $cell }}"></button>
                </template>
            </div>
        </div>
    </aside>
</div>
