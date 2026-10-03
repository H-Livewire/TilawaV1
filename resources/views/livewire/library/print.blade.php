<div class="bg-white min-h-screen text-[#12313a] font-sans">
    {{-- On-Screen Toolbar (Hidden during print) --}}
    <div class="no-print sticky top-0 z-50 bg-[#12313a] text-white px-6 py-4 shadow-md flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('library.book', $slug) }}" wire:navigate
                class="flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-white/20 transition">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                <span>Rudi Kwenye Kitabu</span>
            </a>
            <span class="text-sm font-bold">{{ $this->book['title'] }} — Toleo la Kuchapisha / PDF</span>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs text-white/70 hidden sm:inline">Chagua "Save as PDF" katika print preview ili kupakua PDF</span>
            <button type="button" onclick="window.print()"
                class="flex items-center gap-2 rounded-xl bg-tilawa-amber px-4 py-2 text-xs font-bold text-[#12313a] shadow-sm hover:brightness-105 transition">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                <span>Chapisha / Hifadhi kama PDF</span>
            </button>
        </div>
    </div>

    {{-- Printable Book Document --}}
    <div class="max-w-4xl mx-auto px-8 py-12 print:p-0 print:max-w-full">
        {{-- Cover / Title Block --}}
        <div class="text-center border-b-2 border-stone-300 pb-10 mb-12 print:mb-8">
            <div class="font-arabic text-2xl text-stone-600 mb-3">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
            <h1 class="font-arabic text-5xl font-extrabold text-[#12313a] mb-3">{{ $this->book['arabicTitle'] }}</h1>
            <div class="text-2xl font-extrabold tracking-wide uppercase">{{ $this->book['title'] }}</div>
            <div class="text-base font-semibold text-stone-600 mt-1">{{ $this->book['swahiliTitle'] }}</div>
            <div class="text-xs text-stone-500 mt-2">{{ $this->book['subtitle'] }}</div>

            <div class="mt-6 text-sm font-bold text-stone-800">
                Mkusanyaji: {{ $this->book['author'] }}
            </div>
            <div class="font-arabic text-sm text-stone-600 mt-0.5">
                {{ $this->book['authorArabic'] }}
            </div>
            <div class="mt-4 text-xs text-stone-400">
                Tilawa — Maktaba ya Kiislamu &middot; www.tilawa.app
            </div>
        </div>

        {{-- Foreword --}}
        @if (!empty($this->book['foreword']))
            <div class="mb-12 border-b border-stone-200 pb-8 break-inside-avoid">
                <h2 class="text-lg font-bold uppercase tracking-wider text-stone-700 mb-3">Utangulizi</h2>
                <div class="font-arabic text-xl leading-[2.2] text-right mb-4" dir="rtl">
                    {{ $this->book['foreword']['arabic'] ?? '' }}
                </div>
                <p class="text-sm leading-relaxed text-stone-700">
                    {{ $this->book['foreword']['swahili'] ?? '' }}
                </p>
            </div>
        @endif

        {{-- Table of Contents (Print) --}}
        <div class="mb-12 border-b border-stone-200 pb-8 break-inside-avoid">
            <h2 class="text-lg font-bold uppercase tracking-wider text-stone-700 mb-4">Orodha ya Yaliyomo</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-xs">
                @foreach ($this->book['chapters'] as $ch)
                    <div class="flex items-center justify-between border-b border-stone-100 py-1">
                        <span class="font-medium text-stone-800">
                            <span class="font-bold text-[#188691] mr-1">{{ $ch['number'] }}.</span>
                            {{ $ch['title'] }}
                        </span>
                        <span class="font-arabic text-stone-600">{{ $ch['arabicTitle'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- All Chapters & Duas --}}
        <div class="space-y-12">
            @foreach ($this->book['chapters'] as $chapter)
                <div class="break-inside-avoid mb-10 border-b border-stone-200 pb-8">
                    {{-- Chapter Title Header --}}
                    <div class="border-b-2 border-[#188691] pb-3 mb-6 flex items-baseline justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-[#188691]">Mlango wa {{ $chapter['number'] }}</span>
                            <h2 class="text-xl font-bold text-[#12313a]">{{ $chapter['title'] }}</h2>
                            <div class="text-xs text-stone-500 italic">{{ $chapter['englishTitle'] }}</div>
                        </div>
                        <div class="font-arabic text-2xl font-bold text-[#188691]" dir="rtl">
                            {{ $chapter['arabicTitle'] }}
                        </div>
                    </div>

                    {{-- Duas --}}
                    <div class="space-y-6">
                        @foreach ($chapter['items'] as $item)
                            <div class="p-4 rounded-lg bg-stone-50 border border-stone-200 break-inside-avoid">
                                <div class="flex items-center justify-between text-xs font-bold text-stone-500 mb-2">
                                    <span>Dua Na. {{ $item['id'] }}</span>
                                    <span>{{ $item['repeats'] > 1 ? 'Inasomwa mara ' . $item['repeats'] : 'Inasomwa mara 1' }}</span>
                                </div>

                                {{-- Arabic Text --}}
                                <div class="font-arabic text-2xl leading-[2.4] text-right mb-3 text-black font-normal" dir="rtl">
                                    {{ $item['arabic'] }}
                                </div>

                                {{-- Transliteration --}}
                                @if (!empty($item['transliteration']))
                                    <div class="text-xs italic text-[#188691] mb-2">
                                        {{ $item['transliteration'] }}
                                    </div>
                                @endif

                                {{-- Swahili Translation --}}
                                <div class="text-sm leading-relaxed text-stone-800 mb-2">
                                    <span class="font-bold text-stone-600">Tafsiri:</span> {{ $item['swahili'] }}
                                </div>

                                {{-- Reference --}}
                                @if (!empty($item['reference']))
                                    <div class="text-xs text-stone-500 font-medium pt-1 border-t border-stone-200">
                                        Chanzo: {{ $item['reference'] }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Closing Footer --}}
        <div class="text-center pt-8 border-t-2 border-stone-300 break-inside-avoid text-xs text-stone-500">
            <div class="font-arabic text-lg mb-1">وَآخِرُ دَعْوَانَا أَنِ الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ</div>
            <p>Mwisho wa kitabu cha Hisn al-Muslim (Ngome ya Muislamu) &middot; Imetolewa na Tilawa</p>
        </div>
    </div>
</div>
